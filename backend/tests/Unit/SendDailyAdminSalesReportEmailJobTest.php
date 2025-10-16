<?php

namespace Tests\Unit;

use App\Jobs\SendDailyAdminSalesReportEmail;
use App\Mail\DailyAdminSalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDailyAdminSalesReportEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_admin_email_with_sales_data(): void
    {
        Mail::fake();

        $topSellers = [
            ['name' => 'John Doe', 'sales_count' => 5, 'amount' => 5000.00, 'commission' => 425.00],
            ['name' => 'Jane Smith', 'sales_count' => 3, 'amount' => 3000.00, 'commission' => 255.00],
        ];

        $job = new SendDailyAdminSalesReportEmail(
            adminEmail: 'admin@test.com',
            date: '15/10/2025',
            totalSales: 8,
            totalAmount: 8000.00,
            totalCommission: 680.00,
            topSellers: $topSellers
        );

        $job->handle();

        Mail::assertQueued(DailyAdminSalesReport::class, function ($mail) {
            return $mail->hasTo('admin@test.com')
                && $mail->totalSales === 8
                && $mail->totalAmount === 8000.00
                && $mail->totalCommission === 680.00
                && count($mail->topSellers) === 2;
        });
    }

    public function test_job_handles_zero_sales(): void
    {
        Mail::fake();

        $job = new SendDailyAdminSalesReportEmail(
            adminEmail: 'admin@test.com',
            date: '15/10/2025',
            totalSales: 0,
            totalAmount: 0.0,
            totalCommission: 0.0,
            topSellers: []
        );

        $job->handle();

        Mail::assertQueued(DailyAdminSalesReport::class, function ($mail) {
            return $mail->totalSales === 0
                && $mail->totalAmount === 0.0
                && $mail->totalCommission === 0.0
                && empty($mail->topSellers);
        });
    }
}
