# Sales Management System

Este é um sistema de gerenciamento de vendas full-stack, composto por um backend em Laravel e um frontend em Vue.js, totalmente containerizado com Docker.

## Arquitetura

-   **Backend**: API RESTful construída com [Laravel](https://laravel.com/) 12 e PHP 8.3.
-   **Frontend**: Single Page Application (SPA) construída com [Vue.js](https://vuejs.org/) 3 e TypeScript.
-   **Banco de Dados**: MySQL 8.0.
-   **Cache**: Redis 7.
-   **Ambiente**: Orquestrado com Docker Compose e simplificado com `make`.

## Como Iniciar o Projeto Completo

Com Docker e Make instalados, siga os passos abaixo.

> **Nota:** Todos os comandos `make` devem ser executados de dentro da pasta `backend/`.

### Passo 1: Configurar e Iniciar o Backend

Este comando irá preparar todo o ambiente Docker, instalar dependências e configurar o banco de dados.

```bash
cd backend/
make setup
```

### Passo 2: Iniciar o Servidor do Frontend

Em um **novo terminal**, navegue até a mesma pasta e inicie o servidor de desenvolvimento do Vue.js.

```bash
cd backend/
make npm-dev
```

### Passo 3: Acessar a Aplicação

Após a conclusão dos passos acima, a aplicação estará disponível em:

-   **Frontend (Aplicação Web)**: [http://localhost:3000](http://localhost:3000)
-   **Backend (API)**: [http://localhost:8080/api](http://localhost:8080/api)
-   **Mailpit (E-mails de teste)**: [http://localhost:8025](http://localhost:8025)

## Documentação Detalhada

Para informações mais detalhadas sobre cada parte do projeto, consulte os READMEs específicos:

-   **[Backend README](./backend/README.md)**: Instruções detalhadas sobre o ambiente Laravel, comandos `make`, documentação da API, schema do banco e mais.
-   **[Frontend README](./frontend/README.md)**: Instruções sobre o ambiente Vue.js, como rodar o servidor de desenvolvimento, estrutura de pastas e mais.

## Como Instalar o `make`

O `make` é opcional, mas altamente recomendado para facilitar a execução de comandos.

-   **Windows**: `choco install make` ou `scoop install make`. A melhor opção é usar o WSL (`wsl --install`).
-   **macOS**: `brew install make`
-   **Linux**: `sudo apt-get install make` ou `sudo dnf install make`