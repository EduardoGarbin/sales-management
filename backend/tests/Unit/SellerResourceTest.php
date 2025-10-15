<?php

namespace Tests\Unit;

use App\Http\Resources\SellerResource;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SellerResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se o Resource retorna apenas os campos definidos com valores corretos.
     *
     * Valida o contrato da API garantindo que:
     * - Apenas os campos esperados são retornados
     * - Os valores são transformados corretamente
     * - A estrutura está conforme especificado
     */
    public function test_resource_returns_only_defined_fields_with_correct_values(): void
    {
        $seller = Seller::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        $resource = new SellerResource($seller);
        $request = Request::create('/api/sellers', 'GET');

        $array = $resource->toArray($request);

        // Valida estrutura: deve ter exatamente 5 campos
        $this->assertCount(5, $array);
        $expectedKeys = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $this->assertEquals($expectedKeys, array_keys($array));

        // Valida valores
        $this->assertEquals($seller->id, $array['id']);
        $this->assertEquals('Maria Santos', $array['name']);
        $this->assertEquals('maria@example.com', $array['email']);
        $this->assertNotNull($array['created_at']);
        $this->assertNotNull($array['updated_at']);
    }

    /**
     * Testa se as datas são formatadas em ISO 8601.
     *
     * Valida a transformação específica de datas para o padrão internacional.
     */
    public function test_resource_formats_dates_to_iso8601(): void
    {
        $seller = Seller::factory()->create();

        $resource = new SellerResource($seller);
        $request = Request::create('/api/sellers', 'GET');

        $array = $resource->toArray($request);

        // Verifica se está no formato ISO 8601 (ex: 2025-10-14T12:30:00+00:00)
        $iso8601Pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/';

        $this->assertMatchesRegularExpression($iso8601Pattern, $array['created_at']);
        $this->assertMatchesRegularExpression($iso8601Pattern, $array['updated_at']);
    }
}
