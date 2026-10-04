for app in asiatri_base asiatri_report hris-asiatri ess-asiatri payroll-asiatri; do
  file="/var/www/html/dev/asiatri/$app/application/config/config.php"
  echo "===== $app ====="
  if [ -f "$file" ]; then
    grep -n "sess_cookie_name\\|sess_driver\\|sess_save_path\\|cookie_prefix\\|cookie_domain\\|cookie_path\\|cookie_secure\\|cookie_httponly\\|encryption_key" "$file" || true
  fi
done
