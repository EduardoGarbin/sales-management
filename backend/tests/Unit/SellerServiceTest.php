<?php

namespace Tests\Unit;

use App\Models\Seller;
use App\Services\SellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Testes para SellerService.
 *
 * Este teste foca na LÓGICA DE NEGÓCIO do Service:
 * - Gerenciamento de cache (criação, invalidação)
 * - Orquestração de operações (create + clearCache)
 *
 * Testes de queries, ordenação e persistência estão em SellerRepositoryTest.
 */
class SellerServiceTest extends TestCase
{
    use RefreshDatabase;

    private SellerService $sellerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sellerService = app(SellerService::class);
    }

    /**
     * Testa se getAllSellers utiliza cache corretamente.
     *
     * Valida a lógica de negócio de caching implementada no Service.
     */
    public function test_get_all_sellers_uses_cache(): void
    {
        Seller::factory()->create();

        // Primeira chamada - deve criar o cache
        $this->sellerService->getAllSellers(1, 15);
        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));

        // Segunda chamada - deve usar cache
        $this->sellerService->getAllSellers(1, 15);
        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));
    }

    /**
     * Testa se clearCache invalida corretamente o cache de múltiplas páginas.
     *
     * Valida que o método clearCache limpa caches de diferentes configurações de paginação.
     */
    public function test_clear_cache_invalidates_multiple_page_sizes(): void
    {
        // Cria caches para diferentes configurações
        $this->sellerService->getAllSellers(1, 15);
        $this->sellerService->getAllSellers(2, 15);
        $this->sellerService->getAllSellers(1, 10);

        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));
        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_2_per_15'));
        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_1_per_10'));

        // Limpa o cache
        $this->sellerService->clearCache();

        // Todos os caches devem ter sido limpos
        $this->assertFalse(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));
        $this->assertFalse(Cache::tags(['sellers'])->has('sellers_list_page_2_per_15'));
        $this->assertFalse(Cache::tags(['sellers'])->has('sellers_list_page_1_per_10'));
    }

    /**
     * Testa se clearCache força uma nova busca no banco.
     *
     * Valida que após limpar o cache, dados atualizados são retornados.
     */
    public function test_clear_cache_forces_database_fetch(): void
    {
        Seller::factory()->create(['name' => 'Vendedor Original']);

        // Cria cache
        $cached = $this->sellerService->getAllSellers(1, 15);
        $this->assertCount(1, $cached->items());

        // Adiciona vendedor após cachear
        Seller::factory()->create(['name' => 'Vendedor Novo']);

        // Ainda retorna cache antigo (1 vendedor)
        $stillCached = $this->sellerService->getAllSellers(1, 15);
        $this->assertCount(1, $stillCached->items());

        // Limpa cache
        $this->sellerService->clearCache();

        // Agora busca do banco e retorna 2 vendedores
        $fresh = $this->sellerService->getAllSellers(1, 15);
        $this->assertEquals(2, $fresh->total());
    }

    /**
     * Testa se createSeller invalida o cache automaticamente.
     *
     * Valida a lógica de negócio: criar vendedor deve invalidar cache
     * para garantir que getAllSellers retorne dados atualizados.
     */
    public function test_create_seller_invalidates_cache(): void
    {
        // Cria cache inicial
        $this->sellerService->getAllSellers(1, 15);
        $this->assertTrue(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));

        // Cria vendedor (deve invalidar cache)
        $this->sellerService->createSeller([
            'name' => 'New Seller',
            'email' => 'new@example.com',
        ]);

        // Cache deve ter sido invalidado
        $this->assertFalse(Cache::tags(['sellers'])->has('sellers_list_page_1_per_15'));
    }

    /**
     * Testa se createSeller retorna instância de Seller.
     *
     * Valida que o Service orquestra corretamente Repository + Cache.
     */
    public function test_create_seller_returns_seller_instance(): void
    {
        $seller = $this->sellerService->createSeller([
            'name' => 'Test Seller',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(Seller::class, $seller);
        $this->assertNotNull($seller->id);
    }
}
