# Kurmigo HRIS Android WebView Shell

Shell Android ini dibuat sebagai pembungkus full WebView untuk HRIS/ESS.

Target dev:
- Admin: `https://31.97.106.123:8091`
- ESS entry: `https://31.97.106.123:8092/ess/request-center-entry`
- Bridge JS: `KurmigoNative.postMessage(message)`

Catatan implementasi:
- Default screen membuka ESS entry agar user mobile mendapat login/session sendiri.
- Native bridge disiapkan untuk capability device, lokasi, kamera/selfie, download, biometric, dan badge notifikasi.
- Integrasi permission runtime, file chooser, push notification, dan signing release masuk ke backlog mobile berikutnya.
