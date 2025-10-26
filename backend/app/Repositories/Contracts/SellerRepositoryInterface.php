<?php

namespace App\Repositories\Contracts;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface para o repositório de vendedores.
 *
 * Define métodos específicos para operações relacionadas a vendedores,
 * além dos métodos herdados da interface base.
 */
interface SellerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retorna todos os vendedores com paginação e ordenação decrescente.
     *
     * @param int $page Número da página
     * @param int $perPage Itens por página
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca um vendedor com suas vendas carregadas.
     *
     * @param int $id
     * @return Seller
     */
    public function findWithSales(int $id): Seller;

    /**
     * Retorna vendas de um vendedor em uma data específica.
     *
     * @param int $sellerId
     * @param string $date
     * @return Collection
     */
    public function getSalesByDate(int $sellerId, string $date): Collection;

    /**
     * Verifica se um email já está em uso.
     *
     * @param string $email
     * @param int|null $exceptId ID para excluir da verificação (útil em updates)
     * @return bool
     */
    public function emailExists(string $email, ?int $exceptId = null): bool;
}
