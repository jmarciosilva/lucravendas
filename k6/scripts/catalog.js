/**
 * k6 — Teste de carga: listagem e busca de produtos
 *
 * Uso:
 *   k6 run --env BASE_URL=http://localhost:8000 --env TENANT_ID=minha-loja k6/scripts/catalog.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  scenarios: {
    list_products: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '10s', target: 20 },
        { duration: '30s', target: 50 },
        { duration: '10s', target: 0 },
      ],
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const TENANT_ID = __ENV.TENANT_ID || 'test-tenant';

const headers = { 'X-Tenant-ID': TENANT_ID, 'Accept': 'application/json' };

export default function () {
  // Listagem de produtos
  const list = http.get(`${BASE_URL}/api/v1/products`, { headers });
  check(list, {
    'products list status 200': (r) => r.status === 200,
    'products list has data': (r) => JSON.parse(r.body).data !== undefined,
  });

  sleep(0.5);

  // Listagem de categorias
  const cats = http.get(`${BASE_URL}/api/v1/categories`, { headers });
  check(cats, { 'categories status 200': (r) => r.status === 200 });

  sleep(0.5);

  // Busca de produto
  const search = http.get(`${BASE_URL}/api/v1/products/search?q=produto`, { headers });
  check(search, { 'search status 200 or 422': (r) => [200, 422].includes(r.status) });

  sleep(1);
}
