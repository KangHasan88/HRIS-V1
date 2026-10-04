#!/usr/bin/env bash
set -euo pipefail

cd /var/www/kurmigo-hris
rm -f /tmp/hris-admin-cookie /tmp/hris-ess-cookie

admin_token="$(curl -ks -c /tmp/hris-admin-cookie https://31.97.106.123:8091/login | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"
curl -ks -b /tmp/hris-admin-cookie -c /tmp/hris-admin-cookie -o /dev/null -w "admin_login %{http_code}\n" \
  --data-urlencode "_token=${admin_token}" \
  --data-urlencode "email=ayu@kurmigo.id" \
  --data-urlencode "password=password123" \
  https://31.97.106.123:8091/login
curl -ks -b /tmp/hris-admin-cookie -o /dev/null -w "admin_profile_updates %{http_code}\n" \
  https://31.97.106.123:8091/employees/profile-updates

ess_token="$(curl -ks -c /tmp/hris-ess-cookie https://31.97.106.123:8092/login | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)"
curl -ks -b /tmp/hris-ess-cookie -c /tmp/hris-ess-cookie -o /dev/null -w "ess_login %{http_code}\n" \
  --data-urlencode "_token=${ess_token}" \
  --data-urlencode "email=dimas@kurmigo.id" \
  --data-urlencode "password=password123" \
  https://31.97.106.123:8092/login
curl -ks -b /tmp/hris-ess-cookie -o /dev/null -w "ess_profile_updates %{http_code}\n" \
  https://31.97.106.123:8092/ess/profile-updates
