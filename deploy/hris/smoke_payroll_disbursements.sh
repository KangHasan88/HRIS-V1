#!/usr/bin/env bash
set -euo pipefail

BASE="https://31.97.106.123:8091"
COOKIE="/tmp/hris_payrol039_cookie.txt"
LOGIN="/tmp/hris_payrol039_login.html"
PAGE="/tmp/hris_payrol039_page.html"

curl -k -sS -c "$COOKIE" "$BASE/login" > "$LOGIN"
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN" | head -1)

curl -k -sS -b "$COOKIE" -c "$COOKIE" \
  -o /tmp/hris_payrol039_post.html \
  -w "login %{http_code}\n" \
  -X POST "$BASE/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  -d "remember=1"

curl -k -L -sS -b "$COOKIE" -c "$COOKIE" -o "$PAGE" -w "page %{http_code}\n" "$BASE/payroll/disbursements"
grep -q "Pencairan Gaji" "$PAGE"
grep -q "Buat Batch Pencairan Bank" "$PAGE"
grep -q "Host-to-host bank API" "$PAGE"
grep -q "Kontrol Integritas" "$PAGE"
grep -q "Daftar Batch Transfer" "$PAGE"
echo "smoke payroll disbursements ok"
