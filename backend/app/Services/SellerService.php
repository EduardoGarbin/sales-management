<?php

namespace App\Services;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SellerService
{
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
            fn() => Seller::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page)
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
        $seller = Seller::create($data);

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
}
