#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_perfor070_cookie.txt"
PAGE_HTML="/tmp/hris_perfor070_page.html"

rm -f "$COOKIE" "$PAGE_HTML"

TOKEN="$(curl -ks -c "$COOKIE" "$BASE_URL/login" | grep -o 'name="_token" value="[^"]*' | sed 's/.*value="//')"

curl -ks -b "$COOKIE" -c "$COOKIE" -X POST "$BASE_URL/login" \
    --data-urlencode "_token=$TOKEN" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /tmp/hris_perfor070_login.html

CODE="$(curl -ks -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/performance/competency-matrix")"

echo "status:$CODE"
grep -q 'Matriks Kompetensi' "$PAGE_HTML" && echo 'page:ok'
grep -q 'Setup Matriks' "$PAGE_HTML" && echo 'setup:ok'
grep -q 'Mapping Role / Grade' "$PAGE_HTML" && echo 'mapping:ok'
