for app in asiatri_base asiatri_report hris-asiatri ess-asiatri payroll-asiatri; do
  file="/var/www/html/dev/asiatri/$app/application/config/config.php"
  echo "===== $app ====="
  if [ -f "$file" ]; then
    grep -nE "\\$config\\[(\"|')(base_url|index_page|sess_cookie_name|sess_save_path|cookie_prefix|cookie_domain|cookie_path|cookie_secure|csrf_cookie_name)(\"|')\\]" "$file" || true
  else
    echo "missing config.php"
  fi
done
echo "===== auth redirect references ====="
grep -RInE "redirect\\(|base_url\\(|site_url\\(|Auth|login|logout" /var/www/html/dev/asiatri/asiatri_report/application/controllers /var/www/html/dev/asiatri/asiatri_report/application/config 2>/dev/null | sed -n "1,180p"
