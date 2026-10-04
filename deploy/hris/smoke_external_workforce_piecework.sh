#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE_JAR="$(mktemp)"
LOGIN_PAGE="$(mktemp)"
DASHBOARD_PAGE="$(mktemp)"
PIECEWORK_PAGE="$(mktemp)"
trap 'rm -f "$COOKIE_JAR" "$LOGIN_PAGE" "$DASHBOARD_PAGE" "$PIECEWORK_PAGE"' EXIT

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)"

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -d "_token=$TOKEN" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -d "remember=1" \
    "$BASE_URL/login" -o "$DASHBOARD_PAGE"

curl -k -sS -b "$COOKIE_JAR" "$BASE_URL/external-workforce/piecework" -o "$PIECEWORK_PAGE"

grep -q "Output Borongan" "$PIECEWORK_PAGE"
grep -q "Master Tarif Pekerjaan" "$PIECEWORK_PAGE"
grep -q "Input Output Borongan" "$PIECEWORK_PAGE"
grep -q "Daftar Output Borongan" "$PIECEWORK_PAGE"

echo "OK external workforce piecework smoke passed"
