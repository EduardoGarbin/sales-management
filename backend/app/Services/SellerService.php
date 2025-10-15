<?php

namespace App\Services;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SellerService
{
    /**
     * Chave do cache para a lista de vendedores.
     */
    private const SELLERS_CACHE_KEY = 'sellers_list';

    /**
     * Tempo de vida do cache em minutos.
     */
    private const CACHE_TTL = 60;

    /**
     * Lista todos os vendedores ativos ordenados de forma decrescente.
     *
     * Este método retorna todos os vendedores que não foram deletados (soft delete),
     * ordenados do mais recente para o mais antigo (ID decrescente).
     * Os dados são armazenados em cache por 60 minutos para melhorar a performance.
     *
     * @return Collection<int, Seller>
     */
    public function getAllSellers(): Collection
    {
        return Cache::remember(
            self::SELLERS_CACHE_KEY,
            now()->addMinutes(self::CACHE_TTL),
            fn() => Seller::orderBy('id', 'desc')->get()
        );
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
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::SELLERS_CACHE_KEY);
    }
}
