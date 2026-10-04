#!/usr/bin/env bash
set -euo pipefail

BASE="https://31.97.106.123:8091"
COOKIE="/tmp/hris_payrol032_cookie.txt"
LOGIN="/tmp/hris_payrol032_login.html"
PAGE="/tmp/hris_payrol032_page.html"

curl -k -sS -c "$COOKIE" "$BASE/login" > "$LOGIN"
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN" | head -1)

curl -k -sS -b "$COOKIE" -c "$COOKIE" \
  -o /tmp/hris_payrol032_post.html \
  -w "login %{http_code}\n" \
  -X POST "$BASE/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  -d "remember=1"

curl -k -L -sS -b "$COOKIE" -c "$COOKIE" -o "$PAGE" -w "page %{http_code}\n" "$BASE/payroll/salary-structures"
grep -q "Struktur Gaji" "$PAGE"
grep -q "Buat Salary Band" "$PAGE"
grep -q "Policy Akses Kompensasi" "$PAGE"
echo "smoke salary structures ok"
