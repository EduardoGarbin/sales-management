<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Seller;
use App\Repositories\SaleRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SaleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SaleRepository();
    }

    /**
     * Testa se getAllPaginatedWithSeller retorna vendas com seller carregado.
     */
    public function test_get_all_paginated_with_seller_loads_relationship(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(3)->create(['seller_id' => $seller->id]);

        $paginator = $this->repository->getAllPaginatedWithSeller(15);
        $sales = $paginator->items();

        $this->assertCount(3, $sales);
        $this->assertTrue($sales[0]->relationLoaded('seller'));
    }

    /**
     * Testa se getAllPaginatedWithSeller ordena em ordem decrescente.
     */
    public function test_get_all_paginated_with_seller_orders_desc(): void
    {
        $seller = Seller::factory()->create();
        $sale1 = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale2 = Sale::factory()->create(['seller_id' => $seller->id]);
        $sale3 = Sale::factory()->create(['seller_id' => $seller->id]);

        $paginator = $this->repository->getAllPaginatedWithSeller(15);
        $sales = $paginator->items();

        $this->assertEquals($sale3->id, $sales[0]->id);
        $this->assertEquals($sale2->id, $sales[1]->id);
        $this->assertEquals($sale1->id, $sales[2]->id);
    }

    /**
     * Testa se getAllPaginatedWithSeller respeita paginação.
     */
    public function test_get_all_paginated_with_seller_respects_per_page(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(10)->create(['seller_id' => $seller->id]);

        $paginator = $this->repository->getAllPaginatedWithSeller(5);

        $this->assertCount(5, $paginator->items());
        $this->assertEquals(10, $paginator->total());
        $this->assertEquals(2, $paginator->lastPage());
    }

    /**
     * Testa se createWithSeller cria venda e carrega seller.
     */
    public function test_create_with_seller_loads_relationship(): void
    {
        $seller = Seller::factory()->create();

        $sale = $this->repository->createWithSeller([
            'seller_id' => $seller->id,
            'amount' => 1000.00,
            'sale_date' => '2025-10-26',
        ]);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertTrue($sale->relationLoaded('seller'));
        $this->assertEquals($seller->id, $sale->seller->id);
        $this->assertEquals(1000.00, $sale->amount);
    }

    /**
     * Testa se getBySeller retorna apenas vendas do vendedor específico.
     */
    public function test_get_by_seller_filters_correctly(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();

        Sale::factory()->count(3)->create(['seller_id' => $seller1->id]);
        Sale::factory()->count(2)->create(['seller_id' => $seller2->id]);

        $paginator1 = $this->repository->getBySeller($seller1->id, 15);
        $paginator2 = $this->repository->getBySeller($seller2->id, 15);

        $this->assertEquals(3, $paginator1->total());
        $this->assertEquals(2, $paginator2->total());
    }

    /**
     * Testa se getBySeller ordena por data de venda decrescente.
     */
    public function test_get_by_seller_orders_by_sale_date_desc(): void
    {
        $seller = Seller::factory()->create();

        $sale1 = Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2025-10-24',
        ]);
        $sale2 = Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2025-10-26',
        ]);
        $sale3 = Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => '2025-10-25',
        ]);

        $paginator = $this->repository->getBySeller($seller->id, 15);
        $sales = $paginator->items();

        $this->assertEquals('2025-10-26', $sales[0]->sale_date->toDateString());
        $this->assertEquals('2025-10-25', $sales[1]->sale_date->toDateString());
        $this->assertEquals('2025-10-24', $sales[2]->sale_date->toDateString());
    }

    /**
     * Testa se getBySeller carrega o relacionamento seller.
     */
    public function test_get_by_seller_loads_seller_relationship(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->create(['seller_id' => $seller->id]);

        $paginator = $this->repository->getBySeller($seller->id, 15);
        $sales = $paginator->items();

        $this->assertTrue($sales[0]->relationLoaded('seller'));
    }

    /**
     * Testa se getBySellerAndDate retorna vendas corretas.
     */
    public function test_get_by_seller_and_date_filters_correctly(): void
    {
        $seller = Seller::factory()->create();
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        Sale::factory()->count(2)->create([
            'seller_id' => $seller->id,
            'sale_date' => $today,
        ]);

        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => $yesterday,
        ]);

        $todaySales = $this->repository->getBySellerAndDate($seller->id, $today);
        $yesterdaySales = $this->repository->getBySellerAndDate($seller->id, $yesterday);

        $this->assertCount(2, $todaySales);
        $this->assertCount(1, $yesterdaySales);
    }

    /**
     * Testa se getBySellerAndDate não retorna vendas de outros vendedores.
     */
    public function test_get_by_seller_and_date_excludes_other_sellers(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        $date = Carbon::today()->toDateString();

        Sale::factory()->count(2)->create([
            'seller_id' => $seller1->id,
            'sale_date' => $date,
        ]);

        Sale::factory()->count(3)->create([
            'seller_id' => $seller2->id,
            'sale_date' => $date,
        ]);

        $seller1Sales = $this->repository->getBySellerAndDate($seller1->id, $date);
        $seller2Sales = $this->repository->getBySellerAndDate($seller2->id, $date);

        $this->assertCount(2, $seller1Sales);
        $this->assertCount(3, $seller2Sales);
    }
}
