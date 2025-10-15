<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Retorna os dados da venda formatados para a API, incluindo:
     * - Dados básicos da venda (id, amount, sale_date)
     * - Comissão calculada automaticamente
     * - Dados do vendedor (usando SellerResource)
     * - Timestamps formatados em ISO8601
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'seller' => new SellerResource($this->whenLoaded('seller')),
            'amount' => $this->amount,
            'commission' => $this->commission,
            'sale_date' => $this->sale_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
