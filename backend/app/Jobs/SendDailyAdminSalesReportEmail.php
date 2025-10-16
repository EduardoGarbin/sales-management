<?php

namespace App\Jobs;

use App\Mail\DailyAdminSalesReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailyAdminSalesReportEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $adminEmail,
        public string $date,
        public int $totalSales,
        public float $totalAmount,
        public float $totalCommission,
        public array $topSellers
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->adminEmail)->send(
            new DailyAdminSalesReport(
                $this->date,
                $this->totalSales,
                $this->totalAmount,
                $this->totalCommission,
                $this->topSellers
            )
        );
    }
}
