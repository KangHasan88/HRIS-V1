#!/usr/bin/env bash
set -euo pipefail

BASE_URL="https://31.97.106.123:8091"
COOKIE="/tmp/hris_perfor067_cookie.txt"
LOGIN_HTML="/tmp/hris_perfor067_login.html"
PAGE_HTML="/tmp/hris_perfor067_page.html"

rm -f "$COOKIE" "$LOGIN_HTML" "$PAGE_HTML"

TOKEN="$(curl -ks -c "$COOKIE" "$BASE_URL/login" | grep -o 'name="_token" value="[^"]*' | sed 's/.*value="//')"

curl -ks -b "$COOKIE" -c "$COOKIE" -X POST "$BASE_URL/login" \
    --data-urlencode "_token=$TOKEN" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o "$LOGIN_HTML"

CODE="$(curl -ks -b "$COOKIE" -o "$PAGE_HTML" -w '%{http_code}' "$BASE_URL/performance/goal-okr")"

echo "status:$CODE"
grep -q 'Goal &amp; OKR' "$PAGE_HTML" && echo 'page:ok'
grep -q 'Setup Goal/OKR' "$PAGE_HTML" && echo 'setup:ok'
grep -q 'Check-in Progress' "$PAGE_HTML" && echo 'checkin:ok'
