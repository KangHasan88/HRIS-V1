#!/usr/bin/env bash
set -euo pipefail

ess_cookie="/tmp/hris-ess-helpdesk-cookie"
admin_cookie="/tmp/hris-admin-helpdesk-cookie"
ticket="HRD-HELP-SMOKE-$(date +%H%M%S)"

rm -f "$ess_cookie" "$admin_cookie" /tmp/hris-helpdesk-*.html

ess_token="$(curl -ks -c "$ess_cookie" https://31.97.106.123:8092/login \
    | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' \
    | head -1)"

curl -ks -b "$ess_cookie" -c "$ess_cookie" -o /dev/null -w "ess_login %{http_code}\n" \
    --data-urlencode "_token=${ess_token}" \
    --data-urlencode "email=dimas@kurmigo.id" \
    --data-urlencode "password=password123" \
    https://31.97.106.123:8092/login

curl -ks -b "$ess_cookie" -o /tmp/hris-helpdesk-ess.html -w "ess_helpdesk_get %{http_code}\n" \
    https://31.97.106.123:8092/ess/helpdesk

post_token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hris-helpdesk-ess.html | head -1)"

curl -ks -b "$ess_cookie" -c "$ess_cookie" -o /dev/null -w "ess_helpdesk_post %{http_code}\n" \
    --data-urlencode "_token=${post_token}" \
    --data-urlencode "ticket_number=${ticket}" \
    --data-urlencode "category=akses_ess" \
    --data-urlencode "priority=normal" \
    --data-urlencode "subject=Smoke test helpdesk ESS" \
    --data-urlencode "description=Validasi smoke tiket helpdesk dari ESS." \
    --data-urlencode "attachment_reference=camera://helpdesk/${ticket}.jpg" \
    https://31.97.106.123:8092/ess/helpdesk

curl -ks -b "$ess_cookie" -o /tmp/hris-helpdesk-ess-after.html -w "ess_helpdesk_after %{http_code}\n" \
    https://31.97.106.123:8092/ess/helpdesk

admin_token="$(curl -ks -c "$admin_cookie" https://31.97.106.123:8091/login \
    | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' \
    | head -1)"

curl -ks -b "$admin_cookie" -c "$admin_cookie" -o /dev/null -w "admin_login %{http_code}\n" \
    --data-urlencode "_token=${admin_token}" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    https://31.97.106.123:8091/login

curl -ks -b "$admin_cookie" -o /tmp/hris-helpdesk-admin.html -w "admin_helpdesk_get %{http_code}\n" \
    https://31.97.106.123:8091/employees/helpdesk

grep -q "$ticket" /tmp/hris-helpdesk-ess-after.html
grep -q "$ticket" /tmp/hris-helpdesk-admin.html
echo "ticket ${ticket}"
