<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Seller;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = new SaleService();
    }

    /**
     * Testa se as vendas são retornadas em ordem decrescente por ID.
     */
    public function test_sales_are_ordered_by_id_descending(): void
    {
        $seller = Seller::factory()->create();

        $sale1 = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale2 = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale3 = Sale::factory()->create(['seller_id' => $seller->id]);

        $sales = $this->saleService->getAllSales();

        $this->assertEquals($sale3->id, $sales[0]->id);
        $this->assertEquals($sale2->id, $sales[1]->id);
        $this->assertEquals($sale1->id, $sales[2]->id);
    }

    /**
     * Testa se createSale cria uma venda corretamente.
     */
    public function test_create_sale_stores_sale_in_database(): void
    {
        $seller = Seller::factory()->create();

        $data = [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2025-10-14',
        ];

        $sale = $this->saleService->createSale($data);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals(1000.00, $sale->amount);
        $this->assertEquals('2025-10-14', $sale->sale_date->toDateString());
        $this->assertNotNull($sale->id);

        $this->assertDatabaseCount('sales', 1);
        $this->assertEquals($seller->id, $sale->seller_id);
    }

    /**
     * Testa se getSalesBySeller retorna apenas vendas do vendedor específico.
     */
    public function test_get_sales_by_seller_returns_only_seller_sales(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Sale::factory()->count(3)->create(['seller_id' => $seller1->id]);
        Sale::factory()->count(2)->create(['seller_id' => $seller2->id]);

        $salesSeller1 = $this->saleService->getSalesBySeller($seller1->id);
        $salesSeller2 = $this->saleService->getSalesBySeller($seller2->id);

        $this->assertCount(3, $salesSeller1);
        $this->assertCount(2, $salesSeller2);

        $salesSeller1->each(fn($sale) => $this->assertEquals($seller1->id, $sale->seller_id));
        $salesSeller2->each(fn($sale) => $this->assertEquals($seller2->id, $sale->seller_id));
    }

    /**
     * Testa se o accessor de comissão calcula corretamente.
     *
     * Valida que o accessor 'commission' no Model Sale
     * está calculando a comissão com base na taxa do vendedor.
     */
    public function test_commission_accessor_calculates_correctly(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 10.00]);

        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
        ]);

        // Força o carregamento da relação seller
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
}
