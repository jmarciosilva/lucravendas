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

## FASE 4 — Pagamentos via Mercado Pago `[x]`

> Objetivo: integrar PIX, cartão de crédito e demais formas de pagamento via Mercado Pago —
> gateway único que cobre todos os métodos necessários para o mercado brasileiro.

### 4.1 Estrutura base

- [x] `PaymentGatewayInterface` com métodos: `createPixPayment`, `createCardPayment`, `createBoletoPayment`, `getPaymentStatus`, `refund`
- [x] Migration `payment_transactions` (`order_id`, `gateway`, `external_id`, `method`, `amount`, `status`, `qr_code`, `qr_code_base64`, `ticket_url`, `installments`, `payload` JSON)
- [x] `config/payments.php` com credenciais e modo sandbox
- [x] `composer require mercadopago/dx-php`

### 4.2 Implementação do MercadoPagoGateway

- [x] `MercadoPagoGateway` implementando `PaymentGatewayInterface` (`app/Modules/Payments/Infrastructure/Gateways/`)
- [x] **PIX** — gera QR Code + copia-e-cola via `PaymentClient` do SDK
- [x] **Cartão de crédito** — recebe `card_token` (gerado pelo frontend via MP.js) + `installments`
- [x] **Boleto bancário** — gera boleto com `ticket_url`
- [x] `getPaymentStatus()` — consulta status real no MP por `external_id`
- [x] `refund()` — estorna transação aprovada
- [x] Normalização de status MP → status interno (approved, rejected, cancelled, refunded, pending)

### 4.3 API de pagamentos

- [x] `POST /api/v1/payments/pix` — retorna `qr_code` e `qr_code_base64`
- [x] `POST /api/v1/payments/card` — processa cartão; se aprovado imediatamente, confirma o pedido
- [x] `POST /api/v1/payments/boleto` — retorna `ticket_url`
- [x] `GET  /api/v1/payments/{orderId}/status` — consulta última transação do pedido
- [x] `POST /api/v1/webhooks/mercadopago` — processa notificações IPN (sem auth)

### 4.4 Lógica de negócio no webhook

- [x] Validação de assinatura HMAC-SHA256 via header `x-signature` (ignorada em dev sem secret)
- [x] `approved` → `Order::markAsPaid()`, atualiza status + histórico
- [x] `rejected | cancelled` → `Order::markAsPaymentFailed()`, cancela pedido, **restaura estoque** de todos os itens
- [x] `refunded` → `Order::markAsRefunded()`, registra no histórico
- [x] Idempotência: ignora webhooks com status já processado

### 4.5 Admin — gestão de pagamentos

- [x] `PaymentTransactionResource` no Filament com grupo de navegação **Financeiro**
- [x] Tabela com badges coloridos por método (PIX/Cartão/Boleto) e status
- [x] Filtros por status e método de pagamento
- [x] Página de detalhe com payload JSON completo
- [x] Ação **"Estornar Pagamento"** com confirmação — disponível apenas para `status = approved`

### 4.6 Order entity — novos métodos

- [x] `Order::markAsPaid()` — seta `paymentStatus=PAID`, transiciona `status → CONFIRMED`
- [x] `Order::markAsPaymentFailed()` — seta `paymentStatus=FAILED`, transiciona `status → CANCELLED`
- [x] `Order::markAsRefunded()` — seta `paymentStatus=REFUNDED`
- [x] `OrderRepositoryInterface::update()` e `findByIdRaw()` adicionados

### 4.7 Testes — 11 testes passando (suite completa: 94 testes)

- [x] Teste: PIX retorna `qr_code` (gateway mockado)
- [x] Teste: cartão aprovado confirma pedido automaticamente
- [x] Teste: boleto retorna `ticket_url`
- [x] Teste: webhook com assinatura inválida retorna 401
- [x] Teste: webhook `approved` → pedido `confirmed` + `paid`
- [x] Teste: webhook `rejected` → pedido `cancelled` + estoque restaurado
- [x] Teste: endpoints de pagamento requerem autenticação (401)
- [x] Teste unitário: `markAsPaid`, `markAsPaymentFailed`, `markAsRefunded`, normalização de status MP

---

## FASE 5 — Marketplace Multi-Seller `[x]`

> Objetivo: múltiplos sellers num marketplace compartilhado com comissões e repasses.

### 5.1 Modelo de dados

- [x] Migration `sellers` (`id`, `name`, `slug`, `tenant_id`, `user_id`, `commission_rate`, `status`, `bank_info` JSON, `description`)
- [x] Migration `commissions` (`order_item_id`, `seller_id`, `gross_amount`, `commission_amount`, `net_amount`, `status`)
- [x] Migration `payouts` (`seller_id`, `amount`, `status`, `paid_at`, `gateway_response` JSON)
- [x] Coluna `seller_id` adicionada à tabela `products` (nullable FK para sellers)

### 5.2 API de marketplace

- [x] `GET  /api/v1/marketplace/sellers` — listagem de sellers ativos
- [x] `GET  /api/v1/marketplace/sellers/{slug}` — perfil do seller
- [x] `GET  /api/v1/marketplace/sellers/{slug}/products` — produtos do seller
- [x] `POST /api/v1/sellers/register` — cadastro de novo seller (auth)
- [x] `GET  /api/v1/seller/dashboard` — métricas do seller autenticado

### 5.3 Cálculo de comissões

- [x] `CommissionCalculatorService` — calcula e persiste comissão por item no checkout
- [x] Cancelamento automático de comissões via listener `PaymentRejected` (pedido cancelado)
- [x] `ProcessPayoutHandler` — agrupa comissões pendentes por seller e cria payouts
- [x] `ProcessPayoutJob` — executa semanal (toda segunda às 9h via Schedule)
- [!] Split de pagamento via Mercado Pago Marketplace API — desacoplado, aguarda Fase 9

### 5.4 Admin

- [x] `SellerResource` no Filament — grupo **Marketplace**, ações Aprovar/Suspender com confirmação
- [x] `CommissionResource` e `PayoutResource` — visualização com badges por status
- [x] `MarketplaceStatsWidget` — GMV total, comissão a repassar, contagem de sellers ativos

### 5.5 Testes — 13 testes passando (suite completa: 107 testes)

- [x] Teste: usuário autenticado pode se cadastrar como seller
- [x] Teste: seller duplicado (mesmo usuário no mesmo tenant) retorna 422
- [x] Teste: slug duplicado no mesmo tenant retorna 422
- [x] Teste: cadastro requer autenticação
- [x] Teste: lista apenas sellers ativos do tenant
- [x] Teste: perfil de seller ativo pelo slug
- [x] Teste: seller inativo retorna 404 no perfil
- [x] Teste: lista produtos de um seller ativo
- [x] Teste: seller autenticado visualiza dashboard com métricas
- [x] Teste: usuário sem seller recebe 404 no dashboard
- [x] Teste: dashboard requer autenticação
- [x] Teste: checkout cria comissão para produto com seller vinculado
- [x] Teste: checkout não cria comissão para produto sem seller

---

## FASE 6 — Frete e Logística `[x]`

> Objetivo: cliente calcula frete antes de finalizar o pedido; etiqueta gerada automaticamente após pagamento; rastreio via webhook.

### 6.1 Modelo de dados

- [x] Migration `shipping_zones` (`id`, `tenant_id`, `name`, `states` JSON, `is_active`)
- [x] Migration `shipping_rates` (`id`, `zone_id`, `tenant_id`, `name`, `carrier`, `service_code`, `base_price`, `price_per_kg`, `min_days`, `max_days`, `free_shipping_threshold` nullable, `is_active`)
- [x] Coluna `origin_zipcode` adicionada à tabela `tenants` (CEP de origem para cálculo)
- [x] Colunas de dimensões físicas em `products`: `weight_grams`, `length_cm`, `width_cm`, `height_cm`
- [x] Campos de entrega + rastreio em `orders`: `recipient_*`, `shipping_service_code`, `tracking_code`, `tracking_status`, `shipping_label_url`

### 6.2 Gateway Melhor Envio (`MelhorEnvioGateway`)

- [x] `ShippingGatewayInterface` — contratos: `calculateRates`, `generateLabel`, `getTrackingStatus`
- [x] `MelhorEnvioGateway` implementando a interface via API v2 (sandbox + produção)
- [x] `calculateRates()` — POST `/me/shipment/calculate`, consolida pacotes, filtra erros
- [x] `generateLabel()` — fluxo completo: add cart → checkout → generate → print URL
- [x] `getTrackingStatus()` — consulta eventos de rastreio por código
- [x] Gateway mockável nos testes (mesmo padrão do `PaymentGatewayInterface`)

### 6.3 Sistema interno de tarifas (`InternalRateCalculator`)

- [x] Cálculo offline por zona/UF — sem dependência de API externa
- [x] Preço = `base_price + (peso_kg × price_per_kg)`
- [x] Frete grátis quando `subtotal >= free_shipping_threshold` (por tarifa)
- [x] Gateway mode configurável: `internal` | `melhorenvio` | `both` (via `SHIPPING_GATEWAY`)

### 6.4 API de frete

- [x] `GET  /api/v1/shipping/calculate?zipcode=&state=` — retorna opções internas + ME mescladas, ordenadas por preço
- [x] `POST /api/v1/webhooks/shipping` — recebe notificações de rastreio do Melhor Envio (sem auth)

### 6.5 Integração com checkout

- [x] `CheckoutCommand` aceita `shipping_option_id` ("internal_5" ou "me_2"), endereço do destinatário e `shipping_service_code`
- [x] `CheckoutHandler` aplica custo da tarifa interna ao pedido (re-calcula do banco para segurança)
- [x] Endereço de entrega persistido na tabela `orders`

### 6.6 Geração de etiqueta pós-pagamento

- [x] Listener `PaymentApproved` → `GenerateLabelHandler` (gera etiqueta via ME após pagamento confirmado)
- [x] `GenerateLabelHandler` constrói pacotes a partir dos itens do pedido e dimensões dos produtos
- [x] Atualiza `tracking_code` e `shipping_label_url` no pedido

### 6.7 Tracking via webhook

- [x] `ProcessTrackingWebhookHandler` — localiza pedido por `tracking_code`, atualiza `tracking_status`
- [x] Status `delivered` → transiciona pedido para `OrderStatus::DELIVERED`
- [x] Registra evento no histórico de status do pedido

### 6.8 Admin Filament (grupo **Frete**)

- [x] `ShippingZoneResource` — CRUD de zonas com CheckboxList dos 27 estados brasileiros
- [x] `ShippingRateResource` — CRUD de tarifas: transportadora, preço base, adicional/kg, frete grátis
- [x] `config/shipping.php` — configuração centralizada do gateway e dimensões padrão

### 6.9 Testes — 9 testes passando (suite completa: 116 testes)

- [x] Teste: retorna opções de frete para CEP em zona configurada
- [x] Teste: retorna opções do Melhor Envio quando gateway configurado (mockado)
- [x] Teste: frete grátis quando subtotal ≥ threshold
- [x] Teste: lista vazia para CEP fora das zonas configuradas
- [x] Teste: checkout registra endereço de entrega no pedido
- [x] Teste: checkout com opção interna aplica custo correto no total
- [x] Teste: webhook de rastreio atualiza status do pedido
- [x] Teste: webhook com status `delivered` transiciona pedido para entregue
- [x] Teste: webhook com tracking inexistente retorna `ok: false`

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
| Split de pagamento marketplace | Mercado Pago Split (Marketplace) vs manual | Fase 5 |
| Servidor de produção | AWS vs Hetzner | Fase 9 |

## Decisões técnicas resolvidas

| Decisão | Escolha | Motivo |
|---|---|---|
| Gateway de pagamento | **Mercado Pago** | Cobre PIX, cartão, boleto e demais métodos brasileiros em um único SDK. Tem sandbox completo e suporte nativo a marketplace split. |
| Identificação de tenants na API | **Header `X-Tenant-ID`** | Compatível com API mobile e web sem exigir DNS wildcard em desenvolvimento. |
| Isolamento de tenants | **Coluna `tenant_id`** | Simples, sem overhead de múltiplos bancos, adequado para o volume inicial da plataforma. |

---

## Referências

- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [stancl/tenancy](https://tenancyforlaravel.com)
- [Filament v3](https://filamentphp.com/docs)
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)
- [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary)
- [Laravel Scout](https://laravel.com/docs/12.x/scout)
- [Mercado Pago SDK PHP](https://github.com/mercadopago/sdk-php)
- [Mercado Pago Developers](https://www.mercadopago.com.br/developers/pt/docs)
