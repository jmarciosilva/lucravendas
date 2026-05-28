# LucraVendas — Backend API

> Plataforma de Ecommerce e Marketplace para pequenos empreendedores brasileiros.
> Produto vertical da holding **LucraOne**.

---

## Visão geral

O LucraVendas é uma API REST construída em Laravel 12 que alimenta lojas online individuais e um marketplace multi-seller. Cada loja é um **tenant isolado**, com catálogo, pedidos e clientes próprios. O painel administrativo (Filament v3) permite que os gestores da LucraOne gerenciem tenants, planos, importações em massa, usuários e métricas consolidadas da plataforma.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 12 + PHP 8.2+ |
| Banco de dados | MySQL 8 |
| Cache & Sessão | Redis (produção) / arquivo (desenvolvimento local) |
| Filas | Redis + Laravel Horizon (produção) / sync (desenvolvimento local) |
| Autenticação | Laravel Sanctum (Bearer tokens) |
| Multi-tenancy | stancl/tenancy v3 |
| Permissões | spatie/laravel-permission v6 |
| Busca | Laravel Scout + Meilisearch (produção) / null driver (desenvolvimento) |
| Storage | S3-compatible / local |
| Media | spatie/laravel-medialibrary v11 |
| Pagamentos | Mercado Pago SDK (PIX, cartão de crédito, boleto) |
| Frete | Melhor Envio API v2 (cálculo, etiqueta, rastreio) |
| Admin | Filament v3.3 |
| Testes | PHPUnit + Pest |

---

## Requisitos

- PHP >= 8.2
- Composer >= 2.7
- MySQL 8 (XAMPP ou Docker)
- Node.js >= 20

---

## Instalação rápida — XAMPP

### 1. Clone o repositório

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
```

### 2. Instale as dependências e configure o ambiente

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Configure o `.env` para XAMPP

```dotenv
DB_HOST=127.0.0.1
DB_DATABASE=lucravendas
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SCOUT_DRIVER=null
MAIL_MAILER=log
```

### 4. Crie o banco e rode as migrations

```bash
# Via MySQL do XAMPP
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lucravendas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
```

### 5. Inicie o servidor

```bash
npm install && npm run build
php artisan serve
```

A API estará em `http://localhost:8000`.

---

## Instalação com Docker

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan filament:assets
```

---

## Estrutura de diretórios

```
app/
├── Modules/
│   ├── Admin/           # painel Filament — gestão da plataforma
│   ├── Catalog/         # produtos, categorias, variações, estoque, importação
│   ├── Marketing/       # LucraMarketing nativo (posts, agendamento)
│   ├── Marketplace/     # multi-seller, comissões, vitrines
│   ├── Orders/          # carrinho, checkout, pedidos, status
│   ├── Payments/        # Mercado Pago: PIX, cartão, boleto, webhook, estorno
│   ├── Shipping/        # cálculo de frete, integrações
│   └── Tenant/          # gestão de lojas, usuários, autenticação
├── Shared/
│   ├── Traits/
│   ├── ValueObjects/
│   └── Helpers/
database/
│   ├── migrations/
│   └── seeders/
routes/
│   └── api.php          # rotas da API (/api/v1/)
tests/
│   ├── Feature/
│   └── Unit/
```

Cada módulo segue a estrutura DDD:

```
Modules/{Modulo}/
├── Domain/           # entidades, value objects, interfaces de repositório
├── Application/      # use cases, commands, DTOs, importers
├── Infrastructure/   # models Eloquent, repositórios, integrações externas
└── Presentation/     # controllers, form requests, resources API, resources Filament
```

---

## Variáveis de ambiente principais

```dotenv
APP_NAME=LucraVendas
APP_ENV=local
APP_URL=http://localhost:8000

# Banco de dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1       # Docker: mysql
DB_PORT=3306
DB_DATABASE=lucravendas
DB_USERNAME=root         # Docker: lucravendas
DB_PASSWORD=             # Docker: secret

# Cache / Sessão / Filas
SESSION_DRIVER=file      # Produção: redis
CACHE_STORE=file         # Produção: redis
QUEUE_CONNECTION=sync    # Produção: redis

# Busca
SCOUT_DRIVER=null        # Produção: meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=masterKey

# Storage
FILESYSTEM_DISK=local    # Produção: s3
FILAMENT_FILESYSTEM_DISK=local

# E-mail
MAIL_MAILER=log          # Produção: smtp

# Mercado Pago (PIX, cartão, boleto)
MERCADO_PAGO_ACCESS_TOKEN=
MERCADO_PAGO_PUBLIC_KEY=
MERCADO_PAGO_WEBHOOK_SECRET=
MERCADO_PAGO_SANDBOX=true

# Melhor Envio (frete, etiqueta, rastreio)
MELHORENVIO_TOKEN=
MELHORENVIO_SANDBOX=true
MELHORENVIO_FROM_NAME=LucraVendas
MELHORENVIO_FROM_EMAIL=
MELHORENVIO_FROM_PHONE=
MELHORENVIO_FROM_DOCUMENT=
MELHORENVIO_FROM_ADDRESS=
MELHORENVIO_FROM_NUMBER=
MELHORENVIO_FROM_CITY=
MELHORENVIO_FROM_STATE=
MELHORENVIO_FROM_ZIPCODE=
SHIPPING_GATEWAY=both           # internal | melhorenvio | both
```

---

## API

A API segue o padrão RESTful com prefixo `/api/v1`. Autenticação via Bearer token (Sanctum):

```
Authorization: Bearer {token}
```

Para requisições de tenant específico (ex.: app mobile):

```
X-Tenant-ID: {uuid-do-tenant}
```

### Rotas disponíveis

```
# Autenticação
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
GET    /api/v1/auth/me

# Catálogo (público)
GET    /api/v1/categories
GET    /api/v1/products
GET    /api/v1/products/{slug}
GET    /api/v1/products/search?q=

# Catálogo (requer tenant_admin)
POST   /api/v1/categories
POST   /api/v1/products
PUT    /api/v1/products/{id}
DELETE /api/v1/products/{id}
POST   /api/v1/products/{id}/images

# Carrinho (público — anônimo ou autenticado)
GET    /api/v1/cart
POST   /api/v1/cart/items
PUT    /api/v1/cart/items/{id}
DELETE /api/v1/cart/items/{id}
POST   /api/v1/cart/coupon
DELETE /api/v1/cart/coupon

# Checkout e pedidos (requer autenticação)
POST   /api/v1/checkout
GET    /api/v1/orders
GET    /api/v1/orders/{id}

# Pagamentos via Mercado Pago (requer autenticação)
POST   /api/v1/payments/pix              # gera QR Code PIX
POST   /api/v1/payments/card             # processa cartão (card_token via MP.js)
POST   /api/v1/payments/boleto           # gera boleto com ticket_url
GET    /api/v1/payments/{orderId}/status # consulta última transação

# Webhook Mercado Pago (sem autenticação — chamado pelos servidores do MP)
POST   /api/v1/webhooks/mercadopago

# Marketplace (público)
GET    /api/v1/marketplace/sellers
GET    /api/v1/marketplace/sellers/{slug}
GET    /api/v1/marketplace/sellers/{slug}/products

# Marketplace (requer autenticação)
POST   /api/v1/sellers/register
GET    /api/v1/seller/dashboard

# Frete (público — carrinho anônimo ou autenticado)
GET    /api/v1/shipping/calculate?zipcode=&state=

# Webhook Melhor Envio (sem autenticação — rastreio)
POST   /api/v1/webhooks/shipping
```

---

## Painel Admin

Acesso em `http://localhost:8000/admin`.

```
Email:  jmarciosilva@gmail.com
Senha:  12345678
```

> Altere imediatamente em produção.

### Funcionalidades disponíveis

| Módulo | Funcionalidades |
|---|---|
| **Tenants** | CRUD de lojas, planos (free/starter/growth/enterprise), status |
| **Produtos** | CRUD completo, upload de imagens, gestão de variantes, widget de estoque baixo |
| **Categorias** | CRUD com hierarquia (categorias pai/filho), ordenação |
| **Usuários** | CRUD com roles, máscara de telefone, formatação automática de nome |
| **Importação** | Upload de planilha CSV/XLSX para categorias, produtos e usuários em massa |
| **Financeiro** | Listagem de transações (PIX/Cartão/Boleto), badges de status, ação de estorno com confirmação |
| **Marketplace** | Aprovação/suspensão de sellers, comissões por item, repasses (payouts), widget GMV |
| **Frete** | CRUD de zonas por UF (27 estados), tarifas com frete grátis configurável por valor mínimo |

### Importação via planilha

O painel suporta importação em massa com:
- Seletor de loja (tenant) antes do upload
- Mapeamento visual de colunas
- Relatório de erros por linha — linhas válidas são salvas mesmo quando outras falham
- Botão "Baixar modelo CSV" com as colunas corretas

---

## Testes

```bash
# Todos os testes (XAMPP)
php artisan test

# Todos os testes (Docker)
docker compose exec app php artisan test

# Por suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Com cobertura
php artisan test --coverage
```

116 testes passando. Os testes usam SQLite em memória — independentes do banco principal.
Os gateways Mercado Pago e Melhor Envio são **mockados** nos testes — nenhuma chamada real é feita às APIs externas.

---

## Multi-tenancy

O projeto usa `stancl/tenancy` com isolamento por coluna (`tenant_id`). Cada tenant é identificado pelo subdomínio ou pelo header `X-Tenant-ID` nas requisições da API.

```bash
# Rodar migrations em todos os tenants
php artisan tenants:migrate

# Rodar migrations em tenant específico
php artisan tenants:migrate --tenants=uuid-do-tenant
```

---

## Arquitetura

O projeto segue **Domain-Driven Design (DDD)**:

- **Domain** — entidades, value objects e interfaces de repositório. Zero dependência de framework.
- **Application** — use cases (handlers + commands), importers. Orquestra o domínio. Toda operação de escrita usa `DB::transaction()`.
- **Infrastructure** — models Eloquent, repositórios concretos, integrações externas.
- **Presentation** — controllers HTTP, form requests, API resources, resources Filament. Toda lógica delega para Application. Todos os métodos têm `try/catch` com mapeamento para HTTP status codes.

---

## Progresso

| Fase | Descrição | Status |
|---|---|---|
| 1 | Fundação: setup, multi-tenancy, autenticação, painel admin base | Concluída |
| 2 | Catálogo: produtos, categorias, API, importação via planilha | Concluída |
| 3 | Carrinho e checkout | Concluída |
| 4 | Pagamentos via Mercado Pago (PIX, cartão, boleto) | Concluída |
| 5 | Marketplace multi-seller (sellers, comissões, repasses) | Concluída |
| 6 | Frete e logística (Melhor Envio, tarifas internas, rastreio) | Concluída |
| 7 | LucraMarketing nativo | Pendente |
| 8 | Observabilidade e performance | Pendente |
| 9 | Go-live e infraestrutura | Pendente |

---

## Contribuindo

1. Crie uma branch a partir de `develop`: `git checkout -b feature/nome-da-feature`
2. Siga o padrão PSR-12: `./vendor/bin/pint`
3. Escreva testes para novas funcionalidades
4. Abra um Pull Request para `develop`

---

## Licença

Proprietário — LucraOne. Todos os direitos reservados.
