<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser()
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Testa se o endpoint GET /api/sales retorna a estrutura JSON correta.
     */
    public function test_endpoint_returns_sales_with_correct_structure_and_data(): void
    {
        $seller = Seller::factory()->create(['name' => 'Eduardo Garbin']);

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2025-10-14',
        ]);

        $response = $this->actingAsUser()->getJson('/api/sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'seller',
                        'amount',
                        'commission',
                        'sale_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'amount' => '1000.00',
                'commission' => '85.00', // 8.5% de 1000
            ]);
    }

    /**
     * Testa se retorna array vazio quando não há vendas.
     */
    public function test_returns_empty_array_when_no_sales_exist(): void
    {
        $response = $this->actingAsUser()->getJson('/api/sales');

        $response->assertStatus(200)
            ->assertJson(['data' => []])
            ->assertJsonCount(0, 'data');
    }

    /**
     * Testa se cadastra uma venda com sucesso.
     */
    public function test_creates_sale_successfully(): void
    {
        $seller = Seller::factory()->create();

        $data = [
            'seller_id' => $seller->id,
            'amount' => 1500.00,
            'sale_date' => '2025-10-14',
        ];

        $response = $this->actingAsUser()->postJson('/api/sales', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'seller',
                    'amount',
                    'commission',
                    'sale_date',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'amount' => '1500.00',
                'commission' => '127.50', // 8.5% de 1500
                'sale_date' => '2025-10-14',
            ]);

        $this->assertDatabaseCount('sales', 1);

        $sale = Sale::first();
        $this->assertEquals($seller->id, $sale->seller_id);
        $this->assertEquals(1500.00, $sale->amount);
        $this->assertEquals('2025-10-14', $sale->sale_date->format('Y-m-d'));
    }

    /**
     * Testa validação: seller_id é obrigatório.
     */
    public function test_create_sale_requires_seller_id(): void
    {
        $response = $this->actingAsUser()->postJson('/api/sales', [
            'amount' => 1000.00,
            'sale_date' => '2025-10-14',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seller_id']);
    }

    /**
     * Testa validação: seller_id deve existir.
     */
    public function test_create_sale_requires_existing_seller(): void
    {
        $response = $this->actingAsUser()->postJson('/api/sales', [
            'seller_id' => 99999,
            'amount' => 1000.00,
            'sale_date' => '2025-10-14',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seller_id']);
    }

    /**
     * Testa validação: amount é obrigatório.
     */
    public function test_create_sale_requires_amount(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'sale_date' => '2025-10-14',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Testa validação: amount deve ser maior que zero.
     */
    public function test_create_sale_requires_positive_amount(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'amount' => 0,
            'sale_date' => '2025-10-14',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /**
     * Testa validação: sale_date é obrigatório.
     */
    public function test_create_sale_requires_sale_date(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_date']);
    }

    /**
     * Testa validação: sale_date não pode ser futuro.
     */
    public function test_create_sale_rejects_future_date(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAsUser()->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2099-12-31',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_date']);
    }

    /**
     * Testa endpoint de vendas por vendedor - integração da API.
     *
     * Valida que o endpoint retorna a estrutura correta e status HTTP adequado.
     * A lógica de filtro já está testada no SaleServiceTest (teste unitário).
     */
    public function test_get_sales_by_seller_endpoint_works(): void
    {
        $seller = Seller::factory()->create();

        Sale::factory()->count(2)->create(['seller_id' => $seller->id]);

        $response = $this->actingAsUser()->getJson("/api/sellers/{$seller->id}/sales");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'seller',
                        'amount',
                        'commission',
                        'sale_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }
}
