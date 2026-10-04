#!/usr/bin/env bash
set -euo pipefail

cookie="/tmp/hris-wa-gateway-cookie"
code="WA-SMOKE-$(date +%H%M%S)"
message="WA-MSG-SMOKE-$(date +%H%M%S)"
rm -f "$cookie" /tmp/hris-wa-gateway*.html

token="$(curl -ks -c "$cookie" https://31.97.106.123:8091/login \
    | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' \
    | head -1)"

curl -ks -b "$cookie" -c "$cookie" -o /dev/null -w "login %{http_code}\n" \
    --data-urlencode "_token=${token}" \
    --data-urlencode "email=admin@kurmigo.id" \
    --data-urlencode "password=password123" \
    https://31.97.106.123:8091/login

curl -ks -b "$cookie" -o /tmp/hris-wa-gateway.html -w "gateway_get %{http_code}\n" \
    https://31.97.106.123:8091/integrations/whatsapp-gateway

form_token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hris-wa-gateway.html | head -1)"

curl -ks -b "$cookie" -c "$cookie" -o /dev/null -w "gateway_post %{http_code}\n" \
    --data-urlencode "_token=${form_token}" \
    --data-urlencode "gateway_code=${code}" \
    --data-urlencode "gateway_name=Smoke WhatsApp Gateway" \
    --data-urlencode "provider=generic_webhook" \
    --data-urlencode "connection_type=webhook_queue" \
    --data-urlencode "endpoint_url=https://wa-gateway.example/send" \
    --data-urlencode "sender_id=6281200000000" \
    --data-urlencode "auth_mode=api_key" \
    --data-urlencode "status=active" \
    --data-urlencode "enabled_events[]=approval_request" \
    --data-urlencode "enabled_events[]=payslip_published" \
    --data-urlencode "masked_token=wa_xxx_****" \
    https://31.97.106.123:8091/integrations/whatsapp-gateway/settings

curl -ks -b "$cookie" -o /tmp/hris-wa-gateway-after.html -w "gateway_after %{http_code}\n" \
    https://31.97.106.123:8091/integrations/whatsapp-gateway

gateway_id="$(grep -o "value=\"[0-9]*\">${code}" /tmp/hris-wa-gateway-after.html | sed -n 's/value="\([0-9]*\)".*/\1/p' | head -1)"
if [ -z "$gateway_id" ]; then
    echo "gateway id not found"
    exit 7
fi

queue_token="$(sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' /tmp/hris-wa-gateway-after.html | head -1)"

curl -ks -b "$cookie" -c "$cookie" -o /dev/null -w "message_post %{http_code}\n" \
    --data-urlencode "_token=${queue_token}" \
    --data-urlencode "whatsapp_gateway_setting_id=${gateway_id}" \
    --data-urlencode "message_number=${message}" \
    --data-urlencode "event_key=payslip_published" \
    --data-urlencode "recipient_name=Dimas Wirawan" \
    --data-urlencode "recipient_phone=6281234567890" \
    --data-urlencode "message_body=Smoke WhatsApp gateway HRIS." \
    --data-urlencode "priority=normal" \
    https://31.97.106.123:8091/integrations/whatsapp-gateway/messages

curl -ks -b "$cookie" -o /tmp/hris-wa-gateway-final.html -w "gateway_final %{http_code}\n" \
    https://31.97.106.123:8091/integrations/whatsapp-gateway

grep -q "$code" /tmp/hris-wa-gateway-final.html
grep -q "$message" /tmp/hris-wa-gateway-final.html
echo "gateway ${code}"
echo "message ${message}"
