<?php

namespace App\Providers;

use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\SellerRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\SaleRepository;
use App\Repositories\SellerRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para registro de Repositories.
 *
 * Registra os bindings entre interfaces e suas implementações concretas,
 * permitindo injeção de dependência através das interfaces.
 *
 * Este padrão facilita:
 * - Troca de implementações sem alterar código consumidor
 * - Testes com mock de repositories
 * - Inversão de dependência (SOLID)
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registra os bindings no container de serviços.
     *
     * Liga cada interface de repositório à sua implementação concreta.
     * O Laravel irá resolver automaticamente as dependências quando
     * uma classe solicitar a interface no construtor.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            SellerRepositoryInterface::class,
            SellerRepository::class
        );

        $this->app->bind(
            SaleRepositoryInterface::class,
            SaleRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }

    /**
     * Bootstrap de serviços.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
