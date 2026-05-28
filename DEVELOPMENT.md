# Guia de Desenvolvimento — LucraVendas

> Documento para desenvolvedores que precisam rodar, testar e contribuir com o projeto localmente.

---

## Pré-requisitos

Certifique-se de ter instalado na sua máquina:

| Ferramenta | Versão mínima | Verificar |
|---|---|---|
| Docker Desktop | 24+ | `docker --version` |
| Docker Compose | 2.20+ | `docker compose version` |
| Git | 2.40+ | `git --version` |

> PHP, Composer e Node **não precisam** ser instalados localmente — tudo roda dentro dos containers Docker.

---

## Configuração inicial (primeira vez)

Execute os passos abaixo **uma única vez** ao clonar o repositório.

### 1. Clone o repositório

```bash
git clone https://github.com/sua-org/lucravendas.git
cd lucravendas
```

### 2. Copie o arquivo de ambiente

```bash
cp .env.example .env
```

> O `.env` já está pré-configurado para funcionar com o Docker Compose local. Não é necessário editar nada para rodar o projeto em desenvolvimento.

### 3. Suba os containers

```bash
docker compose up -d
```

Aguarde todos os serviços ficarem saudáveis. Você pode verificar o status com:

```bash
docker compose ps
```

A coluna `STATUS` deve mostrar `healthy` para `mysql` e `redis`. Os demais devem estar `running`.

### 4. Instale as dependências PHP

```bash
docker compose exec app composer install
```

### 5. Gere a chave da aplicação

> Pule este passo se o `.env` já tiver `APP_KEY` preenchido.

```bash
docker compose exec app php artisan key:generate
```

### 6. Rode as migrations e os seeders

```bash
docker compose exec app php artisan migrate --seed
```

Este comando:
- Cria todas as tabelas no banco `lucravendas`
- Cria as roles e permissões (`super_admin`, `tenant_admin`, `customer`)
- Cria o usuário administrador padrão

### 7. Publique os assets do painel admin (Filament)

```bash
docker compose exec app php artisan filament:assets
```

### 8. Verifique a instalação

```bash
docker compose exec app php artisan about
```

O projeto está pronto quando o comando retornar sem erros.

---

## Uso diário

### Iniciar o projeto

```bash
docker compose up -d
```

### Parar o projeto

```bash
docker compose down
```

### Ver logs em tempo real

```bash
# Todos os serviços
docker compose logs -f

# Apenas a aplicação PHP
docker compose logs -f app
```

---

## Serviços disponíveis

| Serviço | URL | Descrição |
|---|---|---|
| API REST | `http://localhost:8000/api/v1/` | Endpoints da aplicação |
| Painel Admin | `http://localhost:8000/admin` | Filament v3 — acesso super_admin |
| Mailpit | `http://localhost:8025` | Interface para visualizar e-mails disparados |
| Meilisearch | `http://localhost:7700` | Painel do motor de busca |
| MySQL | `localhost:3306` | Banco de dados (cliente externo) |
| Redis | `localhost:6379` | Cache e filas |

### Como acessar cada serviço

**Abre direto no navegador**

| Serviço | Como usar |
|---|---|
| **Painel Admin** `localhost:8000/admin` | Interface web completa. Faça login com as credenciais abaixo |
| **Mailpit** `localhost:8025` | Interface web completa. Exibe todos os e-mails disparados pela aplicação |
| **Meilisearch** `localhost:7700` | Mini dashboard web para navegar índices e testar buscas |

**API REST — navegador parcial + Insomnia/Postman**

No navegador só é possível testar rotas `GET` públicas. Para `POST`, `PUT`, `DELETE` e qualquer rota com `Authorization: Bearer` é necessário usar **Insomnia**, **Postman** ou `curl`, pois o navegador não permite enviar body e headers customizados diretamente.

**Requer cliente de desktop (não é HTTP)**

| Serviço | Cliente recomendado | Credenciais |
|---|---|---|
| **MySQL** `localhost:3306` | TablePlus, DBeaver ou MySQL Workbench | host `localhost`, user `lucravendas`, senha `secret`, banco `lucravendas` |
| **Redis** `localhost:6379` | RedisInsight ou `redis-cli` | sem senha |

### Credenciais padrão do painel admin

```
Email:  admin@lucraone.com.br
Senha:  password
```

> Nunca use essas credenciais em produção.

---

## Rodando os testes

Os testes usam **SQLite em memória** (configurado em `phpunit.xml`), portanto rodam de forma isolada e independente do banco de dados Docker.

### Executar todos os testes

```bash
docker compose exec app php artisan test
```

Resultado esperado: **23 testes passando**, 0 falhas.

### Executar por suite

```bash
# Somente testes unitários (entidades de domínio, value objects)
docker compose exec app php artisan test --testsuite=Unit

# Somente testes de integração (API, banco, fluxos)
docker compose exec app php artisan test --testsuite=Feature
```

### Filtrar por nome ou arquivo

```bash
# Testes de autenticação
docker compose exec app php artisan test --filter=Auth

# Testes do módulo Tenant
docker compose exec app php artisan test --filter=Tenant

# Arquivo específico
docker compose exec app php artisan test tests/Feature/Auth/LoginTest.php
```

### Ver cobertura de código

```bash
docker compose exec app php artisan test --coverage
```

### O que cada grupo de testes valida

| Suite | Arquivo | O que testa |
|---|---|---|
| Unit | `tests/Unit/Tenant/TenantEntityTest.php` | Entidade `Tenant`, criação, status, planos, eventos de domínio |
| Unit | `tests/Unit/Tenant/TenantEntityTest.php` | Value Objects: `TenantSlug` (validação, normalização), `TenantPlan` (planos válidos) |
| Feature | `tests/Feature/Auth/RegisterTest.php` | Registro de usuário, validação, role padrão `customer`, e-mail duplicado |
| Feature | `tests/Feature/Auth/LoginTest.php` | Login com sucesso, credenciais inválidas, logout, endpoint `/me` |

---

## Testando a API manualmente

### Registrar um novo usuário

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha12345",
    "password_confirmation": "senha12345"
  }'
```

Resposta esperada (`201 Created`):

```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "roles": ["customer"]
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha12345"
  }'
```

### Consultar usuário autenticado

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

### Logout

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

> Para requisições de um tenant específico (ex.: API mobile), adicione o header `X-Tenant-ID: uuid-do-tenant`.

---

## Comandos Artisan úteis

```bash
# Criar um novo tenant
docker compose exec app php artisan tenant:create "Loja da Maria" loja-da-maria --plan=free

# Rodar migrations em todos os tenants
docker compose exec app php artisan tenants:migrate

# Rodar migrations em um tenant específico
docker compose exec app php artisan tenants:migrate --tenants=UUID_DO_TENANT

# Limpar todos os caches
docker compose exec app php artisan optimize:clear

# Listar todas as rotas registradas
docker compose exec app php artisan route:list

# Abrir o REPL interativo do Laravel (Tinker)
docker compose exec app php artisan tinker
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
│   └── ExampleTest.php
└── Unit/
    ├── Tenant/
    │   └── TenantEntityTest.php      # Entidade Tenant + Value Objects
    └── ExampleTest.php
```

> Ao criar novos módulos, siga a mesma convenção: `tests/Feature/{Modulo}/` e `tests/Unit/{Modulo}/`.

---

## Convenções do projeto

- **Arquitetura:** DDD — lógica de negócio fica em `app/Modules/{Modulo}/Domain/` e `Application/`. Nunca em Controllers ou Models Eloquent.
- **Padrão de código:** PSR-12, verificado com Laravel Pint. Rode `docker compose exec app ./vendor/bin/pint` antes de commitar.
- **Branches:** crie a partir de `develop` com o prefixo `feature/`, `fix/` ou `chore/`.
- **Testes:** toda funcionalidade nova deve ter testes antes do Pull Request.

---

## Solução de problemas comuns

**Container `app` não inicia**
```bash
docker compose logs app
# Verifique se o Dockerfile está correto e reconstrua:
docker compose build --no-cache app
docker compose up -d
```

**Erro "could not find driver" (MySQL)**
```bash
# Confirme que o container mysql está healthy antes de migrar
docker compose ps
# Se necessário, aguarde e tente novamente:
docker compose exec app php artisan migrate
```

**Erro de permissão em `storage/` ou `bootstrap/cache/`**
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

**Migrations já existem / banco sujo**
```bash
# Recria todo o banco (APAGA os dados)
docker compose exec app php artisan migrate:fresh --seed
```

**Porta 3306 ou 8000 já em uso**

Edite o `docker-compose.yml` e altere o mapeamento de porta. Ex.: `"8080:80"` para o nginx.
