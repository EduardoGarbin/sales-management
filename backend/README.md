# Sales Management System

Sistema de gerenciamento de vendas desenvolvido com Laravel e Docker.

## Sobre o Projeto

Sistema de gerenciamento de vendas construído com Laravel 12 e Docker, demonstrando conhecimentos em:

- **Arquitetura Limpa**: Service Layer Pattern, API Resources e Form Requests
- **Desenvolvimento Backend**: Laravel 12 com PHP 8.3 e boas práticas
- **API RESTful**: Endpoints para gerenciamento de vendedores com validação robusta
- **Testes Automatizados**: Cobertura completa com testes unitários e de integração (14 testes, 63 assertions)
- **Containerização**: Docker Compose com múltiplos serviços e health checks
- **Cache Strategy**: Implementação de cache com Redis para otimização de performance
- **Developer Experience**: Makefile com 40+ comandos para facilitar o desenvolvimento

## Tecnologias Utilizadas

- **Laravel 12.33** - Framework PHP moderno
- **PHP 8.3** - Linguagem de programação com typed properties
- **MySQL 8.0** - Banco de dados relacional
- **Redis 7** - Cache e sessões
- **Nginx** - Servidor web de alta performance
- **Docker & Docker Compose** - Containerização e orquestração
- **PHPUnit 11** - Framework de testes
- **Node.js & NPM** - Gerenciamento de assets frontend

## Requisitos

- Docker Desktop instalado e rodando
- Make (opcional, mas recomendado)

## Instalação e Configuração

### 1. Clone o Repositório

```bash
git clone <seu-repositorio>
cd sales-management
```

### 2. Configure as Variáveis de Ambiente

```bash
cp .env.docker .env
```

Ajuste as configurações no arquivo `.env` se necessário.

### 3. Inicie o Ambiente Docker

**Opção A: Usando Makefile (Recomendado)**

```bash
make setup
```

Este comando irá:
- Construir as imagens Docker
- Iniciar os containers
- Instalar dependências PHP (Composer)
- Instalar dependências Node (NPM)

**Opção B: Usando Docker Compose diretamente**

```bash
docker compose -f compose.dev.yaml build
docker compose -f compose.dev.yaml up -d
docker exec laravel-workspace composer install
docker exec laravel-workspace npm install
```

### 4. Configure o Banco de Dados

Execute as migrations e seeders para criar as tabelas e popular com dados de exemplo:

```bash
make migrate-seed
```

Ou manualmente:

```bash
docker exec laravel-workspace php artisan migrate --seed
```

### 5. Execute os Testes

Valide que tudo está funcionando executando os testes:

```bash
make test
```

### 6. Acesse a Aplicação

A API estará disponível em:

```
http://localhost:8080
```

**Endpoints disponíveis:**
- `GET /api/sellers` - Listar todos os vendedores
- `POST /api/sellers` - Criar novo vendedor

## Estrutura do Projeto

```
sales-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/   # Controllers da API
│   │   ├── Requests/          # Form Requests (validação)
│   │   └── Resources/         # API Resources (transformação)
│   ├── Models/                # Eloquent Models
│   └── Services/              # Service Layer (lógica de negócio)
├── database/
│   ├── factories/             # Model Factories para testes
│   ├── migrations/            # Migrations do banco de dados
│   └── seeders/               # Seeders para dados de exemplo
├── docker/                    # Configurações Docker
│   ├── php/                   # Dockerfiles PHP-FPM e Workspace
│   └── nginx/                 # Configuração Nginx
├── routes/
│   └── api.php                # Rotas da API REST
├── tests/
│   ├── Feature/               # Testes de integração
│   └── Unit/                  # Testes unitários
├── compose.dev.yaml           # Docker Compose (desenvolvimento)
├── Makefile                   # Comandos simplificados (40+ comandos)
├── phpunit.xml                # Configuração PHPUnit
└── .env.docker                # Variáveis de ambiente exemplo
```

## Serviços Docker

O ambiente inclui os seguintes serviços:

| Serviço | Descrição | Porta |
|---------|-----------|-------|
| **nginx** | Servidor web | 8080 |
| **php-fpm** | Processador PHP 8.3 | - |
| **mysql** | Banco de dados MySQL 8.0 | 3307 |
| **redis** | Cache e sessões | 6379 |
| **workspace** | Container CLI para comandos | - |

## Comandos Essenciais

O projeto inclui um **Makefile** que simplifica comandos Docker comuns.

### Setup Inicial do Ambiente

```bash
make setup              # Setup completo (build + up + install dependencies)
make fresh              # Setup completo com banco zerado e migrations + seeders
make build              # Construir imagens Docker
make up                 # Iniciar containers
make down               # Parar containers
make restart            # Reiniciar containers
```

### Database

```bash
make migrate            # Executar migrations
make migrate-fresh      # Recriar banco de dados (limpa tudo)
make seed               # Executar todas as seeders
make migrate-seed       # Executar migrations e seeders juntos
make db-reset           # Resetar banco (fresh + seed)
make db-status          # Mostrar status das migrations
```

---

## Comandos de Desenvolvimento

### Gerenciar Containers

```bash
make help               # Ver todos os comandos disponíveis
make restart            # Reiniciar containers
make ps                 # Status dos containers
make logs               # Ver logs em tempo real
```

### Acessar Shell

```bash
make shell              # Shell do workspace (principal para comandos Artisan)
```

### Geradores Laravel

```bash
make make-model name="Product"                    # Criar model
make make-migration name="create_products_table"  # Criar migration
make make-controller name="ProductController"     # Criar controller
make make-seeder name="ProductSeeder"             # Criar seeder
make make-factory name="ProductFactory"           # Criar factory
make make-request name="StoreProductRequest"      # Criar form request
make make-resource name="ProductResource"         # Criar API resource
make make-test name="ProductTest"                 # Criar teste feature
make make-test-unit name="ProductServiceTest"     # Criar teste unitário
make make-service name="ProductService"           # Criar service
```

### Laravel Artisan

```bash
make seed-class name="SaleSeeder"   # Executar seeder específica
make migrate-rollback               # Reverter última migration
make tinker                         # Acessar Laravel Tinker
make artisan cmd="comando"          # Comando Artisan customizado
```

### Composer e NPM

```bash
make composer-install                       # Instalar dependências PHP
make composer cmd="require vendor/pkg"      # Adicionar pacote PHP
make npm-install                            # Instalar dependências Node
make npm-dev                                # Rodar dev server
make npm-build                              # Build de produção
```

### Testes e Manutenção

```bash
make test                   # Executar todos os testes PHPUnit
make test-unit              # Executar apenas testes unitários
make test-feature           # Executar apenas testes de integração
make cache-clear            # Limpar todos os caches do Laravel
make fix-permissions        # Corrigir permissões de arquivos
make clean                  # Parar containers e remover volumes (CUIDADO: deleta dados)
```

## API REST - Endpoints

### GET /api/sellers

Lista todos os vendedores ativos (não deletados) ordenados do mais recente para o mais antigo.

**Resposta de sucesso (200):**

```json
{
  "data": [
    {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "created_at": "2025-10-14T20:30:00+00:00",
      "updated_at": "2025-10-14T20:30:00+00:00"
    }
  ]
}
```

**Exemplo com curl:**

```bash
curl http://localhost:8080/api/sellers
```

### POST /api/sellers

Cadastra um novo vendedor no sistema.

**Body (JSON):**

```json
{
  "name": "Maria Santos",
  "email": "maria@example.com"
}
```

**Resposta de sucesso (201):**

```json
{
  "data": {
    "id": 2,
    "name": "Maria Santos",
    "email": "maria@example.com",
    "created_at": "2025-10-14T21:15:00+00:00",
    "updated_at": "2025-10-14T21:15:00+00:00"
  }
}
```

**Resposta de erro de validação (422):**

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": [
      "Este e-mail já está cadastrado."
    ]
  }
}
```

**Exemplo com curl:**

```bash
curl -X POST http://localhost:8080/api/sellers \
  -H "Content-Type: application/json" \
  -d '{"name":"Maria Santos","email":"maria@example.com"}'
```

### Regras de Validação

- **name**: obrigatório, string, máximo 255 caracteres
- **email**: obrigatório, formato de email válido, único no sistema

## Database Schema

### Tabela: sellers

| Campo      | Tipo         | Descrição                        |
|------------|--------------|----------------------------------|
| id         | BIGINT       | Chave primária (auto incremento) |
| name       | VARCHAR(255) | Nome do vendedor                 |
| email      | VARCHAR(255) | E-mail único do vendedor         |
| created_at | TIMESTAMP    | Data de criação                  |
| updated_at | TIMESTAMP    | Data da última atualização       |
| deleted_at | TIMESTAMP    | Data de exclusão (soft delete)   |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE (email)

### Tabela: sales

| Campo      | Tipo          | Descrição                        |
|------------|---------------|----------------------------------|
| id         | BIGINT        | Chave primária (auto incremento) |
| seller_id  | BIGINT        | Chave estrangeira para sellers   |
| amount     | DECIMAL(10,2) | Valor da venda                   |
| sale_date  | DATE          | Data da venda                    |
| created_at | TIMESTAMP     | Data de criação                  |
| updated_at | TIMESTAMP     | Data da última atualização       |

**Índices e Constraints:**
- PRIMARY KEY (id)
- FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE RESTRICT
- INDEX (seller_id)

## Conexão com o Banco de Dados

### Via Aplicação Laravel

As configurações já estão no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

### Via Cliente Externo (DBeaver, MySQL Workbench, etc.)

Use estas credenciais para conectar de fora do Docker:

- **Host:** `localhost`
- **Porta:** `3307`
- **Database:** `laravel`
- **Username:** `laravel`
- **Password:** `secret`

## Recursos Implementados

Este projeto demonstra boas práticas de desenvolvimento:

### Arquitetura e Design Patterns

- **Service Layer Pattern**: Lógica de negócio separada dos controllers (`SellerService`)
- **API Resources**: Transformação consistente de dados com `SellerResource`
- **Form Request Validation**: Validação centralizada com `StoreSellerRequest`
- **Repository Pattern implícito**: Uso de Eloquent ORM para abstração de dados
- **Dependency Injection**: Injeção de dependências nos controllers

### Backend e API

- **API RESTful**: Endpoints para CRUD de vendedores com status HTTP corretos (200, 201, 422)
- **Validação robusta**: Regras de validação com mensagens customizadas em português
- **Relacionamentos**: Eloquent relationships entre Sellers e Sales (HasMany/BelongsTo)
- **Soft Deletes**: Exclusão lógica de vendedores preservando histórico
- **Type Safety**: PHP 8.3 com typed properties, return types e property promotion
- **Cache Strategy**: Implementação de cache Redis com TTL e invalidação automática

### Qualidade e Testes

- **Cobertura de testes**: 14 testes com 63 assertions (100% nos componentes principais)
- **Testes de Feature**: Integração completa da API incluindo casos de borda
- **Testes Unitários**: Validação isolada da lógica de negócio (service e resources)
- **Factories e Seeders**: Dados de teste e exemplo facilmente gerados

### Database

- **Migrations versionadas**: Controle de versão do schema do banco
- **Foreign Key Constraints**: Integridade referencial com `onDelete('restrict')`
- **Índices otimizados**: Unique index em email, índices em foreign keys
- **Tipos adequados**: Decimal para valores monetários, Date para datas

### Docker e DevOps

- **Multi-container architecture**: 5 serviços orquestrados (Nginx, PHP-FPM, MySQL, Redis, Workspace)
- **Health checks**: MySQL e Redis com verificações de disponibilidade
- **Volumes persistentes**: Dados preservados entre reinicializações
- **Build otimizado**: `.dockerignore` reduzindo tamanho das imagens
- **Separação de ambientes**: Configurações específicas para desenvolvimento

### Developer Experience

- **Makefile rico**: 40+ comandos para operações comuns (setup, testes, migrations, etc.)
- **Documentação completa**: README detalhado com exemplos práticos
- **Workspace container**: Shell dedicado para comandos CLI
- **Comandos de geração**: Helpers para criar models, controllers, tests, etc.
- **Permissões gerenciadas**: Scripts para corrigir ownership de arquivos

### Segurança e Boas Práticas

- **Containers não-root**: Processos executam com usuários limitados
- **Variáveis de ambiente**: Credenciais isoladas em arquivos `.env`
- **Validação de entrada**: Todos os inputs validados antes do processamento
- **SQL Injection protection**: Eloquent ORM com prepared statements
- **CORS configurado**: Pronto para integração com frontends externos

## Troubleshooting

### Erro de Permissões

Se encontrar problemas de permissão:

```bash
make fix-permissions
```

### Porta já em uso

Se a porta 8080 ou 3307 já estiver em uso, edite `compose.dev.yaml`:

```yaml
nginx:
  ports:
    - "8081:80"  # Altere para outra porta

mysql:
  ports:
    - "3308:3306"  # Altere para outra porta
```

### Containers não iniciam

Verifique os logs:

```bash
make logs
```

### Limpar tudo e começar do zero

```bash
make clean
make setup
```

## Licença

Este projeto está sob a licença MIT.
