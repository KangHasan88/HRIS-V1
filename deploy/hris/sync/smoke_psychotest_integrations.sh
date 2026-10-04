#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-https://31.97.106.123:8091}"
COOKIE_JAR="/tmp/hris_psychotest_cookie.txt"
LOGIN_PAGE="/tmp/hris_psychotest_login.html"
PAGE="/tmp/hris_psychotest_page.html"

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  "$BASE_URL/login" -o /dev/null

STATUS=$(curl -k -sS -b "$COOKIE_JAR" -o "$PAGE" -w "%{http_code}" "$BASE_URL/recruitment/psychotest-integrations")
echo "status:$STATUS"

grep -q "Integrasi Psikotest" "$PAGE"
grep -q "Tambah Provider" "$PAGE"
grep -q "Tambah Paket Test" "$PAGE"
grep -q "Provider & Paket Aktif" "$PAGE"
echo "page:ok"
