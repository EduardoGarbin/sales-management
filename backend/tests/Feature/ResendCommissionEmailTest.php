<?php

namespace Tests\Feature;

use App\Jobs\SendDailySalesReportEmail;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ResendCommissionEmailTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): self
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'sanctum');
    }

    public function test_resend_commission_email_successfully(): void
    {
        Queue::fake();

        $seller = Seller::factory()->create();
        $date = Carbon::yesterday();

        Sale::factory()->count(3)->create([
            'seller_id' => $seller->id,
            'sale_date' => $date,
            'amount' => 100.00
        ]);

        $response = $this->actingAsUser()->postJson("/api/sellers/{$seller->id}/resend-commission-email", [
            'date' => $date->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'E-mail de comissão reenviado com sucesso',
                'data' => [
                    'seller' => [
                        'id' => $seller->id,
                        'name' => $seller->name,
                        'email' => $seller->email,
                    ],
                    'sales_count' => 3,
                ]
            ]);

        Queue::assertPushed(SendDailySalesReportEmail::class, function ($job) use ($seller) {
            return $job->seller->id === $seller->id
                && $job->salesCount === 3
                && $job->totalAmount === 300.00;
        });
    }

    public function test_resend_commission_email_with_no_sales(): void
    {
        Queue::fake();

        $seller = Seller::factory()->create();
        $date = Carbon::yesterday();

        $response = $this->actingAsUser()->postJson("/api/sellers/{$seller->id}/resend-commission-email", [
            'date' => $date->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sales_count', 0)
            ->assertJsonPath('data.total_amount', 0)
            ->assertJsonPath('data.total_commission', 0);

        Queue::assertPushed(SendDailySalesReportEmail::class);
    }

    public function test_resend_commission_email_requires_date(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson("/api/sellers/{$seller->id}/resend-commission-email", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_resend_commission_email_requires_valid_date_format(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson("/api/sellers/{$seller->id}/resend-commission-email", [
            'date' => '15/10/2025', // formato errado
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_resend_commission_email_rejects_future_date(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson("/api/sellers/{$seller->id}/resend-commission-email", [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_resend_commission_email_fails_for_non_existent_seller(): void
    {
        $response = $this->actingAsUser()->postJson("/api/sellers/999/resend-commission-email", [
            'date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $response->assertStatus(404);
    }

    public function test_resend_commission_email_requires_authentication(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->postJson("/api/sellers/{$seller->id}/resend-commission-email", [
            'date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
    }
}
