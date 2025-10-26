<?php

namespace App\Services;

use App\Jobs\SendDailySalesReportEmail;
use App\Models\Seller;
use App\Repositories\Contracts\SellerRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SellerService
{
    /**
     * Repositório de vendedores injetado via dependency injection.
     *
     * @var SellerRepositoryInterface
     */
    private SellerRepositoryInterface $sellerRepository;

    /**
     * Construtor do serviço.
     *
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(SellerRepositoryInterface $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }
    /**
     * Prefixo da chave do cache para a lista de vendedores.
     */
    private const SELLERS_CACHE_PREFIX = 'sellers_list';

    /**
     * Tempo de vida do cache em minutos.
     */
    private const CACHE_TTL = 60;

    /**
     * Lista todos os vendedores ativos ordenados de forma decrescente com paginação.
     *
     * Este método retorna todos os vendedores que não foram deletados (soft delete),
     * ordenados do mais recente para o mais antigo (ID decrescente).
     * Cada página é armazenada em cache separadamente por 60 minutos para melhorar a performance.
     *
     * @param int $page Número da página (padrão: 1)
     * @param int $perPage Número de itens por página (padrão: 15)
     * @return LengthAwarePaginator
     */
    public function getAllSellers(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = $this->getCacheKey($page, $perPage);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL),
            fn() => $this->sellerRepository->getAllPaginated($page, $perPage)
        );
    }

    /**
     * Gera a chave de cache para uma página específica.
     *
     * @param int $page
     * @param int $perPage
     * @return string
     */
    private function getCacheKey(int $page, int $perPage): string
    {
        return self::SELLERS_CACHE_PREFIX . "_page_{$page}_per_{$perPage}";
    }

    /**
     * Cria um novo vendedor.
     *
     * Após criar o vendedor, limpa o cache da lista para
     * garantir que a próxima consulta retorne dados atualizados.
     *
     * @param array $data
     * @return Seller
     */
    public function createSeller(array $data): Seller
    {
        $seller = $this->sellerRepository->create($data);

        $this->clearCache();

        return $seller;
    }

    /**
     * Limpa o cache da lista de vendedores.
     *
     * Este método deve ser chamado sempre que houver alterações
     * nos vendedores (criação, atualização ou exclusão).
     * Limpa as páginas mais comuns do cache (primeiras 10 páginas com tamanhos padrão).
     *
     * @return void
     */
    public function clearCache(): void
    {
        // Limpa as páginas mais comuns do cache
        $commonPageSizes = [10, 15, 20, 25, 50];
        $maxPages = 10;

        foreach ($commonPageSizes as $perPage) {
            for ($page = 1; $page <= $maxPages; $page++) {
                Cache::forget($this->getCacheKey($page, $perPage));
            }
        }

        // Também limpa possíveis caches antigos com a chave antiga
        Cache::forget(self::SELLERS_CACHE_PREFIX);
    }

    /**
     * Reenvia o e-mail de comissão para um vendedor específico.
     *
     * Este método busca todas as vendas do vendedor em uma data específica,
     * calcula os totais (quantidade, valor e comissão) e dispara o job
     * para envio do e-mail de relatório.
     *
     * @param int $sellerId ID do vendedor
     * @param string $date Data no formato que será parseado pelo Carbon
     * @return array Dados calculados (seller, date, sales_count, total_amount, total_commission)
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Se o vendedor não for encontrado
     */
    public function resendCommissionEmail(int $sellerId, string $date): array
    {
        // Busca o vendedor ou lança exceção se não existir (via repository)
        $seller = $this->sellerRepository->findOrFail($sellerId);

        // Parse da data para objeto Carbon
        $parsedDate = Carbon::parse($date);

        // Busca todas as vendas do vendedor na data especificada (via repository)
        $sales = $this->sellerRepository->getSalesByDate($sellerId, $date);

        // Calcula os totais
        $salesCount = $sales->count();
        $totalAmount = $sales->sum('amount');
        $totalCommission = $sales->sum(function ($sale) {
            return $sale->amount * ($sale->seller->commission_rate / 100);
        });

        // Dispara o job para envio do e-mail
        SendDailySalesReportEmail::dispatch(
            $seller,
            $parsedDate->format('d/m/Y'),
            $salesCount,
            (float) $totalAmount,
            (float) $totalCommission
        );

        // Retorna os dados calculados para o controller
        return [
            'seller' => $seller,
            'date' => $parsedDate,
            'sales_count' => $salesCount,
            'total_amount' => $totalAmount,
            'total_commission' => $totalCommission,
        ];
    }
}
