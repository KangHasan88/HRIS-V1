for app in asiatri_base asiatri_report hris-asiatri ess-asiatri payroll-asiatri; do
  echo "===== $app is_session refs ====="
  grep -RIn "function is_session\\|is_session()" /var/www/html/dev/asiatri/$app/application 2>/dev/null | sed -n "1,120p"
done
echo "===== asiatri_report helper snippets ====="
grep -RIl "function is_session" /var/www/html/dev/asiatri/asiatri_report/application 2>/dev/null | while read file; do
  echo "--- $file"
  sed -n "1,180p" "$file"
done
