#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-https://31.97.106.123:8091}"
COOKIE_JAR="/tmp/hris_psychotest_inv_cookie.txt"
LOGIN_PAGE="/tmp/hris_psychotest_inv_login.html"
PAGE="/tmp/hris_psychotest_inv_page.html"

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -d "_token=$TOKEN" \
  -d "email=admin@kurmigo.id" \
  -d "password=password123" \
  "$BASE_URL/login" -o /dev/null

STATUS=$(curl -k -sS -b "$COOKIE_JAR" -o "$PAGE" -w "%{http_code}" "$BASE_URL/recruitment/psychotest-invitations")
echo "status:$STATUS"

grep -q "Undangan Psikotest" "$PAGE"
grep -q "Buat Undangan" "$PAGE"
grep -q "Tracker Undangan" "$PAGE"
echo "page:ok"
