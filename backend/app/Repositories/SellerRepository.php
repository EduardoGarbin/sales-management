<?php

namespace App\Repositories;

use App\Models\Seller;
use App\Repositories\Contracts\SellerRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repositório para operações relacionadas a vendedores.
 *
 * Encapsula toda a lógica de acesso a dados da entidade Seller,
 * abstraindo queries Eloquent e permitindo reutilização e testabilidade.
 */
class SellerRepository extends BaseRepository implements SellerRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getModel(): Model
    {
        return new Seller();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * {@inheritDoc}
     */
    public function findWithSales(int $id): Seller
    {
        return $this->model
            ->with('sales')
            ->findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesByDate(int $sellerId, string $date): Collection
    {
        $seller = $this->findOrFail($sellerId);
        $parsedDate = Carbon::parse($date);

        return $seller->sales()
            ->whereDate('sale_date', $parsedDate)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
