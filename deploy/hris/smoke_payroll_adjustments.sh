#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_payroll_adjustments_cookie.txt"
LOGIN="/tmp/hris_payroll_adjustments_login.html"
PAGE="/tmp/hris_payroll_adjustments_page.html"

curl -k -sS -c "$COOKIE" "$BASE_URL/login" -o "$LOGIN"
TOKEN=$(grep -o 'name="_token" value="[^"]*"' "$LOGIN" | sed 's/name="_token" value="//;s/"//')

STATUS=$(curl -k -sS -b "$COOKIE" -c "$COOKIE" -o /dev/null -w '%{http_code}' \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123")
echo "login $STATUS"

PAGE_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/payroll/adjustments")
echo "admin page $PAGE_STATUS"
grep -q "Penyesuaian Payroll" "$PAGE"
grep -q "Tambah Adjustment" "$PAGE"
grep -q "Daftar Adjustment" "$PAGE"

ESS_STATUS=$(curl -k -sS -b "$COOKIE" -o "$PAGE" -w '%{http_code}' "$BASE_URL/ess/payroll-adjustments")
echo "ess preview $ESS_STATUS"
grep -q "Penyesuaian Payroll Saya" "$PAGE"

echo "smoke payroll adjustments ok"
