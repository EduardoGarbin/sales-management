# Sales Management System - Frontend

Frontend em Vue.js 3 + TypeScript para o Sistema de Gerenciamento de Vendas.

## Tecnologias Utilizadas

-   **Vue.js 3** - Framework JavaScript progressivo com Composition API
-   **TypeScript** - Superset tipado do JavaScript (modo strict)
-   **Vue Router 4** - Roteamento oficial do Vue
-   **Pinia** - State management moderno para Vue
-   **Axios** - Cliente HTTP para consumo da API
-   **Vite 7** - Build tool e dev server ultra-rápido
-   **HTML5 & CSS3** - Markup e estilização

## Requisitos

-   Node.js 18+ instalado
-   Backend rodando em `http://localhost:8080`

## Instalação e Execução

### 1. Instalar dependências

```bash
npm install
```

### 2. Rodar servidor de desenvolvimento

```bash
npm run dev
```

O aplicativo estará disponível em: `http://localhost:3000`

## Estrutura do Projeto

```
frontend/
├── src/
│   ├── views/           # Páginas da aplicação
│   │   ├── Login.vue    # Página de login
│   │   ├── Register.vue # Página de cadastro
│   │   ├── Home.vue     # Página inicial (dashboard)
│   │   ├── Sellers.vue  # Gerenciamento de vendedores
│   │   └── Sales.vue    # Gerenciamento de vendas
│   ├── stores/          # Stores Pinia
│   │   └── auth.ts      # Store de autenticação
│   ├── services/        # Serviços de API
│   │   └── api.ts       # Cliente Axios configurado
│   ├── types/           # Tipos TypeScript
│   │   └── index.ts     # Interfaces e tipos
│   ├── router/          # Configuração de rotas
│   │   └── index.ts     # Vue Router com guards
│   ├── App.vue          # Componente raiz
│   ├── main.ts          # Entry point
│   └── style.css        # Estilos globais
├── public/              # Arquivos estáticos
├── tsconfig.json        # Configuração TypeScript
├── vite.config.ts       # Configuração Vite
└── package.json         # Dependências e scripts
```

## Funcionalidades Implementadas

### Autenticação

-   Login de usuários
-   Cadastro de novos usuários
-   Logout
-   Proteção de rotas privadas (guards)
-   Persistência de token no localStorage
-   Interceptors para renovação automática de token

### Vendedores

-   Listagem de todos os vendedores cadastrados
-   Cadastro de novos vendedores (nome e email)
-   Visualização da taxa de comissão
-   Filtrar vendas por vendedor específico
-   Reenvio de email de comissão para data específica

### Vendas

-   Listagem de todas as vendas
-   Cadastro de novas vendas (vendedor, valor, data)
-   Cálculo automático de comissão (8.5% padrão)
-   Filtro de vendas por vendedor
-   Formatação de valores monetários (BRL)
-   Formatação de datas (padrão brasileiro)

## Endpoints da API Consumidos

Base URL: `http://localhost:8080/api`

### Autenticação

-   `POST /register` - Cadastro de usuário
-   `POST /login` - Login
-   `POST /logout` - Logout (requer autenticação)
-   `GET /me` - Dados do usuário autenticado

### Vendedores

-   `GET /sellers` - Listar vendedores
-   `POST /sellers` - Criar vendedor
-   `POST /sellers/{id}/resend-commission-email` - Reenviar email de comissão

### Vendas

-   `GET /sales` - Listar todas as vendas
-   `POST /sales` - Criar venda
-   `GET /sellers/{id}/sales` - Listar vendas de um vendedor específico

**Nota:** Todas as rotas (exceto `/register` e `/login`) requerem autenticação via Bearer Token.

## Configuração da API

O frontend está configurado para se comunicar com o backend em `http://localhost:8080/api`.

Para alterar a URL base da API, edite o arquivo `src/services/api.ts`:

```typescript
const api = axios.create({
    baseURL: "http://localhost:8080/api", // Alterar aqui se necessário
});
```

## Fluxo de Uso

1. **Cadastro/Login**: Acesse `/register` ou `/login` para autenticar
2. **Home**: Após login, visualize o dashboard com opções de navegação
3. **Vendedores**:
    - Cadastre novos vendedores informando nome e email
    - Visualize a lista de vendedores cadastrados
    - Clique em "Ver Vendas" para filtrar vendas daquele vendedor
    - Clique em "Reenviar Email" para reenviar relatório de comissão
4. **Vendas**:
    - Cadastre vendas selecionando vendedor, valor e data
    - Visualize o histórico de vendas com comissão calculada automaticamente
    - Use filtros para ver vendas de vendedor específico

## Recursos Técnicos

### TypeScript

Todo o código utiliza tipagem estática para maior robustez e prevenção de erros:

```typescript
interface Seller {
    id: number;
    name: string;
    email: string;
    commission_rate: string;
    created_at: string;
    updated_at: string;
}
```

### Composition API

Utiliza a API moderna do Vue 3 com `<script setup>`:

```vue
<script setup lang="ts">
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const sellers = ref<Seller[]>([]);
</script>
```

### State Management

Pinia para gerenciamento de estado global da aplicação:

```typescript
export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null as User | null,
        token: localStorage.getItem("token") || "",
    }),
    // ...
});
```

### Interceptors Axios

Configuração automática de headers de autenticação e tratamento de erros:

```typescript
api.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});
```

### Guards de Roteamento

Proteção de rotas privadas com redirecionamento automático:

```typescript
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next("/login");
    } else {
        next();
    }
});
```

## Scripts Disponíveis

```bash
npm run dev        # Inicia servidor de desenvolvimento
npm run build      # Compila para distribuição
npm run preview    # Visualiza build de produção localmente
npm run type-check # Verifica erros de tipagem TypeScript
```
