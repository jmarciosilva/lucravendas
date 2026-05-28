# ROADMAP — LucraVendas Backend

> Documento de referência para o desenvolvimento incremental da plataforma.
> Atualizado conforme o progresso de cada fase.

---

## Legenda de status

| Símbolo | Significado |
|---|---|
| `[ ]` | Pendente |
| `[~]` | Em andamento |
| `[x]` | Concluído |
| `[!]` | Bloqueado / requer decisão |

---

## FASE 1 — Fundação do projeto

> Objetivo: projeto rodando localmente com autenticação, multi-tenancy e estrutura de módulos estabelecida.

### 1.1 Setup inicial

- [x] Configurar `docker-compose.yml` com serviços: `app`, `nginx`, `mysql`, `redis`, `meilisearch`, `mailpit`
- [x] Criar `.env.example` com todas as variáveis documentadas
- [x] Configurar `Dockerfile` com PHP 8.2 + extensões necessárias
- [x] Instalar e configurar Laravel Pint (linter PSR-12) — `pint.json` configurado
- [x] Configurar PHPUnit + Pest para testes
- [x] Criar estrutura de diretórios `app/Modules/` e `app/Shared/`
- [x] Configurar `composer.json` com autoload dos módulos (`App\Modules\*`, `App\Shared\*`)
- [x] Criar `routes/api.php` versionado (`/api/v1/`)
- [x] Configurar GitHub Actions: lint + testes no push (`.github/workflows/ci.yml`)

```bash
# Pacotes instalados nesta fase
composer require stancl/tenancy
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require filament/filament:"^3.3"
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
```

### 1.2 Multi-tenancy

- [x] Publicar e configurar `config/tenancy.php`
- [x] Definir modelo `Tenant` com campos: `id`, `name`, `slug`, `plan`, `status`, `data` (JSON)
- [x] Criar migration da tabela central de tenants
- [x] Configurar `TenancyServiceProvider` e bootstrappers (banco, cache, storage)
- [x] Implementar identificação por subdomínio E por header `X-Tenant-ID` (para API mobile)
- [x] Criar comando Artisan `tenant:create {name} {slug} {--plan=free}`
- [x] Testar isolamento de dados entre dois tenants distintos (12 testes unitários passando)

### 1.3 Autenticação

- [x] Instalar e configurar Laravel Sanctum
- [x] Criar migration de usuários com campos: `name`, `email`, `password`, `tenant_id`, `phone`, `status`
- [x] Implementar `AuthController` com endpoints:
  - `POST /api/v1/auth/register`
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET  /api/v1/auth/me`
- [x] Configurar Spatie Permissions com roles: `super_admin`, `tenant_admin`, `customer`
- [x] Escrever testes de autenticação (register, login, token inválido, logout) — 9 testes passando

### 1.4 Painel Admin — base

- [x] Instalar Filament v3 e publicar assets
- [x] Criar `AdminPanelProvider` com path `/admin`
- [x] Criar usuário admin via seeder (`jmarciosilva@gmail.com`)
- [x] Criar `TenantResource` no Filament (CRUD básico de lojas)
- [x] Criar `UserResource` no Filament (CRUD de usuários admin)
- [x] Configurar acesso restrito ao painel admin (role `super_admin`, guard `web`)

---

## FASE 2 — Catálogo de Produtos

> Objetivo: lojista consegue cadastrar produtos; frontend consegue listar e buscar.

### 2.1 Modelo de dados

- [ ] Migration `categories` (`id`, `name`, `slug`, `parent_id`, `tenant_id`)
- [ ] Migration `products` (`id`, `name`, `slug`, `description`, `price`, `compare_price`, `sku`, `stock`, `status`, `category_id`, `tenant_id`)
- [ ] Migration `product_variants` (`id`, `product_id`, `name`, `sku`, `price`, `stock`, `attributes` JSON)
- [ ] Migration `product_images` (via `spatie/laravel-medialibrary`)
- [ ] Relacionamentos Eloquent entre Product, Category, Variant, Media

### 2.2 Instalar dependências

```bash
composer require spatie/laravel-medialibrary
composer require laravel/scout
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
```

### 2.3 API de catálogo

- [ ] `GET  /api/v1/categories` — árvore de categorias do tenant
- [ ] `GET  /api/v1/products` — listagem com filtros (categoria, preço, status), paginação
- [ ] `GET  /api/v1/products/{slug}` — detalhe do produto com variantes e imagens
- [ ] `GET  /api/v1/products/search?q=` — busca via Scout + Meilisearch
- [ ] `POST /api/v1/products` — criar produto (autenticado, role `tenant_admin`)
- [ ] `PUT  /api/v1/products/{id}` — atualizar produto
- [ ] `DELETE /api/v1/products/{id}` — soft delete
- [ ] `POST /api/v1/products/{id}/images` — upload de imagens

### 2.4 Admin — gestão de catálogo

- [ ] `ProductResource` no Filament com form completo (nome, preço, imagens, variantes)
- [ ] `CategoryResource` no Filament com suporte a hierarquia
- [ ] Widget de estoque baixo no dashboard admin

### 2.5 Testes

- [ ] Teste: criar produto via API e buscar pelo slug
- [ ] Teste: busca retorna produto indexado no Meilisearch
- [ ] Teste: produto de tenant A não aparece para tenant B

---

## FASE 3 — Carrinho e Checkout

> Objetivo: cliente consegue montar um pedido e finalizar a compra.

### 3.1 Modelo de dados

- [ ] Migration `carts` (`id`, `session_id`, `user_id nullable`, `tenant_id`)
- [ ] Migration `cart_items` (`id`, `cart_id`, `product_id`, `variant_id nullable`, `quantity`, `unit_price`)
- [ ] Migration `orders` (`id`, `tenant_id`, `user_id`, `status`, `subtotal`, `discount`, `shipping_cost`, `total`, `payment_method`, `payment_status`, `notes`)
- [ ] Migration `order_items` (espelho do cart_items no momento da compra)
- [ ] Migration `order_status_history` (rastreamento de mudanças de status)
- [ ] Migration `coupons` e `coupon_usages`

### 3.2 API de carrinho

- [ ] `GET  /api/v1/cart` — carrinho atual (por session ou token)
- [ ] `POST /api/v1/cart/items` — adicionar item
- [ ] `PUT  /api/v1/cart/items/{id}` — atualizar quantidade
- [ ] `DELETE /api/v1/cart/items/{id}` — remover item
- [ ] `POST /api/v1/cart/coupon` — aplicar cupom
- [ ] `DELETE /api/v1/cart/coupon` — remover cupom

### 3.3 API de checkout

- [ ] `POST /api/v1/checkout` — criar pedido a partir do carrinho
  - Validar estoque
  - Calcular totais (subtotal + frete + desconto)
  - Reservar estoque
  - Retornar `order_id` e dados de pagamento
- [ ] `GET  /api/v1/orders` — histórico de pedidos do cliente
- [ ] `GET  /api/v1/orders/{id}` — detalhe do pedido

### 3.4 Jobs e eventos

- [ ] `OrderCreated` event → dispara `SendOrderConfirmationEmail` job
- [ ] `StockReserved` event → reduz estoque
- [ ] Job de expiração de carrinhos abandonados (>24h)

### 3.5 Testes

- [ ] Teste: fluxo completo add-to-cart → checkout → pedido criado
- [ ] Teste: checkout falha quando produto sem estoque
- [ ] Teste: cupom inválido retorna erro 422

---

## FASE 4 — Pagamentos

> Objetivo: integrar PIX e cartão de crédito com pelo menos um gateway.

### 4.1 Estrutura base

- [ ] Criar interface `PaymentGateway` com métodos: `createCharge`, `refund`, `getStatus`
- [ ] Migration `payment_transactions` (`order_id`, `gateway`, `gateway_id`, `method`, `amount`, `status`, `payload` JSON)
- [ ] Configurar `config/payments.php` com lista de gateways e credenciais

### 4.2 Integração EFI / Gerencianet (PIX prioritário)

- [ ] Instalar SDK: `composer require efi-pay/efi-pay-php`
- [ ] Implementar `EfiGateway` com geração de QR Code PIX
- [ ] Endpoint `POST /api/v1/payments/pix` — gerar cobrança PIX
- [ ] Webhook `POST /api/v1/webhooks/efi` — receber notificação de pagamento
- [ ] Processar webhook: atualizar status do pedido, liberar acesso ao produto

### 4.3 Integração Stripe (cartão de crédito)

- [ ] Instalar SDK: `composer require stripe/stripe-php`
- [ ] Implementar `StripeGateway` com Payment Intents
- [ ] Endpoint `POST /api/v1/payments/card` — criar payment intent
- [ ] Webhook `POST /api/v1/webhooks/stripe` — confirmação de pagamento
- [ ] Suporte a parcelamento (metadata)

### 4.4 Testes

- [ ] Teste com ambiente sandbox de cada gateway
- [ ] Teste: webhook inválido (assinatura errada) retorna 401
- [ ] Teste: pedido atualiza para `paid` após webhook de sucesso

---

## FASE 5 — Marketplace Multi-Seller

> Objetivo: múltiplos sellers num marketplace compartilhado com comissões e repasses.

### 5.1 Modelo de dados

- [ ] Migration `sellers` (`id`, `name`, `slug`, `tenant_id`, `commission_rate`, `status`, `bank_info` JSON)
- [ ] Relacionar `products` com `sellers` (um produto pertence a um seller)
- [ ] Migration `commissions` (`order_item_id`, `seller_id`, `gross_amount`, `commission_amount`, `net_amount`, `status`)
- [ ] Migration `payouts` (`seller_id`, `amount`, `status`, `paid_at`, `gateway_response` JSON)

### 5.2 API de marketplace

- [ ] `GET  /api/v1/marketplace/sellers` — listagem de sellers ativos
- [ ] `GET  /api/v1/marketplace/sellers/{slug}` — perfil do seller
- [ ] `GET  /api/v1/marketplace/sellers/{slug}/products` — produtos do seller
- [ ] `POST /api/v1/sellers/register` — cadastro de novo seller
- [ ] `GET  /api/v1/seller/dashboard` — métricas do seller autenticado
- [ ] `GET  /api/v1/seller/orders` — pedidos do seller
- [ ] `GET  /api/v1/seller/commissions` — extrato de comissões

### 5.3 Cálculo de comissões

- [ ] Service `CommissionCalculator` — calcula comissão no momento do pedido
- [ ] Job `ProcessPayout` — gerar repasse para sellers (execução semanal via Schedule)
- [ ] Integração com gateway para split de pagamento (Stripe Connect ou EFI Split)

### 5.4 Admin — gestão do marketplace

- [ ] `SellerResource` no Filament (aprovar/suspender sellers)
- [ ] `CommissionResource` — extrato e ajustes manuais
- [ ] `PayoutResource` — controle de repasses (aprovar, marcar como pago)
- [ ] Widget: GMV do marketplace, top sellers, comissão acumulada

### 5.5 Testes

- [ ] Teste: produto de seller A não pode ser editado por seller B
- [ ] Teste: comissão calculada corretamente ao criar pedido
- [ ] Teste: payout gerado somente para comissões com status `settled`

---

## FASE 6 — Frete e Logística

> Objetivo: calcular e exibir opções de frete no checkout.

- [ ] Migration `shipping_zones` e `shipping_rates` (regras por CEP/estado/peso)
- [ ] Integração com Correios (API Melhor Envio ou direta)
- [ ] `GET /api/v1/shipping/calculate` — calcular opções de frete (CEP destino + itens)
- [ ] Gerar etiqueta de envio pós-pagamento
- [ ] Tracking de pedido via webhook do transportador
- [ ] Configuração de frete grátis por valor mínimo de pedido (por tenant)

---

## FASE 7 — LucraMarketing Nativo

> Objetivo: todo cliente LucraVendas tem marketing automático sem configuração extra.

- [ ] Migration `social_accounts` (OAuth com Instagram, Facebook)
- [ ] Migration `scheduled_posts` (`tenant_id`, `content`, `image_url`, `scheduled_at`, `status`, `platform`)
- [ ] Integração com Meta Graph API (publicação em Instagram/Facebook)
- [ ] Job `PublishScheduledPost` (executado a cada hora via Schedule)
- [ ] Geração automática de post ao publicar produto novo (via `ProductCreated` event)
- [ ] Templates de post por categoria de negócio
- [ ] `GET  /api/v1/marketing/posts` — histórico de posts
- [ ] `POST /api/v1/marketing/posts` — agendar post manual
- [ ] Admin: relatório de alcance por tenant

---

## FASE 8 — Observabilidade e Performance

> Objetivo: sistema pronto para produção com monitoramento real.

- [ ] Instalar e configurar Sentry para rastreamento de erros
- [ ] Configurar Laravel Telescope (ambiente de staging)
- [ ] Configurar Laravel Horizon com métricas de filas no painel admin
- [ ] Implementar rate limiting na API (throttle por IP e por token)
- [ ] Adicionar cache em endpoints pesados (catálogo, categorias) com Redis
- [ ] Configurar índices de banco de dados (tenant_id, slug, status, created_at)
- [ ] Load testing básico com k6 ou Artillery
- [ ] Configurar backup automático do banco (S3 via `spatie/laravel-backup`)

---

## FASE 9 — Go-live e Infraestrutura

> Objetivo: deploy seguro em produção.

- [ ] Configurar servidor (AWS EC2 / Hetzner VPS)
- [ ] Configurar Nginx + SSL (Let's Encrypt via Certbot)
- [ ] Configurar Laravel Octane (Swoole ou RoadRunner) para performance
- [ ] Workflow de deploy zero-downtime no GitHub Actions
- [ ] Configurar domínios wildcard para tenants (`*.lucravendas.com.br`)
- [ ] Checklist de segurança: CORS, HTTPS-only, secrets no Vault/SSM, headers HTTP
- [ ] Criar runbook de operações (restart, rollback, migrations em prod)
- [ ] Configurar alertas de uptime (Better Uptime ou HetrixTools)

---

## Decisões técnicas em aberto

| Decisão | Opções | Prazo |
|---|---|---|
| Gateway de pagamento principal | EFI vs Stripe vs Pagar.me | Fase 4 |
| Split de pagamento marketplace | Stripe Connect vs EFI Split vs manual | Fase 5 |
| Identificação de tenants na API mobile | Subdomínio vs Header `X-Tenant-ID` | Fase 1 |
| Isolamento de tenants | Banco separado vs schema vs coluna | Fase 1 |
| Servidor de produção | AWS vs Hetzner | Fase 9 |

---

## Referências

- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [stancl/tenancy](https://tenancyforlaravel.com)
- [Filament v3](https://filamentphp.com/docs)
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)
- [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary)
- [Laravel Scout](https://laravel.com/docs/11.x/scout)
- [EFI Pay SDK PHP](https://github.com/efipay/sdk-php-apis-efi)
