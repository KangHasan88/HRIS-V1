#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE="/tmp/hris_extwf097_cookie.txt"
LOGIN_HTML="/tmp/hris_extwf097_login.html"
PAGE_HTML="/tmp/hris_extwf097_page.html"

curl -k -s -c "$COOKIE" "$BASE_URL/login" > "$LOGIN_HTML"
TOKEN="$(grep -o 'name="_token" value="[^"]*' "$LOGIN_HTML" | sed 's/name="_token" value="//')"

curl -k -s -b "$COOKIE" -c "$COOKIE" \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" > /tmp/hris_extwf097_post.html

CODE="$(curl -k -s -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/external-workforce/workers")"

test "$CODE" = "200"
grep -q "Worker Non-Employee" "$PAGE_HTML"
grep -q "Tambah Worker" "$PAGE_HTML"
grep -q "Payroll / Invoice" "$PAGE_HTML"
grep -q "Dokumen & Akses" "$PAGE_HTML"

echo "external workforce workers smoke ok ($CODE)"
