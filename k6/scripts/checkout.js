/**
 * k6 — Teste de carga: fluxo completo login → carrinho → checkout
 *
 * Uso:
 *   k6 run --env BASE_URL=http://localhost:8000 --env TENANT_ID=minha-loja \
 *          --env USER_EMAIL=user@test.com --env USER_PASSWORD=12345678 \
 *          --env PRODUCT_ID=1 k6/scripts/checkout.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 10,
  duration: '30s',
  thresholds: {
    http_req_duration: ['p(95)<2000'],
    http_req_failed: ['rate<0.05'],
  },
};

const BASE_URL  = __ENV.BASE_URL  || 'http://localhost:8000';
const TENANT_ID = __ENV.TENANT_ID || 'test-tenant';
const EMAIL     = __ENV.USER_EMAIL    || 'user@test.com';
const PASSWORD  = __ENV.USER_PASSWORD || '12345678';
const PRODUCT_ID = parseInt(__ENV.PRODUCT_ID || '1');

const jsonHeaders = (token) => ({
  'X-Tenant-ID': TENANT_ID,
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
});

export default function () {
  // 1. Login
  const loginRes = http.post(`${BASE_URL}/api/v1/auth/login`, JSON.stringify({
    email: EMAIL,
    password: PASSWORD,
  }), { headers: jsonHeaders(null) });

  check(loginRes, { 'login 200': (r) => r.status === 200 });

  if (loginRes.status !== 200) {
    sleep(1);
    return;
  }

  const token = JSON.parse(loginRes.body).token;

  sleep(0.3);

  // 2. Adicionar item ao carrinho
  const addRes = http.post(`${BASE_URL}/api/v1/cart/items`, JSON.stringify({
    product_id: PRODUCT_ID,
    quantity: 1,
  }), { headers: jsonHeaders(token) });

  check(addRes, { 'add to cart 200/201': (r) => [200, 201].includes(r.status) });

  sleep(0.3);

  // 3. Ver carrinho
  const cartRes = http.get(`${BASE_URL}/api/v1/cart`, { headers: jsonHeaders(token) });
  check(cartRes, { 'get cart 200': (r) => r.status === 200 });

  sleep(0.5);
}
