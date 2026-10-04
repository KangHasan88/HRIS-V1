#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://31.97.106.123:8091}"
COOKIE_JAR="$(mktemp)"
LOGIN_PAGE="$(mktemp)"
CONNECTOR_PAGE="$(mktemp)"
trap 'rm -f "$COOKIE_JAR" "$LOGIN_PAGE" "$CONNECTOR_PAGE"' EXIT

curl -k -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_PAGE"
TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' "$LOGIN_PAGE" | head -1)"

curl -k -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -d "_token=$TOKEN" \
    -d "email=admin@kurmigo.id" \
    -d "password=password123" \
    -d "remember=1" \
    "$BASE_URL/login" -o /tmp/hris_integr095_login.html

curl -k -sS -b "$COOKIE_JAR" "$BASE_URL/integrations/biometric-connectors" -o "$CONNECTOR_PAGE"

grep -q "Biometric Connector" "$CONNECTOR_PAGE"
grep -q "Adapter Database Lokal" "$CONNECTOR_PAGE"
grep -q "Local Database Adapter" "$CONNECTOR_PAGE"
grep -q "Database Credentials" "$CONNECTOR_PAGE"

echo "OK INTEGR-095 local database adapter smoke passed"
