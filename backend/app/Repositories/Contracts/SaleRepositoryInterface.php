<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface para o repositório de vendas.
 *
 * Define métodos específicos para operações relacionadas a vendas,
 * incluindo queries com relacionamentos e filtros.
 */
interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retorna todas as vendas com paginação, ordenadas decrescente.
     * Carrega o relacionamento 'seller' para cálculo de comissão.
     *
     * @param int $perPage Itens por página
     * @return LengthAwarePaginator
     */
    public function getAllPaginatedWithSeller(int $perPage = 15): LengthAwarePaginator;

    /**
     * Cria uma venda e carrega o relacionamento com o vendedor.
     *
     * @param array $data
     * @return Sale
     */
    public function createWithSeller(array $data): Sale;

    /**
     * Retorna vendas de um vendedor específico com paginação.
     *
     * @param int $sellerId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBySeller(int $sellerId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Retorna vendas por vendedor em uma data específica.
     *
     * @param int $sellerId
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySellerAndDate(int $sellerId, string $date): \Illuminate\Database\Eloquent\Collection;
}
