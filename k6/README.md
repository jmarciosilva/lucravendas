# Load Testing — LucraVendas

Scripts de teste de carga usando [k6](https://k6.io).

## Instalação

```bash
# macOS
brew install k6

# Linux (Debian/Ubuntu)
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6

# Windows (via Chocolatey)
choco install k6

# Windows (via winget)
winget install k6 --source winget
```

## Scripts disponíveis

| Script | Descrição | VUs | Duração |
|---|---|---|---|
| `catalog.js` | Listagem e busca de produtos | 0→50 | ~50s |
| `checkout.js` | Login → Carrinho → Checkout | 10 | 30s |
| `auth.js` | Rate limiting em login/register | 1 | 15 iterações |

## Como rodar

```bash
# Teste de catálogo (substitua os valores conforme seu ambiente)
k6 run \
  --env BASE_URL=http://localhost:8000 \
  --env TENANT_ID=minha-loja \
  k6/scripts/catalog.js

# Teste de checkout (requer usuário e produto criados)
k6 run \
  --env BASE_URL=http://localhost:8000 \
  --env TENANT_ID=minha-loja \
  --env USER_EMAIL=usuario@teste.com \
  --env USER_PASSWORD=12345678 \
  --env PRODUCT_ID=1 \
  k6/scripts/checkout.js

# Teste de rate limiting
k6 run \
  --env BASE_URL=http://localhost:8000 \
  --env TENANT_ID=minha-loja \
  k6/scripts/auth.js
```

## Thresholds configurados

- **p95 de latência** < 500ms (catálogo) / < 2s (checkout)
- **Taxa de falha** < 1% (catálogo/auth) / < 5% (checkout)

## Observações

- Os scripts assumem que o servidor está rodando localmente em `http://localhost:8000`
- Para testar rate limiting, certifique-se de que `CACHE_STORE=redis` está configurado
- Horizon deve estar rodando para monitorar o processamento de filas durante os testes
