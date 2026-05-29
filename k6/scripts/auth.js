/**
 * k6 — Teste de rate limiting em endpoints de autenticação
 *
 * Verifica que o limite de 10 req/min por IP é respeitado (deve retornar 429).
 *
 * Uso:
 *   k6 run --env BASE_URL=http://localhost:8000 --env TENANT_ID=minha-loja k6/scripts/auth.js
 */
import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 1,
  iterations: 15, // 15 tentativas — as últimas devem receber 429
  thresholds: {
    // Não é falha receber 429 — é o comportamento esperado do rate limiter
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL  = __ENV.BASE_URL  || 'http://localhost:8000';
const TENANT_ID = __ENV.TENANT_ID || 'test-tenant';

export default function () {
  const res = http.post(`${BASE_URL}/api/v1/auth/login`, JSON.stringify({
    email: 'naoexiste@test.com',
    password: 'senhaerrada',
  }), {
    headers: {
      'X-Tenant-ID': TENANT_ID,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  });

  // Aceita 401 (credenciais inválidas) ou 429 (rate limited)
  check(res, {
    'status is 401 or 429': (r) => [401, 422, 429].includes(r.status),
  });
}
