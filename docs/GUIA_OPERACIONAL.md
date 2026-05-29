# Guia Operacional — LucraVendas

> Este documento descreve como operar a plataforma após a instalação:
> desde a criação de uma loja até o primeiro pedido pago.

---

## Visão geral da plataforma

O LucraVendas é uma plataforma multi-tenant de e-commerce. Cada **loja (tenant)** opera de forma isolada — clientes, produtos, pedidos e configurações de uma loja nunca se misturam com outra.

A plataforma tem três camadas de acesso:

| Camada | URL | Quem usa |
|--------|-----|----------|
| Painel super admin | `/admin` | Equipe LucraVendas — gerencia todas as lojas |
| Painel do lojista | `/painel` | Dono da loja — gerencia sua própria loja |
| Vitrine do cliente | `/loja/{slug}` | Comprador final — navega e compra |

---

## Papéis de usuário (roles)

| Role | Acesso |
|------|--------|
| `super_admin` | Painel `/admin` — visão total da plataforma |
| `tenant_admin` | Painel `/painel` — apenas sua própria loja |
| `customer` | Vitrine `/loja/{slug}` — compras |

---

## Passo a passo após criar uma loja

### Passo 1 — Criar o usuário lojista

Após criar a loja no painel `/admin`, é necessário criar o usuário que vai gerenciá-la.

**No painel `/admin` → Usuários:**

1. Clique em **Novo Usuário**
2. Preencha nome, e-mail e senha
3. No campo **Role**, selecione `tenant_admin`
4. No campo **Tenant**, selecione a loja recém-criada
5. Salve

O lojista já pode acessar `/painel` com essas credenciais.

---

### Passo 2 — Configurar o perfil e o tema da loja

O perfil define quais módulos ficam ativos por padrão. O tema define o visual da vitrine.

**No painel `/admin` → Lojas → Editar loja:**

| Campo | O que define |
|-------|-------------|
| **Perfil do negócio** | Conjunto de funcionalidades habilitadas por padrão |
| **Tema visual** | Layout e identidade visual da vitrine |
| **Feature Flags** | Sobrescreve módulo a módulo, independente do perfil |

#### Perfis disponíveis

**Marketplaces (múltiplos sellers):**
- `Marketplace Esotérico` — agenda, blog, feed social, avaliações, multi-seller
- `Marketplace de Artesanato` — idem acima
- `Marketplace de Cursos` — agenda, blog, avaliações, multi-seller (sem feed social)
- `Marketplace de Produtos Diversos` — feed social, avaliações, multi-seller (sem agenda/blog)

**Lojas individuais:**
- `Loja de Artesanato` — agenda, blog, avaliações
- `Loja de Roupas` — blog, avaliações
- `Loja de Armarinhos` — blog, avaliações
- `Loja de Eletrônicos` — blog, avaliações
- `Genérico` — apenas avaliações habilitadas por padrão

#### Feature flags individuais

Cada feature pode ser ativada/desativada por tenant, sobrescrevendo o padrão do perfil:

| Feature | O que habilita |
|---------|---------------|
| `agenda` | Módulo de eventos e cursos na vitrine |
| `blog` | Blog editorial |
| `social_posts` | Feed de posts de clientes |
| `reviews` | Avaliações de produtos e sellers |
| `marketplace` | Multi-seller — sellers vendem na sua vitrine |
| `seller_events_on_marketplace` | Eventos dos sellers aparecem na vitrine do marketplace |

---

### Passo 3 — Configurar o CEP de origem (frete)

O CEP de origem é obrigatório para calcular frete corretamente.

**No painel `/painel` → Configurações da Loja:**

1. Informe o **CEP de origem** (armazém ou endereço de expedição)
2. Salve

---

### Passo 4 — Cadastrar categorias

Organize os produtos em categorias antes de cadastrá-los.

**No painel `/painel` → Catálogo → Categorias:**

1. Clique em **Nova Categoria**
2. Informe nome e slug (gerado automaticamente)
3. Selecione uma categoria pai se for subcategoria
4. Salve

---

### Passo 5 — Cadastrar produtos

**No painel `/painel` → Catálogo → Produtos:**

1. Clique em **Novo Produto**
2. Preencha os campos obrigatórios:

| Campo | Observação |
|-------|-----------|
| Nome | Aparece na vitrine e na nota do pedido |
| Slug | Gerado automaticamente — define a URL do produto |
| Preço | Em reais (ex: `49.90`) — convertido para centavos internamente |
| Preço comparativo | Opcional — riscado na vitrine para indicar desconto |
| SKU | Código interno do produto |
| Estoque | Quantidade disponível — decrementado a cada compra |
| Categoria | Selecionar uma das categorias criadas no passo anterior |
| Status | `active` para aparecer na vitrine |

3. Na aba **Dimensões (Frete)**, informe peso e medidas do produto
4. Na aba **Imagens**, faça upload das fotos (primeira imagem é a capa)
5. Salve

---

### Passo 6 — Configurar zonas e tarifas de frete

**No painel `/painel` → Frete → Zonas de Frete:**

1. Crie uma zona por região (ex: "Sul e Sudeste", "Nacional")
2. Marque os estados que pertencem à zona

**No painel `/painel` → Frete → Tarifas de Frete:**

1. Para cada zona, crie uma ou mais tarifas:

| Campo | Observação |
|-------|-----------|
| Nome | Exibido para o cliente (ex: "PAC", "SEDEX") |
| Transportadora | Melhor Envio, Correios, motoboy etc. |
| Preço base | Valor fixo cobrado independente do peso |
| Adicional por kg | Valor somado por kg do pedido |
| Prazo mínimo / máximo | Em dias úteis |
| Frete grátis acima de | Subtotal mínimo para isenção (deixe vazio para desabilitar) |

---

### Passo 7 — Criar cupons de desconto (opcional)

**No painel `/painel` → Catálogo → Cupons:**

| Campo | Observação |
|-------|-----------|
| Código | Digitado pelo cliente no carrinho — armazenado em maiúsculas |
| Tipo | `percent` (%) ou `fixed` (R$ fixo) |
| Valor | Percentual ou valor em reais |
| Pedido mínimo | Subtotal mínimo para o cupom ser válido |
| Limite de usos | Vazio = usos ilimitados |
| Validade | Data de expiração |

---

### Passo 8 — Testar a vitrine

Acesse `http://localhost/loja/{slug}` substituindo `{slug}` pelo slug da loja cadastrada.

**Fluxo de compra do cliente:**

```
Vitrine → Catálogo → Produto → Adicionar ao carrinho
       → Carrinho (aplicar cupom) → Checkout
       → Endereço → Calcular frete → Escolher pagamento
       → Confirmar pedido → Página de confirmação (QR Code PIX / boleto)
```

---

## Operação diária — Painel do Lojista (`/painel`)

### Gerenciar pedidos

**No painel `/painel` → Pedidos:**

Os pedidos passam pelos seguintes status:

```
pending → confirmed → processing → shipped → delivered
                          ↓
                       cancelled
```

| Status | Significado | Ação disponível |
|--------|-------------|-----------------|
| `pending` | Aguardando pagamento | — |
| `confirmed` | Pagamento aprovado | Confirmar recebimento do pagamento |
| `processing` | Em separação/embalagem | Marcar como Em Processamento |
| `shipped` | Enviado | Marcar como Enviado (informar código de rastreio) |
| `delivered` | Entregue | Marcar como Entregue |
| `cancelled` | Cancelado | Cancelar pedido |

Para avançar o status de um pedido:
1. Abra o pedido
2. Use as ações disponíveis no topo da página
3. Ao clicar em **Enviado**, informe o código de rastreio

### Monitorar estoque

O painel exibe um widget de **Estoque Baixo** no dashboard. Produtos com estoque abaixo do limite configurado são listados para ação imediata.

### Clientes

**No painel `/painel` → Clientes:**

Lista de todos os compradores cadastrados na loja. Exibe nome, e-mail, telefone e data de cadastro. Visualização apenas — edição de clientes não está disponível no painel do lojista.

---

## Pagamentos

A plataforma usa o **Mercado Pago** como gateway único. Três métodos disponíveis:

| Método | Como funciona |
|--------|--------------|
| **PIX** | Gera QR Code + copia-e-cola. Aprovação instantânea via webhook |
| **Cartão de crédito** | Token gerado pelo MP.js no frontend + parcelamento |
| **Boleto bancário** | Gera link do boleto. Aprovação em até 3 dias úteis |

**Configuração necessária** (variáveis de ambiente):

```env
MERCADOPAGO_ACCESS_TOKEN=seu_token_de_producao
MERCADOPAGO_PUBLIC_KEY=sua_public_key
MERCADOPAGO_WEBHOOK_SECRET=sua_chave_webhook
```

Para testes, use as credenciais de sandbox do Mercado Pago.

O webhook de confirmação de pagamento é recebido em:
```
POST /api/v1/webhooks/mercadopago
```

Quando o pagamento é aprovado, o sistema automaticamente:
1. Marca o pedido como `confirmed` e `paid`
2. Gera a etiqueta de envio (se Melhor Envio estiver configurado)
3. Envia o e-mail de confirmação ao cliente

---

## Marketing — Redes Sociais

**No painel `/painel` → Marketing → Contas Sociais:**

1. Clique em **Conectar conta**
2. Escolha **Instagram** ou **Facebook**
3. Autorize via OAuth — o token é salvo automaticamente

**Agendar posts:**

**No painel `/painel` → Marketing → Posts Agendados:**

1. Clique em **Novo Post**
2. Selecione a conta conectada
3. Escreva a legenda
4. Informe a data e hora de publicação
5. Salve

Os posts são publicados automaticamente a cada hora pelo job `PublishScheduledPost`.

**Auto-post de produtos:**

Quando habilitado (`MARKETING_AUTO_POST=true`), cada produto novo cadastrado com imagem gera automaticamente um post agendado em todas as contas ativas.

---

## Agenda de Eventos e Cursos

Disponível quando `feature.agenda = true` na loja.

**No painel `/painel` → Agenda:**

1. Clique em **Novo Evento**
2. Preencha:

| Campo | Observação |
|-------|-----------|
| Tipo | `evento`, `curso` ou `workshop` |
| Título e slug | Slug define a URL na vitrine |
| Descrição | Texto completo exibido na página do evento |
| Data/hora início e fim | Período do evento |
| Local | Endereço físico ou "Online" |
| Vagas | Deixe vazio para sem limite |
| Preço | `0` para evento gratuito |

3. Após salvar, use a ação **Publicar** para tornar visível na vitrine

**Inscrições:**

- Eventos gratuitos: inscrição confirmada imediatamente, e-mail enviado ao participante
- Eventos pagos: inscrição redireciona para o checkout existente

**Exportar lista de inscritos:** Na página do evento, use o botão **Exportar CSV**.

---

## Marketplace — Multi-Seller

Disponível quando `feature.marketplace = true`.

**Como sellers se cadastram:**

Via API:
```http
POST /api/v1/sellers/register
Authorization: Bearer {token_do_usuario}

{
  "name": "Minha Marca",
  "slug": "minha-marca",
  "description": "Descrição da marca",
  "commission_rate": 10
}
```

**Aprovação de sellers:**

**No painel `/admin` → Marketplace → Sellers:**

1. Abra o seller pendente
2. Clique em **Aprovar** ou **Suspender**

Apenas sellers com status `active` aparecem na vitrine e podem vender.

**Comissões e repasses:**

- A comissão é calculada automaticamente no checkout para cada item de seller
- Todo segundo-feira às 9h, o job `ProcessPayoutJob` agrupa comissões pendentes e cria registros de repasse

---

## API REST — Integração com apps externos

Documentação resumida dos endpoints. Base URL: `http://seu-dominio/api/v1`

**Autenticação:**

```http
POST /auth/login
Content-Type: application/json
X-Tenant-ID: {tenant_id}

{
  "email": "usuario@email.com",
  "password": "senha"
}
```

Retorna `token` — envie em todas as requisições autenticadas:
```
Authorization: Bearer {token}
```

**Rate limits:**

| Grupo | Limite |
|-------|--------|
| Login / registro | 10 req/min por IP |
| Endpoints públicos | 60 req/min por IP |
| Endpoints autenticados | 1.000 req/min por usuário |

**Identificação do tenant na API:**

Envie o header em todas as requisições:
```
X-Tenant-ID: {tenant_id}
```

---

## Painel super admin (`/admin`)

Exclusivo para a equipe LucraVendas. Credenciais padrão do seeder:

```
E-mail: jmarciosilva@gmail.com
Senha:  12345678
```

**O que é possível fazer:**

| Seção | Operações |
|-------|-----------|
| Lojas (Tenants) | Criar, editar, suspender lojas — definir perfil, tema e features |
| Usuários | Criar e gerenciar usuários — atribuir roles e tenant |
| Catálogo | Visualizar produtos e categorias de qualquer loja |
| Financeiro | Visualizar todas as transações Mercado Pago — estornar pagamentos |
| Marketplace | Aprovar/suspender sellers — visualizar comissões e repasses |
| Frete | Configurar zonas e tarifas globais |
| Marketing | Visualizar contas sociais e posts de todas as lojas |
| Plataforma | Dashboard Horizon (filas), métricas de marketplace |

**Importação via planilha:**

Nas listagens de Produtos, Categorias e Usuários, use o botão **Importar Planilha** para cadastro em lote via CSV. Use **Baixar modelo CSV** para obter o formato correto.

---

## Jobs agendados

Os seguintes processos rodam automaticamente:

| Job | Frequência | O que faz |
|-----|-----------|-----------|
| `PublishScheduledPost` | A cada hora | Publica posts de redes sociais com horário vencido |
| `ExpireAbandonedCarts` | A cada hora | Remove carrinhos sem atividade há mais de 24h |
| `ProcessPayoutJob` | Segunda às 9h | Agrupa comissões pendentes e cria repasses para sellers |
| `horizon:snapshot` | A cada 5 min | Coleta métricas de filas para o dashboard Horizon |
| `backup:run --only-db` | Diário às 2h | Backup do banco de dados |
| `backup:clean` | Diário às 2h30 | Remove backups antigos conforme política de retenção |
| `backup:monitor` | Diário às 9h | Alerta se backup falhar |

---

## Variáveis de ambiente essenciais

```env
# Banco e cache
DB_HOST=127.0.0.1
DB_DATABASE=lucravendas
REDIS_HOST=127.0.0.1

# Pagamentos
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_PUBLIC_KEY=
MERCADOPAGO_WEBHOOK_SECRET=

# Frete
SHIPPING_GATEWAY=internal          # internal | melhorenvio | both
MELHORENVIO_TOKEN=
MELHORENVIO_SANDBOX=true

# Marketing
META_APP_ID=
META_APP_SECRET=
META_REDIRECT_URI=
MARKETING_AUTO_POST=false

# Observabilidade
SENTRY_LARAVEL_DSN=
TELESCOPE_ENABLED=true
TELESCOPE_ALLOWED_EMAILS=jmarciosilva@gmail.com

# Backup
BACKUP_DISK=s3
```

---

## URLs de referência rápida

| O que acessar | URL |
|---------------|-----|
| Painel super admin | `http://localhost/admin` |
| Painel do lojista | `http://localhost/painel` |
| Vitrine da loja | `http://localhost/loja/{slug}` |
| Agenda da loja | `http://localhost/loja/{slug}/agenda` |
| Monitoramento de filas | `http://localhost/horizon` |
| Diagnóstico (dev) | `http://localhost/telescope` |
| API | `http://localhost/api/v1` |
