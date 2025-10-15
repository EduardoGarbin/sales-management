<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'sale_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    protected $appends = ['commission'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Calcula a comissão da venda automaticamente.
     *
     * A comissão é calculada com base na taxa de comissão do vendedor (commission_rate).
     * Exemplo: Venda de R$ 1000,00 com taxa de 8.5% = R$ 85,00
     *
     * @return Attribute
     */
    protected function commission(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->relationLoaded('seller')) {
                    $this->load('seller');
                }

                $commission = ($this->amount * $this->seller->commission_rate) / 100;

                return number_format($commission, 2, '.', '');
            }
        );
    }
}
