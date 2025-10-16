<?php

namespace Tests\Unit;

use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SellerServiceTest extends TestCase
{
    use RefreshDatabase;

    private SellerService $sellerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sellerService = new SellerService();
    }

    /**
     * Testa se os vendedores são retornados em ordem decrescente por ID com paginação.
     *
     * Este teste valida a regra de negócio de ordenação implementada no Service.
     */
    public function test_sellers_are_ordered_by_id_descending(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        $seller3 = Seller::factory()->create();

        $paginator = $this->sellerService->getAllSellers();
        $sellers = $paginator->items();

        $this->assertEquals($seller3->id, $sellers[0]->id);
        $this->assertEquals($seller2->id, $sellers[1]->id);
        $this->assertEquals($seller1->id, $sellers[2]->id);
        $this->assertEquals(3, $paginator->total());
    }

    /**
     * Testa se clearCache limpa o cache corretamente com paginação.
     *
     * Valida que após limpar o cache, uma nova consulta busca dados atualizados do banco.
     */
    public function test_clear_cache_forces_database_fetch(): void
    {
        Seller::factory()->create(['name' => 'Vendedor Original']);

        // Cria o cache para página 1
        $this->sellerService->getAllSellers(1, 15);
        $this->assertTrue(Cache::has('sellers_list_page_1_per_15'));

        // Adiciona mais um vendedor (não aparecerá no cache)
        Seller::factory()->create(['name' => 'Vendedor Novo']);

        // Ainda deve ter apenas 1 no cache
        $cached = $this->sellerService->getAllSellers(1, 15);
        $this->assertCount(1, $cached->items());

        // Limpa o cache
        $this->sellerService->clearCache();
        $this->assertFalse(Cache::has('sellers_list_page_1_per_15'));

        // Agora deve buscar do banco e retornar 2 vendedores
        $fresh = $this->sellerService->getAllSellers(1, 15);
        $this->assertEquals(2, $fresh->total());
    }

    /**
     * Testa se createSeller cria um vendedor corretamente.
     *
     * Valida que o vendedor é salvo no banco com os dados corretos.
     */
    public function test_create_seller_stores_seller_in_database(): void
    {
        $data = [
            'name' => 'Eduardo Garbin',
            'email' => 'eduardo@example.com',
        ];

        $seller = $this->sellerService->createSeller($data);

        $this->assertInstanceOf(Seller::class, $seller);
        $this->assertEquals('Eduardo Garbin', $seller->name);
        $this->assertEquals('eduardo@example.com', $seller->email);
        $this->assertNotNull($seller->id);

        // Verifica se está no banco
        $this->assertDatabaseHas('sellers', [
            'name' => 'Eduardo Garbin',
            'email' => 'eduardo@example.com',
        ]);
    }

    /**
     * Testa se createSeller limpa o cache após criar vendedor com paginação.
     *
     * Garante que o cache é invalidado para refletir o novo vendedor.
     */
    public function test_create_seller_clears_cache(): void
    {
        // Cria cache inicial para página 1
        $this->sellerService->getAllSellers(1, 15);
        $this->assertTrue(Cache::has('sellers_list_page_1_per_15'));

        // Cria novo vendedor
        $this->sellerService->createSeller([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
        ]);

        // Cache deve ter sido limpo
        $this->assertFalse(Cache::has('sellers_list_page_1_per_15'));
    }
}
