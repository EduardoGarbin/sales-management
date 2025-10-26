<?php

namespace Tests\Unit;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SaleResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o Resource retorna apenas os campos definidos com valores corretos.
     */
    public function test_resource_returns_only_defined_fields_with_correct_values(): void
    {
        $seller = Seller::factory()->create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'commission_rate' => 10.00,
        ]);

        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2025-10-26',
        ]);

        $sale->load('seller');

        $resource = new SaleResource($sale);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica campos esperados
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('seller', $response);
        $this->assertArrayHasKey('amount', $response);
        $this->assertArrayHasKey('commission', $response);
        $this->assertArrayHasKey('sale_date', $response);
        $this->assertArrayHasKey('created_at', $response);
        $this->assertArrayHasKey('updated_at', $response);

        // Verifica valores
        $this->assertEquals($sale->id, $response['id']);
        $this->assertEquals(1000.00, $response['amount']);
        $this->assertEquals('100.00', $response['commission']); // 10% de 1000
        $this->assertEquals('2025-10-26', $response['sale_date']);

        // Verifica estrutura do seller (nested resource)
        // O SellerResource retorna como objeto no toArray, precisa ser convertido
        $this->assertInstanceOf(\App\Http\Resources\SellerResource::class, $response['seller']);

        // Converte o SellerResource para array para verificar os campos
        $sellerArray = $response['seller']->toArray(Request::create('/test'));
        $this->assertArrayHasKey('id', $sellerArray);
        $this->assertArrayHasKey('name', $sellerArray);
        $this->assertArrayHasKey('email', $sellerArray);
    }

    /**
     * Testa se as datas são formatadas corretamente.
     */
    public function test_resource_formats_dates_correctly(): void
    {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2025-10-26',
        ]);

        $sale->load('seller');

        $resource = new SaleResource($sale);
        $response = $resource->toArray(Request::create('/test'));

        // sale_date deve ser string no formato Y-m-d
        $this->assertEquals('2025-10-26', $response['sale_date']);

        // created_at e updated_at devem ser ISO8601
        $this->assertStringContainsString('T', $response['created_at']);
        $this->assertStringContainsString('T', $response['updated_at']);
    }

    /**
     * Testa se o Resource calcula a comissão corretamente.
     */
    public function test_resource_calculates_commission_correctly(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 8.5]);
        $sale = Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
        ]);

        $sale->load('seller');

        $resource = new SaleResource($sale);
        $response = $resource->toArray(Request::create('/test'));

        // 8.5% de 1000 = 85.00
        $this->assertEquals('85.00', $response['commission']);
    }

    /**
     * Testa se o Resource usa SellerResource para formatar o seller.
     */
    public function test_resource_uses_seller_resource_for_seller_data(): void
    {
        $seller = Seller::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $sale = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale->load('seller');

        $resource = new SaleResource($sale);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica que seller está formatado como SellerResource
        $this->assertEquals($seller->id, $response['seller']['id']);
        $this->assertEquals('John Doe', $response['seller']['name']);
        $this->assertEquals('john@example.com', $response['seller']['email']);

        // Verifica que SellerResource formata as datas corretamente
        $this->assertArrayHasKey('created_at', $response['seller']);
        $this->assertArrayHasKey('updated_at', $response['seller']);
    }

    /**
     * Testa se o Resource não expõe campos sensíveis ou desnecessários.
     */
    public function test_resource_does_not_expose_sensitive_fields(): void
    {
        $seller = Seller::factory()->create();
        $sale = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale->load('seller');

        $resource = new SaleResource($sale);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica que não expõe seller_id diretamente (apenas dentro do objeto seller)
        // O seller_id é um detalhe de implementação, não deve estar no primeiro nível
        $expectedFields = ['id', 'seller', 'amount', 'commission', 'sale_date', 'created_at', 'updated_at'];
        $this->assertEquals($expectedFields, array_keys($response));
    }
}
