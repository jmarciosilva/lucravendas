# LucraVendas — Backend API

> Plataforma de Ecommerce e Marketplace para pequenos empreendedores brasileiros.
> Produto vertical da holding **LucraOne**.

---

## Visão geral

O LucraVendas é uma API REST construída em Laravel que alimenta lojas online individuais e um marketplace multi-seller. Cada loja é um **tenant isolado**, com catálogo, pedidos e clientes próprios. O painel administrativo (Filament v3) permite que os sócios da LucraOne gerenciem tenants, planos, repasses financeiros e métricas consolidadas.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 11 + PHP 8.3 |
| Banco de dados | MySQL 8 / PostgreSQL 15 |
| Cache & Filas | Redis + Laravel Horizon |
| Autenticação | Laravel Sanctum (JWT-like tokens) |
| Multi-tenancy | stancl/tenancy v3 |
| Permissões | spatie/laravel-permission |
| Busca | Laravel Scout + Meilisearch |
| Storage | S3-compatible (AWS S3 / MinIO) + spatie/laravel-medialibrary |
| Admin | Filament v3 |
| Containers | Docker + Docker Compose |
| Testes | PHPUnit + Pest |
| CI/CD | GitHub Actions |

---

## Requisitos

- PHP >= 8.3
- Composer >= 2.7
- Docker & Docker Compose (recomendado)
- Node.js >= 20 (para assets do Filament)

---

## Instalação local

### 1. Clone o repositório

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
```

### 2. Suba os containers

```bash
docker compose up -d
```

### 3. Instale as dependências PHP

```bash
docker compose exec app composer install
```

### 4. Configure o ambiente

```bash
cp .env.example .env
docker compose exec app php artisan key:generate
```

Edite o `.env` com suas credenciais de banco, Redis, S3 e serviços externos.

### 5. Execute as migrations

```bash
docker compose exec app php artisan migrate --seed
```

### 6. Instale os assets do Filament

```bash
docker compose exec app php artisan filament:assets
```

### 7. Inicie o Horizon (filas)

```bash
docker compose exec app php artisan horizon
```

A API estará disponível em `http://localhost:8000`.
O painel admin estará em `http://localhost:8000/admin`.

---

## Estrutura de diretórios

```
app/
├── Modules/
│   ├── Tenant/          # gestão de lojas e tenants
│   ├── Catalog/         # produtos, categorias, variações, estoque
│   ├── Orders/          # carrinho, checkout, pedidos, status
│   ├── Marketplace/     # multi-seller, comissões, vitrines
│   ├── Payments/        # gateways (PIX, cartão, boleto)
│   ├── Shipping/        # cálculo de frete, integrações
│   ├── Admin/           # painel Filament — gestão da plataforma
│   └── Marketing/       # LucraMarketing nativo (posts, agendamento)
├── Shared/
│   ├── Traits/
│   ├── ValueObjects/
│   └── Helpers/
config/
database/
│   ├── migrations/
│   └── seeders/
routes/
│   ├── api.php          # rotas públicas da API
│   └── admin.php        # rotas do painel admin
tests/
│   ├── Feature/
│   └── Unit/
```

---

## Variáveis de ambiente principais

```dotenv
APP_NAME=LucraVendas
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=lucravendas
DB_USERNAME=lucravendas
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=lucravendas

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=

# Gateway de pagamento (Efi / Gerencianet)
EFI_CLIENT_ID=
EFI_CLIENT_SECRET=
EFI_SANDBOX=true

# Stripe (opcional)
STRIPE_KEY=
STRIPE_SECRET=

FILAMENT_FILESYSTEM_DISK=s3
```

---

## API

A API segue o padrão RESTful com prefixo `/api/v1`.

Autenticação via Bearer token (Sanctum). Todas as rotas protegidas exigem o header:

```
Authorization: Bearer {token}
```

### Principais grupos de rotas

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout

GET    /api/v1/products
GET    /api/v1/products/{slug}
GET    /api/v1/categories

POST   /api/v1/cart
GET    /api/v1/cart
POST   /api/v1/checkout

GET    /api/v1/orders
GET    /api/v1/orders/{id}

GET    /api/v1/marketplace/sellers
GET    /api/v1/marketplace/sellers/{slug}/products
```

A documentação completa da API está disponível via Scribe em `/docs/api`.

---

## Testes

```bash
# Todos os testes
docker compose exec app php artisan test

# Somente testes unitários
docker compose exec app php artisan test --testsuite=Unit

# Com cobertura
docker compose exec app php artisan test --coverage
```

---

## Multi-tenancy

O projeto usa `stancl/tenancy` com isolamento por banco de dados por tenant. Cada tenant é identificado pelo subdomínio ou pelo header `X-Tenant-ID` nas requisições da API.

```bash
# Criar um novo tenant via Artisan
php artisan tenant:create nome-da-loja dominio.lucravendas.com.br

# Rodar migrations em todos os tenants
php artisan tenants:migrate

# Rodar seed em um tenant específico
php artisan tenants:migrate --tenants=uuid-do-tenant
```

---

## Painel Admin

Acesso em `/admin`. Credenciais padrão após seed:

```
Email:  admin@lucraone.com.br
Senha:  password
```

> Altere imediatamente em produção.

Funcionalidades disponíveis no MVP:
- Gestão de tenants (lojas e sellers)
- Planos e contratos
- Pedidos consolidados
- Financeiro e repasses
- Usuários e permissões
- Métricas da plataforma

---

## Deploy

O projeto inclui um workflow de CI/CD via GitHub Actions (`.github/workflows/deploy.yml`) com os seguintes estágios:

1. Lint (Pint)
2. Testes automatizados
3. Build da imagem Docker
4. Push para registry
5. Deploy via SSH + zero-downtime (Laravel Octane)

Variáveis de ambiente de produção devem ser configuradas nos **Secrets** do repositório GitHub.

---

## Contribuindo

1. Crie uma branch a partir de `develop`: `git checkout -b feature/nome-da-feature`
2. Siga o padrão PSR-12 e as convenções do Laravel
3. Escreva testes para novas funcionalidades
4. Abra um Pull Request para `develop`

---

## Licença

Proprietário — LucraOne. Todos os direitos reservados.
