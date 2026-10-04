#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE="/tmp/hris_extwf096_cookie.txt"
LOGIN_HTML="/tmp/hris_extwf096_login.html"
PAGE_HTML="/tmp/hris_extwf096_page.html"

curl -k -s -c "$COOKIE" "$BASE_URL/login" > "$LOGIN_HTML"
TOKEN="$(grep -o 'name="_token" value="[^"]*' "$LOGIN_HTML" | sed 's/name="_token" value="//')"

curl -k -s -b "$COOKIE" -c "$COOKIE" \
  -X POST "$BASE_URL/login" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" > /tmp/hris_extwf096_post.html

CODE="$(curl -k -s -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/external-workforce/vendors")"

test "$CODE" = "200"
grep -q "Vendor Outsourcing" "$PAGE_HTML"
grep -q "Tambah Vendor" "$PAGE_HTML"
grep -q "Tambah Kontrak Jasa SDM" "$PAGE_HTML"
grep -q "Tanggung Jawab Compliance" "$PAGE_HTML"

echo "external workforce vendors smoke ok ($CODE)"
