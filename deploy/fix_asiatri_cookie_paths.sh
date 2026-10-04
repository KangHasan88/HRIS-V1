set -eu
ts=$(date +%Y%m%d-%H%M%S)

fix_app() {
  app="$1"
  cookie="$2"
  path="$3"
  file="/var/www/html/dev/asiatri/$app/application/config/config.php"
  if [ ! -f "$file" ]; then
    echo "missing:$file"
    return
  fi

  cp "$file" "$file.bak-cookie-loop-$ts"
  sed -i "s#^\$config\\['sess_cookie_name'\\].*#\$config['sess_cookie_name'] = '$cookie';#" "$file"
  sed -i "s#^\$config\\['cookie_path'\\].*#\$config['cookie_path'] = '$path';#" "$file"
  echo "fixed:$app:$cookie:$path"
}

fix_app asiatri_base ci_asiatri_base /dev/asiatri/asiatri_base/
fix_app asiatri_report ci_asiatri_report /dev/asiatri/asiatri_report/
fix_app hris-asiatri ci_asiatri_hris /dev/asiatri/hris-asiatri/
fix_app ess-asiatri ci_asiatri_ess /dev/asiatri/ess-asiatri/
fix_app payroll-asiatri ci_asiatri_payroll /dev/asiatri/payroll-asiatri/

echo "backup_ts:$ts"
