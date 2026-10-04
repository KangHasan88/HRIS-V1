#!/usr/bin/env bash
set -euo pipefail

tmpcookie=/tmp/hris_payroll_foundation_cookie.txt
login=/tmp/hris_payroll_foundation_login.html
page=/tmp/hris_payroll_foundation_page.html

curl -k -s -c "$tmpcookie" https://31.97.106.123:8091/login -o "$login"
token=$(grep -o 'name="_token" value="[^"]*' "$login" | sed 's/.*value="//')

curl -k -s -b "$tmpcookie" -c "$tmpcookie" \
    -X POST https://31.97.106.123:8091/login \
    --data-urlencode "_token=$token" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /tmp/hris_payroll_foundation_post.html

code=$(curl -k -s -b "$tmpcookie" -o "$page" -w '%{http_code}' https://31.97.106.123:8091/payroll)
echo "status=$code"
grep -q 'Fondasi Payroll' "$page"
grep -q 'Checklist Kesiapan Payroll' "$page"
grep -q 'Workflow Payroll' "$page"
grep -q 'Proses Payroll' "$page"
echo 'smoke=ok'
