<?php

namespace Tests\Feature;

use App\Jobs\SendDailySalesReportEmail;
use App\Models\Sale;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendDailySalesReportsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_jobs_for_all_sellers(): void
    {
        Queue::fake();

        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        $date = Carbon::yesterday();

        Sale::factory()->create([
            'seller_id' => $seller1->id,
            'sale_date' => $date,
            'amount' => 1000.00
        ]);

        $this->artisan('sales:send-daily-reports', ['--date' => $date->format('Y-m-d')])
            ->expectsOutput("Processando relatórios de vendas para {$date->format('d/m/Y')}...")
            ->assertExitCode(0);

        Queue::assertPushed(SendDailySalesReportEmail::class, 2);
    }

    public function test_command_calculates_sales_correctly(): void
    {
        Queue::fake();

        $seller = Seller::factory()->create(['commission_rate' => 10.0]);
        $date = Carbon::yesterday();

        Sale::factory()->count(3)->create([
            'seller_id' => $seller->id,
            'sale_date' => $date,
            'amount' => 100.00
        ]);

        $this->artisan('sales:send-daily-reports', ['--date' => $date->format('Y-m-d')])
            ->assertExitCode(0);

        Queue::assertPushed(SendDailySalesReportEmail::class, function ($job) use ($seller) {
            return $job->seller->id === $seller->id
                && $job->salesCount === 3
                && $job->totalAmount === 300.00
                && $job->totalCommission === 30.00;
        });
    }

    public function test_command_handles_sellers_with_no_sales(): void
    {
        Queue::fake();

        $seller = Seller::factory()->create();
        $date = Carbon::yesterday();

        $this->artisan('sales:send-daily-reports', ['--date' => $date->format('Y-m-d')])
            ->assertExitCode(0);

        Queue::assertPushed(SendDailySalesReportEmail::class, function ($job) use ($seller) {
            return $job->seller->id === $seller->id
                && $job->salesCount === 0
                && $job->totalAmount === 0.0
                && $job->totalCommission === 0.0;
        });
    }

    public function test_command_uses_yesterday_as_default_date(): void
    {
        Queue::fake();

        Seller::factory()->create();

        $this->artisan('sales:send-daily-reports')
            ->expectsOutputToContain('Processando relatórios de vendas')
            ->assertExitCode(0);

        Queue::assertPushed(SendDailySalesReportEmail::class);
    }
}
