#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_tax_simulations_cookie.txt"
LOGIN="/tmp/hris_tax_simulations_login.html"
PAGE="/tmp/hris_tax_simulations_page.html"

curl -k -sS -c "$COOKIE" "$BASE_URL/login" -o "$LOGIN"
TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$LOGIN" | sed 's/name="_token" value="//;s/"//')

STATUS=$(curl -k -sS -b "$COOKIE" -c "$COOKIE" -o /dev/null -w '%{http_code}' \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123")
echo "login $STATUS"

ADMIN_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/payroll/tax-simulations")
echo "admin page $ADMIN_STATUS"
grep -q "Simulasi PPh 21" "$PAGE"
grep -q "Buat Scenario" "$PAGE"
grep -q "THR / Bonus" "$PAGE"

ESS_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/ess/tax-simulations")
echo "ess preview $ESS_STATUS"
grep -q "Simulasi Pajak Saya" "$PAGE"

echo "smoke tax simulations ok"
