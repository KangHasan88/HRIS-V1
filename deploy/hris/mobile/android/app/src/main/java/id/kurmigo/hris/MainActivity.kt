package id.kurmigo.hris

import android.annotation.SuppressLint
import android.app.Activity
import android.os.Bundle
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import org.json.JSONObject

class MainActivity : Activity() {
    private lateinit var webView: WebView

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        webView = WebView(this)
        setContentView(webView)

        webView.settings.javaScriptEnabled = true
        webView.settings.domStorageEnabled = true
        webView.settings.cacheMode = WebSettings.LOAD_DEFAULT
        webView.settings.mediaPlaybackRequiresUserGesture = false
        webView.webViewClient = WebViewClient()
        webView.webChromeClient = WebChromeClient()
        webView.addJavascriptInterface(KurmigoNativeBridge(), "KurmigoNative")

        val target = intent?.data?.toString() ?: BuildConfig.ESS_URL
        webView.loadUrl(target)
    }

    override fun onBackPressed() {
        if (::webView.isInitialized && webView.canGoBack()) {
            webView.goBack()
            return
        }

        super.onBackPressed()
    }

    inner class KurmigoNativeBridge {
        @JavascriptInterface
        fun postMessage(message: String): String {
            val request = JSONObject(message)
            val capability = request.optString("capability")
            val payload = JSONObject()
                .put("platform", "android")
                .put("appVersion", BuildConfig.VERSION_NAME)
                .put("capability", capability)
                .put("status", "queued")

            return JSONObject()
                .put("ok", true)
                .put("data", payload)
                .toString()
        }
    }
}
