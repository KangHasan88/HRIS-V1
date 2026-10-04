#!/usr/bin/env bash
set -euo pipefail

BASE="https://31.97.106.123:8091"
COOKIE="/tmp/hris_payrol035_cookie.txt"
LOGIN="/tmp/hris_payrol035_login.html"
PAGE="/tmp/hris_payrol035_page.html"

curl -k -sS -c "$COOKIE" "$BASE/login" > "$LOGIN"
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN" | head -1)

curl -k -sS -b "$COOKIE" -c "$COOKIE" \
  -o /tmp/hris_payrol035_post.html \
  -w "login %{http_code}\n" \
  -X POST "$BASE/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  -d "remember=1"

curl -k -L -sS -b "$COOKIE" -c "$COOKIE" -o "$PAGE" -w "page %{http_code}\n" "$BASE/payroll/run-wizard"
grep -q "Wizard Proses Payroll" "$PAGE"
grep -q "Buat Payroll Run" "$PAGE"
grep -q "Daftar Payroll Run" "$PAGE"
echo "smoke payroll run wizard ok"
