<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResendCommissionEmailRequest;
use App\Http\Requests\StoreSellerRequest;
use App\Http\Resources\SellerResource;
use App\Jobs\SendDailySalesReportEmail;
use App\Models\Seller;
use App\Services\SellerService;
use Carbon\Carbon;
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
     * de um vendedor para uma data específica.
     *
     * @param ResendCommissionEmailRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function resendCommissionEmail(ResendCommissionEmailRequest $request, int $id): JsonResponse
    {
        $seller = Seller::findOrFail($id);

        $date = Carbon::parse($request->validated('date'));

        $sales = $seller->sales()
            ->whereDate('sale_date', $date)
            ->get();

        $salesCount = $sales->count();
        $totalAmount = $sales->sum('amount');
        $totalCommission = $sales->sum(function ($sale) {
            return $sale->amount * ($sale->seller->commission_rate / 100);
        });

        SendDailySalesReportEmail::dispatch(
            $seller,
            $date->format('d/m/Y'),
            $salesCount,
            (float) $totalAmount,
            (float) $totalCommission
        );

        return response()->json([
            'message' => 'E-mail de comissão reenviado com sucesso',
            'data' => [
                'seller' => [
                    'id' => $seller->id,
                    'name' => $seller->name,
                    'email' => $seller->email,
                ],
                'date' => $date->format('d/m/Y'),
                'sales_count' => $salesCount,
                'total_amount' => $totalAmount,
                'total_commission' => $totalCommission,
            ]
        ], 200);
    }
}
