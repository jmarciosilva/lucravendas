# Guia de Desenvolvimento — LucraVendas

> Documento para desenvolvedores que precisam rodar, testar e contribuir com o projeto localmente.

---

## Pré-requisitos

### Opção A — XAMPP (recomendado para desenvolvimento local)

| Ferramenta | Versão mínima | Verificar |
|---|---|---|
| XAMPP | 8.2+ | painel XAMPP |
| PHP (via XAMPP) | 8.2.12+ | `php --version` |
| Composer | 2.7+ | `composer --version` |
| Node.js | 20+ | `node --version` |
| Git | 2.40+ | `git --version` |

> O MySQL do XAMPP é usado como banco de dados. Redis, Meilisearch e Mailpit não são necessários para desenvolvimento local — a aplicação usa drivers alternativos (`file`, `sync`, `log`).

### Opção B — Docker

| Ferramenta | Versão mínima | Verificar |
|---|---|---|
| Docker Desktop | 24+ | `docker --version` |
| Docker Compose | 2.20+ | `docker compose version` |
| Git | 2.40+ | `git --version` |

---

## Configuração inicial — XAMPP (Opção A)

### 1. Clone o repositório

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
```

### 2. Instale as dependências

```bash
composer install
npm install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as seguintes configurações para ambiente XAMPP:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lucravendas
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

SCOUT_DRIVER=null

MAIL_MAILER=log
```

### 4. Crie o banco de dados

Abra o phpMyAdmin (`http://localhost/phpmyadmin`) e crie o banco:

```sql
CREATE DATABASE lucravendas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ou via linha de comando:

```bash
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lucravendas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Rode as migrations e seeders

```bash
php artisan migrate --seed
```

Este comando:
- Cria todas as tabelas do banco `lucravendas`
- Cria as roles e permissões (`super_admin`, `tenant_admin`, `customer`)
- Cria o usuário administrador padrão

### 6. Compile os assets

```bash
npm run build
```

### 7. Inicie o servidor

```bash
php artisan serve
```

A aplicação estará disponível em `http://localhost:8000`.

---

## Configuração inicial — Docker (Opção B)

### 1. Clone e configure o ambiente

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
cp .env.example .env
```

### 2. Suba os containers

```bash
docker compose up -d
```

Aguarde todos os serviços ficarem saudáveis:

```bash
docker compose ps
```

A coluna `STATUS` deve mostrar `healthy` para `mysql` e `redis`.

### 3. Instale dependências e configure

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan filament:assets
```

---

## Uso diário — XAMPP

### Iniciar

```bash
# Inicie o MySQL no painel XAMPP, depois:
php artisan serve
```

### Parar

Pressione `Ctrl+C` no terminal.

### Ver logs

Os logs ficam em `storage/logs/laravel.log`. E-mails disparados aparecem nesse mesmo arquivo (driver `log`).

---

## Uso diário — Docker

```bash
# Iniciar
docker compose up -d

# Parar
docker compose down

# Ver logs
docker compose logs -f app
```

---

## Serviços disponíveis

### Com XAMPP (desenvolvimento local)

| Serviço | URL | Descrição |
|---|---|---|
| API REST | `http://localhost:8000/api/v1/` | Endpoints da aplicação |
| Painel Admin | `http://localhost:8000/admin` | Filament v3 — acesso super_admin |
| phpMyAdmin | `http://localhost/phpmyadmin` | Interface web do banco (via XAMPP) |
| Logs de e-mail | `storage/logs/laravel.log` | E-mails são logados em arquivo |

> Redis, Meilisearch e Mailpit não são necessários em desenvolvimento XAMPP.

### Com Docker

| Serviço | URL | Descrição |
|---|---|---|
| API REST | `http://localhost:8000/api/v1/` | Endpoints da aplicação |
| Painel Admin | `http://localhost:8000/admin` | Filament v3 — acesso super_admin |
| Mailpit | `http://localhost:8025` | Interface para visualizar e-mails |
| Meilisearch | `http://localhost:7700` | Painel do motor de busca |
| phpMyAdmin | `http://localhost:8080` | Interface web do banco de dados |
| MySQL | `localhost:3306` | Banco de dados (cliente externo) |
| Redis | `localhost:6379` | Cache e filas |

### Credenciais padrão do painel admin

```
URL:    http://localhost:8000/admin
Email:  jmarciosilva@gmail.com
Senha:  12345678
```

> Nunca use essas credenciais em produção.

---

## Rodando os testes

Os testes usam **SQLite em memória** (configurado em `phpunit.xml`), portanto rodam de forma isolada e independente do banco de dados principal.

### Executar todos os testes

```bash
# XAMPP
php artisan test

# Docker
docker compose exec app php artisan test
```

Resultado esperado: **53 testes passando**, 0 falhas.

### Executar por suite

```bash
# Somente testes unitários (entidades de domínio, value objects)
php artisan test --testsuite=Unit

# Somente testes de integração (API, banco, fluxos)
php artisan test --testsuite=Feature
```

### Filtrar por módulo ou arquivo

```bash
php artisan test --filter=Auth
php artisan test --filter=Catalog
php artisan test tests/Feature/Catalog/ProductTest.php
php artisan test tests/Feature/Catalog/CategoryTest.php
```

### O que cada grupo de testes valida

| Suite | Arquivo | O que testa |
|---|---|---|
| Unit | `tests/Unit/Tenant/TenantEntityTest.php` | Entidade `Tenant`, status, planos, eventos de domínio, Value Objects |
| Unit | `tests/Unit/Catalog/ProductEntityTest.php` | Entidade `Product`, publicação, estoque, eventos de domínio |
| Unit | `tests/Unit/Catalog/MoneyTest.php` | Value Object `Money` — centavos, reais, formatação, comparação |
| Feature | `tests/Feature/Auth/RegisterTest.php` | Registro, validação, role padrão, e-mail duplicado |
| Feature | `tests/Feature/Auth/LoginTest.php` | Login, credenciais inválidas, logout, `/me` |
| Feature | `tests/Feature/Catalog/CategoryTest.php` | Árvore, criação, subcategorias, isolamento por tenant |
| Feature | `tests/Feature/Catalog/ProductTest.php` | CRUD, filtros de preço, soft delete, isolamento, rascunhos |

---

## Importação de dados via planilha

O painel admin suporta importação em massa de categorias, produtos e usuários via CSV ou XLSX. O botão **"Importar Planilha"** está disponível nas páginas de listagem de cada entidade.

### Fluxo de importação

1. Acesse `/admin/categories`, `/admin/products` ou `/admin/users`
2. Clique em **"Importar Planilha"**
3. Selecione a loja (tenant) de destino no formulário
4. Clique em **"Baixar modelo"** para obter o CSV com as colunas corretas
5. Preencha o arquivo e faça o upload
6. Linhas válidas são salvas; linhas com erro aparecem no relatório com motivo e número da linha

### Formato das planilhas

**Categorias (`categorias.csv`)**

| Coluna | Obrigatório | Exemplo |
|---|---|---|
| name | Sim | Roupas |
| slug | Não | roupas *(auto-gerado se vazio)* |
| parent_slug | Não | *(slug da categoria pai)* |
| sort_order | Não | 0 |
| is_active | Não | true |

> Categorias pai devem aparecer **antes** das filhas no arquivo.

**Produtos (`produtos.csv`)**

| Coluna | Obrigatório | Exemplo |
|---|---|---|
| name | Sim | Camiseta Básica |
| slug | Não | camiseta-basica *(auto-gerado se vazio)* |
| price | Sim | 29.90 *(em reais)* |
| description | Não | Camiseta 100% algodão |
| compare_price | Não | 39.90 *(em reais)* |
| sku | Não | CAM-001 |
| stock | Não | 50 |
| category_slug | Não | roupas |
| status | Não | draft / active / inactive |

> Preços informados em reais — convertidos para centavos automaticamente.

**Usuários (`usuarios.csv`)**

| Coluna | Obrigatório | Exemplo |
|---|---|---|
| name | Sim | José Márcio Silva |
| email | Sim | jose@example.com |
| password | Sim | senha12345 |
| phone | Não | (11)99999-9999 |
| role | Não | customer / tenant_admin / super_admin |

---

## Testando a API manualmente

### Observação sobre preços

Preços são enviados e recebidos **em reais** na API. O banco armazena em centavos internamente.

```json
{ "price": 49.90 }              ← envie assim
{ "data": { "price": 49.9 } }   ← receberá assim
```

### Autenticação

```bash
# Registrar
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name": "João Silva", "email": "joao@example.com", "password": "senha12345", "password_confirmation": "senha12345"}'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "joao@example.com", "password": "senha12345"}'

# Usuário autenticado
curl http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer SEU_TOKEN"

# Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer SEU_TOKEN"
```

> Para requisições de um tenant específico, adicione o header `X-Tenant-ID: uuid-do-tenant`.

### Catálogo — Categorias

```bash
# Listar (público)
curl http://localhost:8000/api/v1/categories -H "X-Tenant-ID: uuid"

# Criar (requer tenant_admin)
curl -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Tenant-ID: uuid" \
  -d '{"name": "Roupas"}'
```

### Catálogo — Produtos

```bash
# Listar com filtros (público)
curl "http://localhost:8000/api/v1/products?category_id=1&min_price=10&max_price=100&per_page=15" \
  -H "X-Tenant-ID: uuid"

# Detalhe pelo slug (público)
curl http://localhost:8000/api/v1/products/camiseta-azul -H "X-Tenant-ID: uuid"

# Criar (requer tenant_admin)
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Tenant-ID: uuid" \
  -d '{"name": "Camiseta Azul", "price": 49.90, "stock": 100}'

# Upload de imagem
curl -X POST http://localhost:8000/api/v1/products/1/images \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Tenant-ID: uuid" \
  -F "image=@/caminho/imagem.jpg"

# Remover (soft delete)
curl -X DELETE http://localhost:8000/api/v1/products/1 \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-Tenant-ID: uuid"
```

---

## Comandos Artisan úteis

```bash
# Limpar todos os caches
php artisan optimize:clear

# Listar todas as rotas registradas
php artisan route:list

# REPL interativo (Tinker)
php artisan tinker

# Rodar migrations em todos os tenants
php artisan tenants:migrate

# Rodar migrations em um tenant específico
php artisan tenants:migrate --tenants=UUID_DO_TENANT
```

---

## Estrutura de testes

```
tests/
├── Pest.php                          # Configuração global do Pest
├── TestCase.php                      # Base: semeia roles/permissions em cada teste Feature
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php             # Login, logout, /me, token inválido
│   │   └── RegisterTest.php          # Registro, validação, role padrão
│   ├── Catalog/
│   │   ├── CategoryTest.php          # Árvore, criação, subcategorias, isolamento
│   │   └── ProductTest.php           # CRUD, filtros, soft delete, isolamento
│   └── ExampleTest.php
└── Unit/
    ├── Catalog/
    │   ├── MoneyTest.php             # Value Object Money
    │   └── ProductEntityTest.php     # Entidade Product
    ├── Tenant/
    │   └── TenantEntityTest.php      # Entidade Tenant + Value Objects
    └── ExampleTest.php
```

---

## Convenções do projeto

- **Arquitetura:** DDD — lógica de negócio fica em `Domain/` e `Application/`. Nunca em Controllers ou Models Eloquent.
- **Transações:** toda operação de escrita no banco usa `DB::transaction()` no Use Case Handler correspondente.
- **Tratamento de erros:** todos os métodos de Controller têm `try/catch` com mapeamento para HTTP status codes.
- **Padrão de código:** PSR-12 verificado com Laravel Pint. Rode `./vendor/bin/pint` antes de commitar.
- **Branches:** crie a partir de `develop` com prefixo `feature/`, `fix/` ou `chore/`.
- **Testes:** toda funcionalidade nova deve ter testes antes do Pull Request.

---

## Solução de problemas comuns

**Erro `platform_check.php` — PHP 8.3 requerido**

O `composer.json` tem `"platform": {"php": "8.2.12"}` para garantir pacotes compatíveis com PHP 8.2. Se ocorrer esse erro, rode:

```bash
composer install
```

**Erro "could not find driver" (pdo_mysql)**

Verifique se a extensão `pdo_mysql` está habilitada no `php.ini` do XAMPP:

```ini
extension=pdo_mysql
```

**Erro de permissão em `storage/` ou `bootstrap/cache/`**

```bash
# Windows (PowerShell)
icacls storage /grant Everyone:F /T
icacls bootstrap/cache /grant Everyone:F /T
```

**Banco de dados sujo — recriar do zero**

```bash
php artisan migrate:fresh --seed
```

**Porta 8000 já em uso**

```bash
php artisan serve --port=8001
```
