<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repositório para operações relacionadas a vendas.
 *
 * Encapsula toda a lógica de acesso a dados da entidade Sale,
 * incluindo queries que carregam relacionamentos para cálculo de comissões.
 */
class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getModel(): string
    {
        return Sale::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginatedWithSeller(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with('seller')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function createWithSeller(array $data): Sale
    {
        $sale = $this->create($data);
        return $sale->load('seller');
    }

    /**
     * {@inheritDoc}
     */
    public function getBySeller(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with('seller')
            ->where('seller_id', $sellerId)
            ->orderBy('sale_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getBySellerAndDate(int $sellerId, string $date): Collection
    {
        $parsedDate = Carbon::parse($date);

        return $this->model
            ->where('seller_id', $sellerId)
            ->whereDate('sale_date', $parsedDate)
            ->get();
    }
}
