# Makefile para facilitar comandos Docker do projeto Laravel
# Simplifica a experiência de desenvolvimento

.PHONY: help build up down restart logs shell artisan composer npm test clean

# Comando padrão ao rodar apenas 'make'
help: ## Exibir ajuda com todos os comandos disponíveis
	@echo "Comandos disponíveis:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

# Comandos Docker Compose
build: ## Construir as imagens Docker
	docker compose -f compose.dev.yaml build

up: ## Iniciar os containers
	docker compose -f compose.dev.yaml up -d

down: ## Parar os containers
	docker compose -f compose.dev.yaml down

restart: down up ## Reiniciar os containers

logs: ## Ver logs de todos os containers
	docker compose -f compose.dev.yaml logs -f

ps: ## Listar status dos containers
	docker compose -f compose.dev.yaml ps

# Acesso aos containers
shell: ## Acessar shell do container workspace
	docker exec -it laravel-workspace bash

# Comandos Laravel
artisan: ## Executar comando Artisan (ex: make artisan cmd="migrate")
	docker exec laravel-workspace php artisan $(cmd)

migrate: ## Executar migrations
	docker exec laravel-workspace php artisan migrate

migrate-fresh: ## Recriar banco de dados e executar migrations
	docker exec laravel-workspace php artisan migrate:fresh

migrate-rollback: ## Reverter última migration
	docker exec laravel-workspace php artisan migrate:rollback

seed: ## Executar seeders
	docker exec laravel-workspace php artisan db:seed

seed-class: ## Executar seeder específica (ex: make seed-class name="SaleSeeder")
	docker exec laravel-workspace php artisan db:seed --class=$(name)

migrate-seed: ## Executar migrations e seeders
	docker exec laravel-workspace php artisan migrate --seed

db-reset: ## Resetar banco (fresh + seed)
	docker exec laravel-workspace php artisan migrate:fresh --seed

db-status: ## Mostrar status das migrations
	docker exec laravel-workspace php artisan migrate:status

db-count-sellers: ## Contar vendedores no banco
	@docker exec laravel-workspace php artisan tinker --execute="echo 'Total de vendedores: ' . \App\Models\Seller::count() . PHP_EOL;"

db-list-sellers: ## Listar vendedores do banco
	@docker exec laravel-workspace php artisan tinker --execute="\App\Models\Seller::all()->each(fn(\$$s) => print(\$$s->id . ' - ' . \$$s->name . ' (' . \$$s->email . ')' . PHP_EOL));"

tinker: ## Acessar Laravel Tinker
	docker exec -it laravel-workspace php artisan tinker

# Geradores Laravel
make-model: ## Criar model (ex: make make-model name="Product")
	docker exec laravel-workspace php artisan make:model $(name)

make-migration: ## Criar migration (ex: make make-migration name="create_products_table")
	docker exec laravel-workspace php artisan make:migration $(name)

make-controller: ## Criar controller (ex: make make-controller name="ProductController")
	docker exec laravel-workspace php artisan make:controller $(name)

make-seeder: ## Criar seeder (ex: make make-seeder name="ProductSeeder")
	docker exec laravel-workspace php artisan make:seeder $(name)

make-factory: ## Criar factory (ex: make make-factory name="ProductFactory")
	docker exec laravel-workspace php artisan make:factory $(name)

make-request: ## Criar form request (ex: make make-request name="StoreSellerRequest")
	docker exec laravel-workspace php artisan make:request $(name)

make-resource: ## Criar API resource (ex: make make-resource name="SellerResource")
	docker exec laravel-workspace php artisan make:resource $(name)

make-test: ## Criar teste (ex: make make-test name="SellerTest")
	docker exec laravel-workspace php artisan make:test $(name)

make-test-unit: ## Criar teste unitário (ex: make make-test-unit name="SellerServiceTest")
	docker exec laravel-workspace php artisan make:test $(name) --unit

make-service: ## Criar service (arquivo manual) (ex: make make-service name="SellerService")
	@echo "<?php\n\nnamespace App\Services;\n\nclass $(name)\n{\n    //\n}" > app/Services/$(name).php
	@echo "Service $(name) criado em app/Services/$(name).php"

# Comandos Composer
composer: ## Executar comando Composer (ex: make composer cmd="require vendor/package")
	docker exec laravel-workspace composer $(cmd)

composer-install: ## Instalar dependências PHP
	docker exec laravel-workspace composer install

composer-update: ## Atualizar dependências PHP
	docker exec laravel-workspace composer update

# Comandos NPM
npm: ## Executar comando NPM (ex: make npm cmd="install")
	docker exec laravel-workspace npm $(cmd)

npm-install: ## Instalar dependências Node
	docker exec laravel-workspace npm install

npm-dev: ## Executar NPM dev
	docker exec laravel-workspace npm run dev

npm-build: ## Build de produção do frontend
	docker exec laravel-workspace npm run build

# Testes
test: ## Executar todos os testes PHPUnit
	docker exec laravel-workspace php artisan test

test-unit: ## Executar apenas testes unitários
	docker exec laravel-workspace php artisan test --testsuite=Unit

test-feature: ## Executar apenas testes de feature
	docker exec laravel-workspace php artisan test --testsuite=Feature

# Limpeza e manutenção
clean: ## Parar containers e remover volumes
	docker compose -f compose.dev.yaml down -v

cache-clear: ## Limpar cache do Laravel
	docker exec laravel-workspace php artisan cache:clear
	docker exec laravel-workspace php artisan config:clear
	docker exec laravel-workspace php artisan route:clear
	docker exec laravel-workspace php artisan view:clear

fix-permissions: ## Corrigir permissões dos arquivos
	docker run --rm -v $(PWD):/app -w /app alpine:latest chown -R $(shell id -u):$(shell id -g) .

# Setup inicial
setup: build up composer-install npm-install ## Setup completo do projeto
	@echo "Projeto configurado com sucesso!"
	@echo "Acesse: http://localhost:8080"

fresh: clean setup migrate-seed ## Setup completo com banco de dados zerado
