#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE_JAR="$(mktemp)"
LOGIN_PAGE="$(mktemp)"
PAGE="$(mktemp)"
trap 'rm -f "$COOKIE_JAR" "$LOGIN_PAGE" "$PAGE"' EXIT

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)"

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -d "_token=$TOKEN" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -d "remember=1" \
    "$BASE_URL/login" -o /tmp/hris_extwf100_login.html

curl -k -sS -b "$COOKIE_JAR" "$BASE_URL/external-workforce/vendor-invoices" -o "$PAGE"

grep -q "Rekonsiliasi Tagihan Vendor" "$PAGE"
grep -q "Buat Invoice Rekonsiliasi" "$PAGE"
grep -q "Daftar Invoice Vendor" "$PAGE"
grep -q "Tagihan Vendor" "$PAGE"

echo "OK external workforce vendor invoices smoke passed"
