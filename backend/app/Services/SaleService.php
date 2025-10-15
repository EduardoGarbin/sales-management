<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Collection;

class SaleService
{
    /**
     * Lista todas as vendas ordenadas de forma decrescente.
     *
     * Retorna as vendas com o relacionamento 'seller' carregado para
     * que o cálculo da comissão possa ser feito automaticamente.
     *
     * @return Collection<int, Sale>
     */
    public function getAllSales(): Collection
    {
        return Sale::with('seller')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Cria uma nova venda.
     *
     * A comissão é calculada automaticamente através do accessor no Model Sale.
     * Valida se o vendedor existe antes de criar a venda.
     *
     * @param array $data
     * @return Sale
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createSale(array $data): Sale
    {
        // Valida se o vendedor existe
        Seller::findOrFail($data['seller_id']);

        $sale = Sale::create([
            'seller_id' => $data['seller_id'],
            'amount' => $data['amount'],
            'sale_date' => $data['sale_date'],
        ]);

        return $sale->load('seller');
    }

    /**
     * Lista vendas de um vendedor específico.
     *
     * @param int $sellerId
     * @return Collection<int, Sale>
     */
    public function getSalesBySeller(int $sellerId): Collection
    {
        return Sale::with('seller')
            ->where('seller_id', $sellerId)
            ->orderBy('sale_date', 'desc')
            ->get();
    }
}
