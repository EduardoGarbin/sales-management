<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Seller;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReportEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Seller $seller,
        public string $date,
        public int $salesCount,
        public float $totalAmount,
        public float $totalCommission
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->seller->email)->send(
            new DailySalesReport(
                $this->seller,
                $this->date,
                $this->salesCount,
                $this->totalAmount,
                $this->totalCommission
            )
        );
    }
}
