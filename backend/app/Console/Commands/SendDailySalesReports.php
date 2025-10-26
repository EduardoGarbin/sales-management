<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyAdminSalesReportEmail;
use App\Jobs\SendDailySalesReportEmail;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDailySalesReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:send-daily-reports {--date= : Data para gerar relatório (formato: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia relatório diário de vendas para todos os vendedores e administrador';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Processando relatórios de vendas para {$date->format('d/m/Y')}...");

        $sellers = Seller::all();
        $processedCount = 0;

        // Variáveis para relatório administrativo
        $globalTotalSales = 0;
        $globalTotalAmount = 0.0;
        $globalTotalCommission = 0.0;
        $sellersData = [];

        foreach ($sellers as $seller) {
            $sales = $seller->sales()
                ->with('seller')
                ->whereDate('sale_date', $date)
                ->get();

            $salesCount = $sales->count();
            $totalAmount = $sales->sum('amount');
            $totalCommission = $sales->sum(function ($sale) {
                return $sale->amount * ($sale->seller->commission_rate / 100);
            });

            SendDailySalesReportEmail::dispatch(
                $seller,
                $date->format('d/m/Y'),
                $salesCount,
                (float) $totalAmount,
                (float) $totalCommission
            );

            $processedCount++;

            // Acumular dados para relatório administrativo
            if ($salesCount > 0) {
                $globalTotalSales += $salesCount;
                $globalTotalAmount += $totalAmount;
                $globalTotalCommission += $totalCommission;

                $sellersData[] = [
                    'name' => $seller->name,
                    'sales_count' => $salesCount,
                    'amount' => (float) $totalAmount,
                    'commission' => (float) $totalCommission,
                ];
            }

            $this->line("Relatório agendado para {$seller->name} ({$seller->email}) - {$salesCount} vendas");
        }

        // Ordenar vendedores por valor total
        usort($sellersData, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        // Pegar apenas top 5
        $topSellers = array_slice($sellersData, 0, 5);

        // Enviar relatório administrativo
        $adminEmail = config('mail.admin_email');

        if ($adminEmail) {
            SendDailyAdminSalesReportEmail::dispatch(
                $adminEmail,
                $date->format('d/m/Y'),
                $globalTotalSales,
                $globalTotalAmount,
                $globalTotalCommission,
                $topSellers
            );

            $this->newLine();
            $this->line("Relatório administrativo agendado para {$adminEmail}");
            $this->line("  Total: {$globalTotalSales} vendas | R$ " . number_format($globalTotalAmount, 2, ',', '.'));
        } else {
            $this->newLine();
            $this->warn('ADMIN_EMAIL não configurado no .env - relatório administrativo não enviado');
        }

        $this->newLine();
        $this->info("Total de {$processedCount} relatórios de vendedores agendados.");

        return Command::SUCCESS;
    }
}
