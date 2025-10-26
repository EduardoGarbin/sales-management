<?php

namespace Tests\Unit;

use App\Http\Resources\CommissionReportResource;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class CommissionReportResourceTest extends TestCase
{
    /**
     * Testa se o Resource retorna a estrutura correta com todos os campos.
     */
    public function test_resource_returns_correct_structure(): void
    {
        $seller = Seller::factory()->make([
            'id' => 1,
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
        ]);

        $date = Carbon::parse('2025-10-26');

        $data = [
            'seller' => $seller,
            'date' => $date,
            'sales_count' => 5,
            'total_amount' => 10000.00,
            'total_commission' => 850.00,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica estrutura
        $this->assertArrayHasKey('seller', $response);
        $this->assertArrayHasKey('date', $response);
        $this->assertArrayHasKey('sales_count', $response);
        $this->assertArrayHasKey('total_amount', $response);
        $this->assertArrayHasKey('total_commission', $response);

        // Verifica valores
        $this->assertEquals(5, $response['sales_count']);
        $this->assertEquals(10000.00, $response['total_amount']);
        $this->assertEquals(850.00, $response['total_commission']);
    }

    /**
     * Testa se o Resource formata corretamente os dados do seller.
     */
    public function test_resource_formats_seller_data_correctly(): void
    {
        $seller = Seller::factory()->make([
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $data = [
            'seller' => $seller,
            'date' => Carbon::now(),
            'sales_count' => 0,
            'total_amount' => 0,
            'total_commission' => 0,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica estrutura do seller
        $this->assertIsArray($response['seller']);
        $this->assertArrayHasKey('id', $response['seller']);
        $this->assertArrayHasKey('name', $response['seller']);
        $this->assertArrayHasKey('email', $response['seller']);

        // Verifica valores do seller
        $this->assertEquals(10, $response['seller']['id']);
        $this->assertEquals('John Doe', $response['seller']['name']);
        $this->assertEquals('john@example.com', $response['seller']['email']);

        // Verifica que não expõe campos desnecessários do seller
        $this->assertArrayNotHasKey('commission_rate', $response['seller']);
        $this->assertArrayNotHasKey('created_at', $response['seller']);
        $this->assertArrayNotHasKey('updated_at', $response['seller']);
    }

    /**
     * Testa se o Resource formata a data corretamente (d/m/Y).
     */
    public function test_resource_formats_date_correctly(): void
    {
        $seller = Seller::factory()->make();
        $date = Carbon::parse('2025-10-26');

        $data = [
            'seller' => $seller,
            'date' => $date,
            'sales_count' => 1,
            'total_amount' => 100,
            'total_commission' => 10,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource->toArray(Request::create('/test'));

        // Verifica formato brasileiro: dd/mm/yyyy
        $this->assertEquals('26/10/2025', $response['date']);
    }

    /**
     * Testa se o Resource lida corretamente com zero vendas.
     */
    public function test_resource_handles_zero_sales_correctly(): void
    {
        $seller = Seller::factory()->make();

        $data = [
            'seller' => $seller,
            'date' => Carbon::today(),
            'sales_count' => 0,
            'total_amount' => 0.00,
            'total_commission' => 0.00,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource->toArray(Request::create('/test'));

        $this->assertEquals(0, $response['sales_count']);
        $this->assertEquals(0.00, $response['total_amount']);
        $this->assertEquals(0.00, $response['total_commission']);
    }

    /**
     * Testa se o Resource preserva valores decimais corretamente.
     */
    public function test_resource_preserves_decimal_values(): void
    {
        $seller = Seller::factory()->make();

        $data = [
            'seller' => $seller,
            'date' => Carbon::today(),
            'sales_count' => 3,
            'total_amount' => 1234.56,
            'total_commission' => 104.94,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource->toArray(Request::create('/test'));

        $this->assertEquals(1234.56, $response['total_amount']);
        $this->assertEquals(104.94, $response['total_commission']);
    }

    /**
     * Testa se o Resource pode ser usado com o método additional().
     */
    public function test_resource_works_with_additional_data(): void
    {
        $seller = Seller::factory()->make();

        $data = [
            'seller' => $seller,
            'date' => Carbon::today(),
            'sales_count' => 2,
            'total_amount' => 500,
            'total_commission' => 50,
        ];

        $resource = new CommissionReportResource($data);
        $response = $resource
            ->additional(['message' => 'E-mail de comissão reenviado com sucesso'])
            ->toArray(Request::create('/test'));

        // Verifica que o additional não interfere nos dados principais
        $this->assertArrayHasKey('seller', $response);
        $this->assertArrayHasKey('sales_count', $response);

        // O método additional adiciona dados no nível superior quando usado com response()
        // mas no toArray() eles não aparecem diretamente
        // Este teste documenta o comportamento esperado
    }

    /**
     * Testa consistência com diferentes datas.
     */
    public function test_resource_handles_different_dates_correctly(): void
    {
        $seller = Seller::factory()->make();

        $testDates = [
            '2025-01-01' => '01/01/2025',
            '2025-12-31' => '31/12/2025',
            '2025-02-28' => '28/02/2025',
            '2024-02-29' => '29/02/2024', // Ano bissexto
        ];

        foreach ($testDates as $inputDate => $expectedOutput) {
            $data = [
                'seller' => $seller,
                'date' => Carbon::parse($inputDate),
                'sales_count' => 1,
                'total_amount' => 100,
                'total_commission' => 10,
            ];

            $resource = new CommissionReportResource($data);
            $response = $resource->toArray(Request::create('/test'));

            $this->assertEquals(
                $expectedOutput,
                $response['date'],
                "Failed for date: {$inputDate}"
            );
        }
    }
}
