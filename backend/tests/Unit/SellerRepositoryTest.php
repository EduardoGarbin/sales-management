<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Seller;
use App\Repositories\SellerRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SellerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SellerRepository();
    }

    /**
     * Testa se getAllPaginated retorna vendedores em ordem decrescente.
     */
    public function test_get_all_paginated_returns_sellers_ordered_desc(): void
    {
        $seller1 = Seller::factory()->create();
        $seller2 = Seller::factory()->create();
        $seller3 = Seller::factory()->create();

        $paginator = $this->repository->getAllPaginated(1, 15);
        $sellers = $paginator->items();

        $this->assertCount(3, $sellers);
        $this->assertEquals($seller3->id, $sellers[0]->id);
        $this->assertEquals($seller2->id, $sellers[1]->id);
        $this->assertEquals($seller1->id, $sellers[2]->id);
    }

    /**
     * Testa se getAllPaginated respeita a paginação.
     */
    public function test_get_all_paginated_respects_per_page(): void
    {
        Seller::factory()->count(10)->create();

        $paginator = $this->repository->getAllPaginated(1, 5);

        $this->assertCount(5, $paginator->items());
        $this->assertEquals(10, $paginator->total());
        $this->assertEquals(2, $paginator->lastPage());
    }

    /**
     * Testa se findWithSales carrega o relacionamento.
     */
    public function test_find_with_sales_loads_relationship(): void
    {
        $seller = Seller::factory()->create();
        Sale::factory()->count(3)->create(['seller_id' => $seller->id]);

        $result = $this->repository->findWithSales($seller->id);

        $this->assertTrue($result->relationLoaded('sales'));
        $this->assertCount(3, $result->sales);
    }

    /**
     * Testa se getSalesByDate retorna apenas vendas da data especificada.
     */
    public function test_get_sales_by_date_filters_correctly(): void
    {
        $seller = Seller::factory()->create();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Vendas de hoje
        Sale::factory()->count(2)->create([
            'seller_id' => $seller->id,
            'sale_date' => $today,
        ]);

        // Vendas de ontem
        Sale::factory()->create([
            'seller_id' => $seller->id,
            'sale_date' => $yesterday,
        ]);

        $todaySales = $this->repository->getSalesByDate($seller->id, $today->toDateString());
        $yesterdaySales = $this->repository->getSalesByDate($seller->id, $yesterday->toDateString());

        $this->assertCount(2, $todaySales);
        $this->assertCount(1, $yesterdaySales);
    }

    /**
     * Testa se emailExists detecta email já cadastrado.
     */
    public function test_email_exists_returns_true_for_existing_email(): void
    {
        Seller::factory()->create(['email' => 'existing@example.com']);

        $exists = $this->repository->emailExists('existing@example.com');

        $this->assertTrue($exists);
    }

    /**
     * Testa se emailExists retorna false para email não cadastrado.
     */
    public function test_email_exists_returns_false_for_non_existing_email(): void
    {
        $exists = $this->repository->emailExists('nonexistent@example.com');

        $this->assertFalse($exists);
    }

    /**
     * Testa se emailExists ignora o próprio ID na verificação.
     */
    public function test_email_exists_excludes_own_id(): void
    {
        $seller = Seller::factory()->create(['email' => 'test@example.com']);

        // Verificando o mesmo email mas excluindo o próprio seller
        $exists = $this->repository->emailExists('test@example.com', $seller->id);

        $this->assertFalse($exists);
    }

    /**
     * Testa os métodos CRUD herdados do BaseRepository.
     */
    public function test_base_repository_crud_methods(): void
    {
        // Create
        $seller = $this->repository->create([
            'name' => 'Test Seller',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(Seller::class, $seller);
        $this->assertEquals('Test Seller', $seller->name);

        // Find
        $found = $this->repository->find($seller->id);
        $this->assertNotNull($found);
        $this->assertEquals($seller->id, $found->id);

        // FindOrFail
        $foundOrFail = $this->repository->findOrFail($seller->id);
        $this->assertEquals($seller->id, $foundOrFail->id);

        // Update
        $updated = $this->repository->update($seller->id, ['name' => 'Updated Name']);
        $this->assertEquals('Updated Name', $updated->name);

        // All
        $all = $this->repository->all();
        $this->assertCount(1, $all);

        // Delete
        $deleted = $this->repository->delete($seller->id);
        $this->assertTrue($deleted);
        $this->assertNull($this->repository->find($seller->id));
    }
}
