# Sales Management System

> Sistema completo de gerenciamento de vendas e comissões com arquitetura full-stack moderna.

## Sobre o Projeto

Sistema web desenvolvido para gerenciar vendedores, vendas e cálculo automático de comissões. A aplicação oferece interface intuitiva para cadastro de vendedores e vendas, além de sistema automatizado de envio de relatórios diários por email para vendedores e administradores.

## Funcionalidades

### API REST

-   Cadastrar vendedores informando nome e e-mail
-   Cadastrar vendas informando vendedor, valor e data
-   Listar todos os vendedores
-   Listar todas as vendas
-   Listar vendas por vendedor específico
-   Cálculo automático de comissão (8.5% configurável por vendedor)
-   Reenvio manual de emails de comissão

### Aplicação Web

-   Interface completa para todas as operações da API
-   Sistema de autenticação (login/registro)
-   Envio automático de emails diários para vendedores
-   Envio automático de email consolidado para administrador
-   Modal para reenvio de emails de comissão

### Sistema de Emails

-   **Envio automático diário** às 23:55 (via Laravel Scheduler)
-   Relatório diário individual para cada vendedor
    -   Quantidade de vendas realizadas
    -   Valor total das vendas
    -   Valor total das comissões
-   Relatório consolidado para o administrador
    -   Soma de todas as vendas do dia
    -   Total de comissões
    -   Ranking dos top 5 vendedores

## Arquitetura e Tecnologias

### Backend

-   **Laravel 12** (PHP 8.3)
-   **MySQL 8.0** - Banco de dados relacional
-   **Redis 7** - Sistema de cache
-   **Laravel Sanctum** - Autenticação API
-   **Laravel Queue** - Processamento assíncrono de emails
-   **Mailpit** - Servidor SMTP para desenvolvimento
-   **PHPUnit 11** - Testes automatizados (55 testes, 212 asserções)

### Frontend

-   **Vue.js 3** com Composition API
-   **TypeScript** (modo strict)
-   **Vite 7** - Build tool
-   **Pinia** - Gerenciamento de estado
-   **Vue Router 4** - Roteamento
-   **Axios** - Cliente HTTP

### DevOps & Ferramentas

-   **Docker Compose** - Orquestração de 6 serviços
-   **Nginx** - Web server
-   **Makefile** - Automação de comandos (40+ comandos)
-   **Git** - Controle de versão

## Pré-requisitos

-   Docker e Docker Compose
-   Make (opcional, mas recomendado)
-   Node.js 18+ (para desenvolvimento do frontend fora do Docker)

### Instalação do Make

-   **Windows**: Use WSL (`wsl --install`) ou `choco install make`
-   **macOS**: `brew install make`
-   **Linux**: `sudo apt-get install make`

## Setup Rápido

### 1. Clone o repositório

```bash
git clone https://github.com/EduardoGarbin/sales-management.git
cd sales-management
```

### 2. Configure e inicie o backend

```bash
cd backend/
make setup
```

Este comando irá:

-   Construir as imagens Docker
-   Iniciar todos os containers
-   Instalar dependências PHP e Node.js
-   Criar arquivo `.env`
-   Gerar chave da aplicação
-   Executar migrations e seeders

### 3. Inicie o servidor do frontend

Em um **novo terminal**:

```bash
cd frontend/
npm install
npm run dev
```

### 4. Inicie o processador de emails (Queue Worker)

Em um **terceiro terminal**:

```bash
cd backend/
make queue-work
```

### 5. Inicie o Scheduler para envio automático de relatórios

Em um **quarto terminal**:

```bash
cd backend/
make schedule-work
```

### 6. Acesse a aplicação

-   **Frontend**: [http://localhost:3000](http://localhost:3000)
-   **API**: [http://localhost:8080/api](http://localhost:8080/api)
-   **Mailpit** (Emails): [http://localhost:8025](http://localhost:8025)

## Como Usar

### 1. Fazer Login

Acesse http://localhost:3000 e faça login ou crie uma conta.

**Credenciais de teste** (criadas pelos seeders):

-   Email: `admin@example.com`
-   Senha: `password`

### 2. Cadastrar Vendedores

-   Acesse "Vendedores" no menu
-   Preencha nome e email
-   Clique em "Cadastrar"

### 3. Cadastrar Vendas

-   Acesse "Vendas" no menu
-   Selecione o vendedor
-   Informe valor e data
-   Clique em "Cadastrar"
-   A comissão será calculada automaticamente (8.5%)

### 4. Visualizar Vendas por Vendedor

-   Na lista de vendedores, clique em "Ver Vendas"
-   Será exibida a lista filtrada das vendas daquele vendedor

### 5. Reenviar Email de Comissão

-   Na lista de vendedores, clique em "Reenviar Email"
-   Selecione a data desejada
-   Clique em "Reenviar Email"
-   Verifique o email no Mailpit: http://localhost:8025

### 6. Enviar Relatórios Diários

Para enviar relatórios de todas as vendas do dia:

```bash
cd backend/
make send-daily-reports
```

Ou para uma data específica:

```bash
make send-reports-date date="2025-10-15"
```

Isso enviará:

-   1 email para cada vendedor com suas vendas
-   1 email para o administrador com o consolidado

## Comandos Make Úteis

### Ambiente Docker

```bash
make up          # Iniciar containers
make down        # Parar containers
make restart     # Reiniciar containers
make ps          # Ver status dos containers
make logs        # Ver logs em tempo real
make shell       # Acessar terminal do container
```

### Banco de Dados

```bash
make migrate           # Executar migrations
make migrate-fresh     # Recriar banco de dados
make seed              # Executar seeders
make migrate-seed      # Migrations + seeders
make db-reset          # Reset completo do banco
```

### Testes

```bash
make test              # Executar todos os testes
make test-unit         # Executar testes unitários
make test-feature      # Executar testes de feature
```

### Queue (Emails)

```bash
make queue-work        # Processar emails
make queue-restart     # Reiniciar workers
make queue-failed      # Ver jobs falhados
```

### Relatórios

```bash
make send-daily-reports              # Enviar relatórios manualmente
make send-reports-date date="..."    # Enviar relatórios de data específica
make schedule-work                   # Executar scheduler (envio automático)
make schedule-run                    # Executar tarefas agendadas uma vez
```

Ver todos os comandos:

```bash
make help
```

## Testes Automatizados

O projeto possui **55 testes com 212 asserções** cobrindo:

### Feature Tests

-   Controllers (Sellers, Sales, Auth)
-   Envio de emails
-   Comandos de console

### Unit Tests

-   Services (SellerService, SaleService, AuthService)
-   Resources (transformação de dados)
-   Jobs (processamento de emails)

Executar testes:

```bash
make test
```

## Endpoints da API

Base URL: `http://localhost:8080/api`

### Autenticação

-   `POST /register` - Registrar usuário
-   `POST /login` - Fazer login
-   `POST /logout` - Fazer logout (autenticado)
-   `GET /me` - Obter dados do usuário (autenticado)

### Vendedores

-   `GET /sellers` - Listar vendedores
-   `POST /sellers` - Criar vendedor
-   `POST /sellers/{id}/resend-commission-email` - Reenviar email

### Vendas

-   `GET /sales` - Listar vendas
-   `POST /sales` - Criar venda
-   `GET /sellers/{id}/sales` - Listar vendas de um vendedor

**Todas as rotas (exceto registro/login) requerem autenticação via Bearer Token.**

## Estrutura do Projeto

```
sales-management/
├── backend/                    # API Laravel
│   ├── app/
│   │   ├── Console/           # Comandos Artisan
│   │   ├── Http/
│   │   │   ├── Controllers/   # Controllers da API
│   │   │   ├── Requests/      # Validação de requests
│   │   │   └── Resources/     # Transformação de dados
│   │   ├── Jobs/              # Jobs assíncronos
│   │   ├── Mail/              # Templates de email
│   │   ├── Models/            # Models Eloquent
│   │   └── Services/          # Lógica de negócio
│   ├── database/
│   │   ├── migrations/        # Migrations do banco
│   │   └── seeders/           # Dados de teste
│   ├── resources/views/       # Templates de email
│   ├── routes/                # Definição de rotas
│   ├── tests/                 # Testes automatizados
│   ├── compose.dev.yaml       # Docker Compose
│   └── Makefile               # Automação de comandos
│
└── frontend/                   # SPA Vue.js
    ├── src/
    │   ├── components/        # Componentes Vue
    │   ├── router/            # Configuração de rotas
    │   ├── services/          # Serviços (API)
    │   ├── stores/            # Gerenciamento de estado
    │   ├── types/             # Definições TypeScript
    │   └── views/             # Páginas da aplicação
    └── vite.config.ts         # Configuração Vite
```

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
| seller_id  | BIGINT        | FK para sellers     |
| amount     | DECIMAL(10,2) | Valor da venda      |
| sale_date  | DATE          | Data da venda       |
| created_at | TIMESTAMP     | Data de criação     |
| updated_at | TIMESTAMP     | Data de atualização |

## Troubleshooting

### Containers não iniciam

```bash
make down
make clean
make setup
```

### Emails não chegam

Verifique se o queue worker está rodando:

```bash
make queue-work
```

### Erro de permissão

```bash
make fix-permissions
```

### Resetar tudo

```bash
make clean
make setup
```

## Documentação Adicional

-   [Backend README](./backend/README-2.md) - Detalhes do Laravel
-   [Frontend README](./frontend/README.md) - Detalhes do Vue.js

---
