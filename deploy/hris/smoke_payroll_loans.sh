#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_payroll_loans_cookie.txt"
LOGIN="/tmp/hris_payroll_loans_login.html"
PAGE="/tmp/hris_payroll_loans_page.html"

curl -k -sS -c "$COOKIE" "$BASE_URL/login" -o "$LOGIN"
TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$LOGIN" | sed 's/name="_token" value="//;s/"//')

STATUS=$(curl -k -sS -b "$COOKIE" -c "$COOKIE" -o /dev/null -w '%{http_code}' \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123")
echo "login $STATUS"

PAGE_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/payroll/loans")
echo "admin page $PAGE_STATUS"

grep -q "Pinjaman &amp; Potongan\\|Pinjaman & Potongan" "$PAGE"
grep -q "Tambah Pinjaman" "$PAGE"
grep -q "Daftar Pinjaman" "$PAGE"

ESS_PAGE_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/ess/loans")
echo "ess preview $ESS_PAGE_STATUS"
grep -q "Pinjaman Saya" "$PAGE"

echo "smoke payroll loans ok"
