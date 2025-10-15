<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Resources\SellerResource;
use App\Services\SellerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerController extends Controller
{
    /**
     * Construtor do controller.
     *
     * @param SellerService $sellerService
     */
    public function __construct(
        private readonly SellerService $sellerService
    ) {
    }

    /**
     * Lista todos os vendedores ativos.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $sellers = $this->sellerService->getAllSellers();
        return SellerResource::collection($sellers);
    }

    /**
     * Cadastra um novo vendedor.
     *
     * Recebe nome e e-mail, valida os dados através do StoreSellerRequest
     * e cria um novo vendedor no sistema.
     *
     * @param StoreSellerRequest $request
     * @return JsonResponse
     */
    public function store(StoreSellerRequest $request): JsonResponse
    {
        $seller = $this->sellerService->createSeller($request->validated());

        return (new SellerResource($seller))
            ->response()
            ->setStatusCode(201);
    }
}
