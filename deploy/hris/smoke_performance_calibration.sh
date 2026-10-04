#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_perfor068_cookie.txt"
PAGE_HTML="/tmp/hris_perfor068_page.html"

rm -f "$COOKIE" "$PAGE_HTML"

TOKEN="$(curl -ks -c "$COOKIE" "$BASE_URL/login" | grep -o 'name="_token" value="[^"]*' | sed 's/.*value="//')"

curl -ks -b "$COOKIE" -c "$COOKIE" -X POST "$BASE_URL/login" \
    --data-urlencode "_token=$TOKEN" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /tmp/hris_perfor068_login.html

CODE="$(curl -ks -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/performance/calibration")"

echo "status:$CODE"
grep -q 'Kalibrasi Review' "$PAGE_HTML" && echo 'page:ok'
grep -q 'Setup Kalibrasi' "$PAGE_HTML" && echo 'setup:ok'
grep -q 'Distribusi Rating' "$PAGE_HTML" && echo 'distribution:ok'
