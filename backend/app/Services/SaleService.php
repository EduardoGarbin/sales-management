<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\SellerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleService
{
    /**
     * Repositórios injetados via dependency injection.
     *
     * @var SaleRepositoryInterface
     * @var SellerRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;
    private SellerRepositoryInterface $sellerRepository;

    /**
     * Construtor do serviço.
     *
     * @param SaleRepositoryInterface $saleRepository
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(
        SaleRepositoryInterface $saleRepository,
        SellerRepositoryInterface $sellerRepository
    ) {
        $this->saleRepository = $saleRepository;
        $this->sellerRepository = $sellerRepository;
    }
    /**
     * Lista todas as vendas ordenadas de forma decrescente com paginação.
     *
     * Retorna as vendas com o relacionamento 'seller' carregado para
     * que o cálculo da comissão possa ser feito automaticamente.
     *
     * @param int $perPage Número de itens por página (padrão: 15)
     * @return LengthAwarePaginator
     */
    public function getAllSales(int $perPage = 15): LengthAwarePaginator
    {
        return $this->saleRepository->getAllPaginatedWithSeller($perPage);
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
        $this->sellerRepository->findOrFail($data['seller_id']);

        // Cria a venda através do repository
        return $this->saleRepository->createWithSeller([
            'seller_id' => $data['seller_id'],
            'amount' => $data['amount'],
            'sale_date' => $data['sale_date'],
        ]);
    }

    /**
     * Lista vendas de um vendedor específico com paginação.
     *
     * @param int $sellerId
     * @param int $perPage Número de itens por página (padrão: 15)
     * @return LengthAwarePaginator
     */
    public function getSalesBySeller(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->saleRepository->getBySeller($sellerId, $perPage);
    }
}
