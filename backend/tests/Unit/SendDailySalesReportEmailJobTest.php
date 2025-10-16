<?php

namespace Tests\Unit;

use App\Jobs\SendDailySalesReportEmail;
use App\Mail\DailySalesReport;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDailySalesReportEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_email_to_seller(): void
    {
        Mail::fake();

        $seller = Seller::factory()->create(['email' => 'test@example.com']);

        $job = new SendDailySalesReportEmail(
            seller: $seller,
            date: '15/10/2025',
            salesCount: 5,
            totalAmount: 5000.00,
            totalCommission: 425.00
        );

        $job->handle();

        Mail::assertQueued(DailySalesReport::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email)
                && $mail->seller->id === $seller->id
                && $mail->salesCount === 5
                && $mail->totalAmount === 5000.00
                && $mail->totalCommission === 425.00;
        });
    }

    public function test_job_handles_zero_sales(): void
    {
        Mail::fake();

        $seller = Seller::factory()->create();

        $job = new SendDailySalesReportEmail(
            seller: $seller,
            date: '15/10/2025',
            salesCount: 0,
            totalAmount: 0.0,
            totalCommission: 0.0
        );

        $job->handle();

        Mail::assertQueued(DailySalesReport::class, function ($mail) {
            return $mail->salesCount === 0
                && $mail->totalAmount === 0.0
                && $mail->totalCommission === 0.0;
        });
    }
}
