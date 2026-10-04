# Kurmigo HRIS iOS WKWebView Shell

Shell iOS ini dibuat sebagai pembungkus full WKWebView untuk HRIS/ESS.

Target dev:
- Admin: `https://31.97.106.123:8091`
- ESS entry: `https://31.97.106.123:8092/ess/request-center-entry`
- Bridge JS: `window.webkit.messageHandlers.kurmigoNative.postMessage(message)`

Catatan implementasi:
- Default screen membuka ESS entry agar session user mobile terpisah dari admin.
- Native bridge menggunakan `WKScriptMessageHandler` dengan nama `kurmigoNative`.
- Permission text kamera, lokasi, dan Face ID sudah disiapkan di `Info.plist`.
- Project Xcode/signing, push notification, file picker, dan biometric native detail masuk backlog mobile berikutnya.
