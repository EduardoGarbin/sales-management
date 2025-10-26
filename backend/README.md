# Backend - Sales Management System

Este é o README para o backend do sistema, construído com Laravel 12.

Para uma visão geral do projeto e instruções de setup rápido, consulte o [README principal na raiz do projeto](../README.md).

## Tecnologias Utilizadas

-   **Laravel 12.33**
-   **PHP 8.3**
-   **MySQL 8.0**
-   **Redis 7**
-   **Nginx**
-   **Docker & Docker Compose**
-   **PHPUnit 11**

## Comandos de Gerenciamento (Makefile)

Todos os comandos `make` devem ser executados a partir desta pasta (`backend/`).

### Ver Todos os Comandos

```bash
make help
```

### Ambiente Docker

```bash
make up                 # Iniciar containers
make down               # Parar containers
make restart            # Reiniciar containers
make ps                 # Listar status dos containers
make logs               # Ver logs em tempo real
make shell              # Acessar o shell do container 'workspace'
make clean              # Parar e remover volumes (CUIDADO: apaga dados do DB)
```

### Banco de Dados

```bash
make migrate            # Executar migrations
make migrate-fresh      # Recriar o banco de dados
make seed               # Executar todos os seeders
make migrate-seed       # Executar migrations e seeders
make db-reset           # Resetar banco (fresh + seed)
```

### Testes

```bash
make test               # Executar todos os testes PHPUnit
make test-unit          # Executar apenas testes unitários
make test-feature       # Executar apenas testes de feature
make cache-clear        # Limpar todos os caches do Laravel
```

### Filas (Queue)

```bash
make queue-work         # Processar jobs da fila
make queue-listen       # Escutar a fila com auto-reload
make queue-restart      # Reiniciar workers da fila
make queue-failed       # Listar jobs que falharam
make queue-retry id="1" # Tentar novamente um job que falhou
make queue-flush        # Limpar todos os jobs falhados
```

### Relatórios

```bash
make send-daily-reports              # Enviar relatórios diários manualmente
make send-reports-date date="..."    # Enviar relatórios de data específica
make schedule-work                   # Executar scheduler (envio automático)
make schedule-run                    # Executar tarefas agendadas uma vez
```

## API REST - Endpoints

A URL base da API é `http://localhost:8080/api`.

### Autenticação

-   `POST /register` - Cadastro de usuário
-   `POST /login` - Login
-   `POST /logout` - Logout (requer autenticação)
-   `GET /me` - Obter dados do usuário logado (requer autenticação)

### Vendedores

-   `GET /sellers` - Listar vendedores (requer autenticação)
-   `POST /sellers` - Criar um novo vendedor (requer autenticação)
-   `POST /sellers/{id}/resend-commission-email` - Reenviar email de comissão (requer autenticação)

### Vendas

-   `GET /sales` - Listar todas as vendas (requer autenticação)
-   `POST /sales` - Criar uma nova venda (requer autenticação)
-   `GET /sellers/{id}/sales` - Listar vendas de um vendedor específico (requer autenticação)

## Schema do Banco de Dados

### Tabela: `sellers`

| Campo           | Tipo         | Descrição                       |
| --------------- | ------------ | ------------------------------- |
| id              | BIGINT       | Chave primária                  |
| name            | VARCHAR(255) | Nome do vendedor                |
| email           | VARCHAR(255) | Email único                     |
| commission_rate | DECIMAL(5,2) | Taxa de comissão (padrão: 8.5%) |
| created_at      | TIMESTAMP    | Data de criação                 |
| updated_at      | TIMESTAMP    | Data de atualização             |
| deleted_at      | TIMESTAMP    | Soft delete                     |

### Tabela: `sales`

| Campo      | Tipo          | Descrição           |
| ---------- | ------------- | ------------------- |
| id         | BIGINT        | Chave primária      |
| seller_id  | BIGINT        | FK para `sellers`   |
| amount     | DECIMAL(10,2) | Valor da venda      |
| sale_date  | DATE          | Data da venda       |
| created_at | TIMESTAMP     | Data de criação     |
| updated_at | TIMESTAMP     | Data de atualização |

## Conexão Externa com o Banco de Dados

Para conectar ao MySQL de fora do Docker (usando ferramentas como MySQL Workbench, DBeaver, etc.):

-   **Host**: `localhost`
-   **Porta**: `3307`
-   **Database**: `laravel`
-   **Username**: `laravel`
-   **Password**: `secret`

## Estrutura de Pastas

```
backend/
├── app/
│   ├── Console/           # Comandos Artisan
│   ├── Http/
│   │   ├── Controllers/   # Controllers da API
│   │   ├── Requests/      # Validação de requests
│   │   └── Resources/     # Transformação de dados
│   ├── Jobs/              # Jobs assíncronos
│   ├── Mail/              # Mailable classes
│   ├── Models/            # Models Eloquent
│   ├── Providers/         # Service Providers
│   ├── Repositories/      # Camada de acesso a dados
│   │   ├── Contracts/     # Interfaces dos Repositories
│   │   ├── BaseRepository.php
│   │   ├── SellerRepository.php
│   │   ├── SaleRepository.php
│   │   └── UserRepository.php
│   └── Services/          # Lógica de negócio
├── database/
│   ├── migrations/        # Migrations do banco
│   └── seeders/           # Dados de teste
├── resources/views/       # Templates de email
├── routes/
│   ├── api.php            # Rotas da API
│   └── console.php        # Comandos e scheduler
├── tests/                 # Testes automatizados
│   ├── Feature/           # Testes de integração
│   └── Unit/              # Testes unitários
├── compose.dev.yaml       # Docker Compose
└── Makefile               # Automação de comandos
```

## Scheduler (Cron)

O sistema possui agendamento automático para envio de relatórios diários:

-   **Horário**: 23:55 (todos os dias)
-   **Timezone**: America/Sao_Paulo
-   **Comando**: `sales:send-daily-reports`

Para ativar o scheduler localmente:

```bash
make schedule-work
```

Ver tarefas agendadas:

```bash
php artisan schedule:list
```

## Testes Automatizados

O projeto possui **95 testes com 368 asserções**.

### Cobertura de Testes

-   **Feature Tests (40 testes)** - Testes de integração com Controllers, Jobs e Commands
-   **Unit Tests (55 testes)**
    -   Repositories (25 testes) - Persistência e queries
    -   Services (18 testes) - Lógica de negócio
    -   Resources (17 testes) - Transformação de dados
    -   Jobs (5 testes) - Processamento assíncrono

Executar todos os testes:

```bash
make test
```

Executar apenas testes unitários:

```bash
make test-unit
```

Executar apenas testes de feature:

```bash
make test-feature
```

## Variáveis de Ambiente

As principais variáveis de ambiente estão no arquivo `.env`:

```env
APP_NAME="Sales Management"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@salesmanagement.com"
MAIL_ADMIN_EMAIL="admin@salesmanagement.com"
```

## Troubleshooting

### Containers não iniciam

```bash
make down
make clean
make setup
```

### Erro de permissão

```bash
make fix-permissions
```

### Cache não está funcionando

Verifique se o Redis está rodando:

```bash
make ps
```

Limpe o cache:

```bash
make cache-clear
```

### Migrations não rodam

Reset completo do banco:

```bash
make db-reset
```
