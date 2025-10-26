<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Seller;
use App\Services\SaleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes para SaleService.
 *
 * Este teste foca na LÓGICA DE NEGÓCIO do Service:
 * - Validação de regras de negócio (seller existe antes de criar venda)
 * - Cálculo de comissão (accessor do Model, mas é lógica de negócio)
 * - Orquestração de Repository operations
 *
 * Testes de queries, filtros, ordenação e persistência estão em SaleRepositoryTest.
 */
class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = app(SaleService::class);
    }

    /**
     * Testa se createSale valida que o seller existe.
     *
     * Regra de negócio: não é possível criar venda para seller inexistente.
     */
    public function test_create_sale_validates_seller_exists(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->saleService->createSale([
            'seller_id' => 9999, // Seller inexistente
            'amount' => 1000.00,
            'sale_date' => '2025-10-26',
        ]);
    }

    /**
     * Testa se createSale retorna Sale com seller carregado.
     *
     * Valida que o Service orquestra corretamente o Repository
     * para retornar venda com relacionamento carregado.
     */
    public function test_create_sale_returns_sale_with_seller_loaded(): void
    {
        $seller = Seller::factory()->create();

        $sale = $this->saleService->createSale([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2025-10-26',
        ]);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertTrue($sale->relationLoaded('seller'));
        $this->assertEquals($seller->id, $sale->seller->id);
    }

    /**
     * Testa se createSale preserva dados corretamente.
     */
    public function test_create_sale_preserves_data_correctly(): void
    {
        $seller = Seller::factory()->create();

        $sale = $this->saleService->createSale([
            'seller_id' => $seller->id,
            'amount' => 1234.56,
            'sale_date' => '2025-10-26',
        ]);

        $this->assertEquals(1234.56, $sale->amount);
        $this->assertEquals('2025-10-26', $sale->sale_date->toDateString());
    }

    /**
     * Testa se o accessor de comissão calcula corretamente.
     *
     * NOTA: Este teste valida o Model Sale (accessor), mas como é
     * lógica de negócio crítica para o domínio, mantemos aqui.
     * Valida que a comissão é calculada com base na taxa do vendedor.
     */
    public function test_commission_accessor_calculates_correctly(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 10.00]);

        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
        ]);

        $sale->load('seller');

        // Comissão deve ser 10% de 1000 = 100.00
        $this->assertEquals('100.00', $sale->commission);
    }

    /**
     * Testa se o accessor usa a taxa padrão de 8.5%.
     */
    public function test_commission_accessor_uses_default_rate(): void
    {
        $seller = Seller::factory()->create(); // Taxa padrão: 8.5%

        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
        ]);

        $sale->load('seller');

        // Comissão deve ser 8.5% de 1000 = 85.00
        $this->assertEquals('85.00', $sale->commission);
    }

    /**
     * Testa se getAllSales retorna paginador.
     *
     * Valida que o Service orquestra corretamente o Repository.
     */
    public function test_get_all_sales_returns_paginator(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(3)->create(['seller_id' => $seller->id]);

        $paginator = $this->saleService->getAllSales(15);

        $this->assertEquals(3, $paginator->total());
        $this->assertCount(3, $paginator->items());
    }

    /**
     * Testa se getSalesBySeller delega corretamente ao Repository.
     */
    public function test_get_sales_by_seller_delegates_to_repository(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(5)->create(['seller_id' => $seller->id]);

        $paginator = $this->saleService->getSalesBySeller($seller->id, 15);

        $this->assertEquals(5, $paginator->total());
    }
}
