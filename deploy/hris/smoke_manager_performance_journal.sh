#!/usr/bin/env bash
set -euo pipefail

base_url="https://31.97.106.123:8091"
cookie_file="/tmp/hris_manage054_cookie.txt"
login_html="/tmp/hris_manage054_login.html"
page_html="/tmp/hris_manage054_page.html"

rm -f "$cookie_file" "$login_html" "$page_html"

curl -k -s -c "$cookie_file" "$base_url/login" -o "$login_html"
token="$(php8.3 -r '$html = file_get_contents($argv[1]); preg_match("/name=\"_token\" value=\"([^\"]+)/", $html, $m); echo $m[1] ?? "";' "$login_html")"

if [[ -z "$token" ]]; then
  echo "login_token:missing"
  exit 1
fi

curl -k -s -b "$cookie_file" -c "$cookie_file" \
  -X POST "$base_url/login" \
  --data-urlencode "_token=$token" \
  --data-urlencode "email=bima@kurmigo.id" \
  --data-urlencode "password=password123" \
  -o /tmp/hris_manage054_after_login.html \
  -w "login:%{http_code}\n"

curl -k -s -b "$cookie_file" "$base_url/manager/performance-journal" \
  -o "$page_html" \
  -w "journal:%{http_code}\n"

grep -E "Jurnal Performa Tim|Tambah Catatan Performa|Log One-on-One" "$page_html" | head -5
