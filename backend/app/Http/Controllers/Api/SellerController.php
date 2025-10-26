<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResendCommissionEmailRequest;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Resources\CommissionReportResource;
use App\Http\Resources\SellerResource;
use App\Services\SellerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Lista todos os vendedores ativos com paginação.
     *
     * Aceita os parâmetros 'page' e 'per_page' via query string.
     * Os dados são armazenados em cache por página para melhor performance.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $sellers = $this->sellerService->getAllSellers($page, $perPage);
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

    /**
     * Reenvia o e-mail de comissão para um vendedor específico.
     *
     * Permite que o administrador reenvie o relatório de comissões
     * de um vendedor para uma data específica. A lógica de negócio
     * (busca de dados, cálculos e dispatch do job) é delegada ao SellerService.
     * A formatação da resposta é delegada ao CommissionReportResource.
     *
     * @param ResendCommissionEmailRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function resendCommissionEmail(ResendCommissionEmailRequest $request, int $id): JsonResponse
    {
        // Delega toda a lógica de negócio para o service
        $result = $this->sellerService->resendCommissionEmail(
            $id,
            $request->validated('date')
        );

        // Usa o Resource para formatar a resposta e adiciona mensagem de sucesso
        return (new CommissionReportResource($result))
            ->additional(['message' => 'E-mail de comissão reenviado com sucesso'])
            ->response()
            ->setStatusCode(200);
    }
}
