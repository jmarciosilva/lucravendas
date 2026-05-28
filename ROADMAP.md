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

## FASE 1 — Fundação do projeto `[x]`

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
- [x] Configurar ambiente XAMPP para desenvolvimento local sem Docker (PHP 8.2.12 + MySQL)
- [x] Adicionar `"platform": {"php": "8.2.12"}` no `composer.json` para garantir compatibilidade de pacotes

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
- [x] Configurar `TenancyServiceProvider` e bootstrappers
- [x] Implementar identificação por subdomínio E por header `X-Tenant-ID` (para API mobile)
- [x] Criar comando Artisan `tenant:create {name} {slug} {--plan=free}`

### 1.3 Autenticação

- [x] Instalar e configurar Laravel Sanctum
- [x] Criar migration de usuários com campos: `name`, `email`, `password`, `tenant_id`, `phone`, `status`
- [x] Implementar `AuthController` com endpoints: register, login, logout, me
- [x] Configurar Spatie Permissions com roles: `super_admin`, `tenant_admin`, `customer`
- [x] Escrever testes de autenticação — 9 testes passando

### 1.4 Painel Admin — base

- [x] Instalar Filament v3 e publicar assets
- [x] Criar `AdminPanelProvider` com path `/admin`
- [x] Criar usuário admin via seeder (`jmarciosilva@gmail.com` / `12345678`)
- [x] Criar `TenantResource` no Filament (CRUD básico de lojas)
- [x] Criar `UserResource` no Filament (CRUD de usuários admin)
- [x] Configurar acesso restrito ao painel admin (role `super_admin`, guard `web`)

---

## FASE 2 — Catálogo de Produtos `[x]`

> Objetivo: lojista consegue cadastrar produtos; frontend consegue listar e buscar.

### 2.1 Modelo de dados

- [x] Migration `categories` (`id`, `name`, `slug`, `parent_id`, `tenant_id`, `sort_order`, `is_active`)
- [x] Migration `products` (`id`, `name`, `slug`, `description`, `price`, `compare_price`, `sku`, `stock`, `status`, `category_id`, `tenant_id`) com soft delete
- [x] Migration `product_variants` (`id`, `product_id`, `name`, `sku`, `price`, `stock`, `attributes` JSON)
- [x] Migration `product_images` (via `spatie/laravel-medialibrary` — tabela `media`)
- [x] Relacionamentos Eloquent entre Product, Category, Variant, Media

### 2.2 Dependências instaladas

```bash
composer require spatie/laravel-medialibrary
composer require laravel/scout
composer require meilisearch/meilisearch-php http-interop/http-factory-guzzle
composer require filament/spatie-laravel-media-library-plugin:"^3.3"
```

### 2.3 API de catálogo

- [x] `GET  /api/v1/categories` — árvore de categorias do tenant
- [x] `GET  /api/v1/products` — listagem com filtros (categoria, preço, status), paginação
- [x] `GET  /api/v1/products/{slug}` — detalhe do produto com variantes e imagens
- [x] `GET  /api/v1/products/search?q=` — busca via Scout + Meilisearch
- [x] `POST /api/v1/products` — criar produto (autenticado, role `tenant_admin`)
- [x] `PUT  /api/v1/products/{id}` — atualizar produto
- [x] `DELETE /api/v1/products/{id}` — soft delete
- [x] `POST /api/v1/products/{id}/images` — upload de imagens

### 2.4 Admin — gestão de catálogo

- [x] `ProductResource` no Filament com form completo (nome, preço, imagens, variantes)
- [x] `CategoryResource` no Filament com suporte a hierarquia
- [x] Widget de estoque baixo no dashboard admin (`LowStockWidget`)

### 2.5 Qualidade de código — controllers e handlers

- [x] `try/catch` em **todos** os métodos dos controllers (incluindo leitura) com mapeamento correto de HTTP status
- [x] `catch (\Throwable)` como fallback para erros inesperados (retorna 500)
- [x] `DB::transaction()` em todos os Use Case Handlers de escrita:
  - `CreateCategoryHandler` — check de slug + create (evita race condition)
  - `CreateProductHandler` — check de slug + create + dispatch de eventos
  - `UpdateProductHandler` — find + save + dispatch de eventos
  - `DeleteProductHandler` — find + delete
  - `UploadProductImageHandler` — query + media upload
  - `RegisterUserHandler` — create + assignRole + createToken (3 escritas em cascata)
  - `LoginUserHandler` — createToken

### 2.6 Importação de dados via planilha

- [x] Publicar e rodar migrations do sistema de imports do Filament (`imports`, `failed_import_rows`, `exports`)
- [x] `CategoryImporter` — importa categorias com suporte a hierarquia via `parent_slug`
- [x] `ProductImporter` — importa produtos com conversão automática de preços (reais → centavos)
- [x] `UserImporter` — importa usuários com atribuição de role via Spatie Permission
- [x] Botão **"Importar Planilha"** nas páginas: ListCategories, ListProducts, ListUsers
- [x] Seletor de tenant (loja) no formulário de upload
- [x] Relatório de erros por linha — importação parcial (linhas válidas são salvas mesmo com erros em outras)
- [x] Botão **"Baixar modelo CSV"** automático com as colunas corretas

### 2.7 Melhorias no painel admin

- [x] Campo `phone` no `UserResource` com máscara `(99)99999-9999`
- [x] Campo `name` no `UserResource` com formatação automática em Title Case (respeita conectores "da", "de", "do", "dos", "das")
- [x] Correção de conflito de ícones em grupos de navegação do Filament (grupos sem ícone, itens com ícone)

### 2.8 Testes

- [x] Teste: criar produto via API e buscar pelo slug
- [x] Teste: produto de tenant A não aparece para tenant B
- [x] Teste: busca retorna produto indexado (Scout desabilitado nos testes com `SCOUT_DRIVER=null`)

---

## FASE 3 — Carrinho e Checkout `[x]`

> Objetivo: cliente consegue montar um pedido e finalizar a compra.

### 3.1 Modelo de dados

- [x] Migration `coupons` (`id`, `tenant_id`, `code`, `type`, `value`, `min_order_value`, `max_uses`, `uses_count`, `expires_at`, `is_active`)
- [x] Migration `carts` (`id`, `session_id nullable`, `user_id nullable`, `tenant_id`, `coupon_id nullable`)
- [x] Migration `cart_items` (`id`, `cart_id`, `product_id`, `variant_id nullable`, `quantity`, `unit_price`)
- [x] Migration `orders` (`id`, `tenant_id`, `user_id`, `status`, `subtotal`, `discount`, `shipping_cost`, `total`, `payment_method`, `payment_status`, `coupon_id nullable`, `notes`) com soft delete
- [x] Migration `order_items` — snapshot de nome, sku, preço e quantidade no momento da compra
- [x] Migration `order_status_history` — rastreamento de mudanças de status com `created_at`
- [x] Migration `coupon_usages` — registro de cada uso de cupom por pedido

### 3.2 API de carrinho

- [x] `GET  /api/v1/cart` — carrinho atual (por `X-Cart-Session` ou token Sanctum)
- [x] `POST /api/v1/cart/items` — adicionar item (valida estoque e produto ativo)
- [x] `PUT  /api/v1/cart/items/{id}` — atualizar quantidade (qty=0 remove o item)
- [x] `DELETE /api/v1/cart/items/{id}` — remover item
- [x] `POST /api/v1/cart/coupon` — aplicar cupom (valida código, expiração, uso mínimo, limite)
- [x] `DELETE /api/v1/cart/coupon` — remover cupom

### 3.3 API de checkout

- [x] `POST /api/v1/checkout` — criar pedido (valida estoque → cria Order + OrderItems → deduz estoque → aplica cupom → limpa carrinho)
- [x] `GET  /api/v1/orders` — histórico de pedidos do cliente autenticado
- [x] `GET  /api/v1/orders/{id}` — detalhe do pedido

### 3.4 Jobs e eventos

- [x] `OrderCreated` event → dispara `SendOrderConfirmationEmail` (log em dev, email em produção)
- [x] Dedução de estoque síncrona e transacional no `CheckoutHandler` (dentro do `DB::transaction`)
- [x] Job `ExpireAbandonedCarts` — remove carrinhos com `updated_at` > 24h; agendado via Schedule hourly

### 3.5 Carrinho anônimo + mesclagem

- [x] Carrinho anônimo identificado pelo header `X-Cart-Session: {uuid}` (gerado pelo frontend)
- [x] Carrinho autenticado vinculado ao `user_id`
- [x] Mesclagem automática: quando usuário autenticado envia `X-Cart-Session`, o carrinho anônimo é fundido ao seu carrinho e apagado

### 3.6 Cupons de desconto

- [x] Tipos: `percent` (porcentagem) e `fixed` (valor fixo em centavos)
- [x] Validações: ativo, não expirado, `max_uses` não atingido, `min_order_value` respeitado
- [x] Aplicação registrada em `coupon_usages` + incremento de `uses_count` no checkout

### 3.7 Testes — 30 testes passando (suite completa: 83 testes)

- [x] Teste: visitante anônimo adiciona item via `X-Cart-Session`
- [x] Teste: merge de carrinho anônimo ao autenticar
- [x] Teste: atualizar e remover itens
- [x] Teste: cupom percentual válido aplicado
- [x] Teste: cupom expirado retorna 422
- [x] Teste: cupom com valor mínimo não atingido retorna 422
- [x] Teste: produto sem estoque retorna 422 ao adicionar ao carrinho
- [x] Teste: fluxo completo checkout → pedido criado, estoque deduzido, carrinho limpo
- [x] Teste: checkout falha quando produto sem estoque suficiente
- [x] Teste: checkout com cupom aplica desconto corretamente no total
- [x] Teste: histórico de pedidos do usuário autenticado
- [x] Teste: checkout requer autenticação (401 sem token)

---

## FASE 4 — Pagamentos

> Objetivo: integrar PIX e cartão de crédito com pelo menos um gateway.

### 4.1 Estrutura base

- [ ] Criar interface `PaymentGateway` com métodos: `createCharge`, `refund`, `getStatus`
- [ ] Migration `payment_transactions` (`order_id`, `gateway`, `gateway_id`, `method`, `amount`, `status`, `payload` JSON)
- [ ] Configurar `config/payments.php`

### 4.2 Integração EFI / Gerencianet (PIX prioritário)

- [ ] `composer require efi-pay/efi-pay-php`
- [ ] Implementar `EfiGateway` com geração de QR Code PIX
- [ ] `POST /api/v1/payments/pix` — gerar cobrança PIX
- [ ] `POST /api/v1/webhooks/efi` — processar notificação de pagamento

### 4.3 Integração Stripe (cartão de crédito)

- [ ] `composer require stripe/stripe-php`
- [ ] Implementar `StripeGateway` com Payment Intents
- [ ] `POST /api/v1/payments/card` — criar payment intent
- [ ] `POST /api/v1/webhooks/stripe` — confirmação de pagamento

### 4.4 Testes

- [ ] Teste com sandbox de cada gateway
- [ ] Teste: webhook inválido (assinatura errada) retorna 401
- [ ] Teste: pedido atualiza para `paid` após webhook de sucesso

---

## FASE 5 — Marketplace Multi-Seller

> Objetivo: múltiplos sellers num marketplace compartilhado com comissões e repasses.

### 5.1 Modelo de dados

- [ ] Migration `sellers` (`id`, `name`, `slug`, `tenant_id`, `commission_rate`, `status`, `bank_info` JSON)
- [ ] Migration `commissions` (`order_item_id`, `seller_id`, `gross_amount`, `commission_amount`, `net_amount`, `status`)
- [ ] Migration `payouts` (`seller_id`, `amount`, `status`, `paid_at`, `gateway_response` JSON)

### 5.2 API de marketplace

- [ ] `GET  /api/v1/marketplace/sellers` — listagem de sellers ativos
- [ ] `GET  /api/v1/marketplace/sellers/{slug}` — perfil do seller
- [ ] `GET  /api/v1/marketplace/sellers/{slug}/products` — produtos do seller
- [ ] `POST /api/v1/sellers/register` — cadastro de novo seller
- [ ] `GET  /api/v1/seller/dashboard` — métricas do seller autenticado

### 5.3 Cálculo de comissões

- [ ] Service `CommissionCalculator`
- [ ] Job `ProcessPayout` — repasse para sellers (semanal via Schedule)
- [ ] Split de pagamento (Stripe Connect ou EFI Split)

### 5.4 Admin

- [ ] `SellerResource` no Filament (aprovar/suspender sellers)
- [ ] `CommissionResource` e `PayoutResource`
- [ ] Widget: GMV, top sellers, comissão acumulada

---

## FASE 6 — Frete e Logística

- [ ] Migration `shipping_zones` e `shipping_rates`
- [ ] Integração com Correios / Melhor Envio
- [ ] `GET /api/v1/shipping/calculate` — opções de frete
- [ ] Geração de etiqueta pós-pagamento
- [ ] Tracking via webhook do transportador
- [ ] Frete grátis por valor mínimo por tenant

---

## FASE 7 — LucraMarketing Nativo

- [ ] Migration `social_accounts` (OAuth Instagram/Facebook)
- [ ] Migration `scheduled_posts`
- [ ] Integração com Meta Graph API
- [ ] Job `PublishScheduledPost` (a cada hora via Schedule)
- [ ] Geração automática de post ao publicar produto (`ProductCreated` event)
- [ ] `POST /api/v1/marketing/posts` — agendar post manual

---

## FASE 8 — Observabilidade e Performance

- [ ] Sentry para rastreamento de erros
- [ ] Laravel Telescope (staging)
- [ ] Laravel Horizon com métricas de filas no painel admin
- [ ] Rate limiting na API (por IP e por token)
- [ ] Cache em endpoints pesados com Redis
- [ ] Load testing com k6 ou Artillery
- [ ] Backup automático do banco (`spatie/laravel-backup`)

---

## FASE 9 — Go-live e Infraestrutura

- [ ] Configurar servidor (AWS EC2 / Hetzner VPS)
- [ ] Nginx + SSL (Let's Encrypt)
- [ ] Laravel Octane (Swoole ou RoadRunner)
- [ ] Deploy zero-downtime via GitHub Actions
- [ ] Domínios wildcard para tenants (`*.lucravendas.com.br`)
- [ ] Checklist de segurança: CORS, HTTPS-only, secrets no Vault/SSM
- [ ] Alertas de uptime

---

## Decisões técnicas em aberto

| Decisão | Opções | Prazo |
|---|---|---|
| Gateway de pagamento principal | EFI vs Stripe vs Pagar.me | Fase 4 |
| Split de pagamento marketplace | Stripe Connect vs EFI Split vs manual | Fase 5 |
| Servidor de produção | AWS vs Hetzner | Fase 9 |

---

## Referências

- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [stancl/tenancy](https://tenancyforlaravel.com)
- [Filament v3](https://filamentphp.com/docs)
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)
- [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary)
- [Laravel Scout](https://laravel.com/docs/12.x/scout)
- [EFI Pay SDK PHP](https://github.com/efipay/sdk-php-apis-efi)
