<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser()
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Testa se o endpoint GET /api/sellers retorna a estrutura JSON correta.
     *
     * Este teste valida o contrato da API (integração completa):
     * - Status HTTP 200
     * - Estrutura do envelope "data"
     * - Campos retornados por vendedor
     * - Valores corretos
     */
    public function test_endpoint_returns_sellers_with_correct_structure_and_data(): void
    {
        Seller::factory()->create([
            'name' => 'Eduardo Garbin',
            'email' => 'eduardo@example.com',
        ]);

        $response = $this->actingAsUser()->getJson('/api/sellers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Eduardo Garbin',
                'email' => 'eduardo@example.com',
            ]);
    }

    /**
     * Testa se retorna array vazio quando não há vendedores.
     *
     * Caso de borda importante: valida que a API responde corretamente
     * mesmo quando não há dados no banco.
     */
    public function test_returns_empty_array_when_no_sellers_exist(): void
    {
        $response = $this->actingAsUser()->getJson('/api/sellers');

        $response->assertStatus(200)
            ->assertJson(['data' => []])
            ->assertJsonCount(0, 'data');
    }

    /**
     * Testa se cadastra um vendedor com sucesso.
     *
     * Valida a resposta HTTP do endpoint:
     * - Status 201 (Created)
     * - Estrutura JSON correta
     * - Dados retornados correspondem aos enviados
     *
     * Nota: Persistência em banco é validada no SellerServiceTest (Unit Test)
     */
    public function test_creates_seller_successfully(): void
    {
        $data = [
            'name' => 'Eduardo Garbin',
            'email' => 'eduardo@example.com',
        ];

        $response = $this->actingAsUser()->postJson('/api/sellers', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Eduardo Garbin',
                'email' => 'eduardo@example.com',
            ]);
    }

    /**
     * Testa validação: nome é obrigatório.
     */
    public function test_create_seller_requires_name(): void
    {
        $response = $this->actingAsUser()->postJson('/api/sellers', [
            'email' => 'eduardo@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Testa validação: email é obrigatório.
     */
    public function test_create_seller_requires_email(): void
    {
        $response = $this->actingAsUser()->postJson('/api/sellers', [
            'name' => 'Eduardo Garbin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa validação: email deve ser válido.
     */
    public function test_create_seller_requires_valid_email(): void
    {
        $response = $this->actingAsUser()->postJson('/api/sellers', [
            'name' => 'Eduardo Garbin',
            'email' => 'email-invalido',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Testa validação: email deve ser único.
     */
    public function test_create_seller_requires_unique_email(): void
    {
        Seller::factory()->create(['email' => 'eduardo@example.com']);

        $response = $this->actingAsUser()->postJson('/api/sellers', [
            'name' => 'Outro Eduardo',
            'email' => 'eduardo@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
