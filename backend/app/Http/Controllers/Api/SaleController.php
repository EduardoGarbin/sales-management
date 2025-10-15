<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleController extends Controller
{
    /**
     * Construtor do controller.
     *
     * @param SaleService $saleService
     */
    public function __construct(
        private readonly SaleService $saleService
    ) {
    }

    /**
     * Lista todas as vendas.
     *
     * Retorna todas as vendas ordenadas do mais recente para o mais antigo,
     * incluindo os dados do vendedor e a comissão calculada automaticamente.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $sales = $this->saleService->getAllSales();
        return SaleResource::collection($sales);
    }

    /**
     * Cadastra uma nova venda.
     *
     * Recebe seller_id, amount e sale_date, valida os dados através do StoreSaleRequest
     * e cria uma nova venda no sistema. A comissão é calculada automaticamente.
     *
     * @param StoreSaleRequest $request
     * @return JsonResponse
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $sale = $this->saleService->createSale($request->validated());

        return (new SaleResource($sale))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Lista vendas de um vendedor específico.
     *
     * @param int $sellerId
     * @return AnonymousResourceCollection
     */
    public function salesBySeller(int $sellerId): AnonymousResourceCollection
    {
        $sales = $this->saleService->getSalesBySeller($sellerId);
        return SaleResource::collection($sales);
    }
}
