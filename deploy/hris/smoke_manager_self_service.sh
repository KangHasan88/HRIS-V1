#!/usr/bin/env bash
set -euo pipefail

tmpcookie=/tmp/hris_manager_mss_cookie.txt
login=/tmp/hris_manager_mss_login.html
page=/tmp/hris_manager_mss_page.html

curl -k -s -c "$tmpcookie" https://31.97.106.123:8091/login -o "$login"
token=$(grep -o 'name="_token" value="[^"]*' "$login" | sed 's/.*value="//')

curl -k -s -b "$tmpcookie" -c "$tmpcookie" \
    -X POST https://31.97.106.123:8091/login \
    --data-urlencode "_token=$token" \
    --data-urlencode "email=bima@kurmigo.id" \
    --data-urlencode "password=password123" \
    -o /tmp/hris_manager_mss_post.html

code=$(curl -k -s -b "$tmpcookie" -o "$page" -w '%{http_code}' https://31.97.106.123:8091/manager/self-service)
echo "status=$code"
grep -q 'Manager Self Service' "$page"
grep -q 'Antrian Keputusan' "$page"
grep -q 'Beranda MSS' "$page"
grep -q 'Approval Inbox' "$page"
echo 'smoke=ok'
