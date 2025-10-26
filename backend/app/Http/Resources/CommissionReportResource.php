<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Retorna os dados do relatório de comissão formatados para a API, incluindo:
     * - Dados básicos do vendedor (id, name, email)
     * - Data do relatório
     * - Métricas agregadas (quantidade de vendas, valor total, comissão total)
     *
     * Este Resource é usado principalmente para responses do endpoint de reenvio
     * de e-mail de comissão, mas pode ser reutilizado em outros contextos que
     * precisem exibir relatórios de comissão.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'seller' => [
                'id' => $this->resource['seller']->id,
                'name' => $this->resource['seller']->name,
                'email' => $this->resource['seller']->email,
            ],
            'date' => $this->resource['date']->format('d/m/Y'),
            'sales_count' => $this->resource['sales_count'],
            'total_amount' => $this->resource['total_amount'],
            'total_commission' => $this->resource['total_commission'],
        ];
    }
}
