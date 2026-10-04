(function (window) {
    'use strict';

    var pending = {};
    var timeoutMs = 20000;
    var capabilities = [
        'device.info',
        'location.current',
        'camera.selfie',
        'file.download',
        'biometric.verify',
        'notification.badge'
    ];

    function uuid() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID();
        }

        return 'bridge-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    }

    function host() {
        if (window.KurmigoNative && typeof window.KurmigoNative.postMessage === 'function') {
            return 'android';
        }
        if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.kurmigoBridge) {
            return 'ios';
        }
        if (window.ReactNativeWebView && typeof window.ReactNativeWebView.postMessage === 'function') {
            return 'react-native';
        }

        return 'browser';
    }

    function send(message) {
        var target = host();

        if (target === 'android') {
            window.KurmigoNative.postMessage(JSON.stringify(message));
            return true;
        }
        if (target === 'ios') {
            window.webkit.messageHandlers.kurmigoBridge.postMessage(message);
            return true;
        }
        if (target === 'react-native') {
            window.ReactNativeWebView.postMessage(JSON.stringify(message));
            return true;
        }

        return false;
    }

    function settle(message) {
        var item = pending[message.id];
        if (!item) {
            return;
        }

        window.clearTimeout(item.timer);
        delete pending[message.id];

        if (message.ok === false) {
            item.reject(new Error(message.error || 'Native bridge request failed'));
            return;
        }

        item.resolve(message.data || {});
    }

    function request(capability, payload, options) {
        if (capabilities.indexOf(capability) === -1) {
            return Promise.reject(new Error('Capability tidak terdaftar: ' + capability));
        }

        var id = uuid();
        var message = {
            id: id,
            app: 'kurmigo-hris',
            version: '1.0',
            capability: capability,
            payload: payload || {},
            issuedAt: new Date().toISOString()
        };

        return new Promise(function (resolve, reject) {
            pending[id] = {
                resolve: resolve,
                reject: reject,
                timer: window.setTimeout(function () {
                    delete pending[id];
                    reject(new Error('Native bridge timeout'));
                }, (options && options.timeout) || timeoutMs)
            };

            if (!send(message)) {
                window.clearTimeout(pending[id].timer);
                delete pending[id];
                resolve({
                    unsupported: true,
                    host: 'browser',
                    capability: capability,
                    message: 'Native bridge belum tersedia di browser biasa.'
                });
            }
        });
    }

    window.addEventListener('message', function (event) {
        var data = event.data;
        if (typeof data === 'string') {
            try {
                data = JSON.parse(data || '{}');
            } catch (error) {
                return;
            }
        }
        if (data && data.id) {
            settle(data);
        }
    });

    window.addEventListener('kurmigo:bridge-response', function (event) {
        if (event.detail && event.detail.id) {
            settle(event.detail);
        }
    });

    window.KurmigoBridge = {
        host: host,
        capabilities: capabilities.slice(),
        request: request,
        getDeviceInfo: function () {
            return request('device.info');
        },
        getLocation: function (payload) {
            return request('location.current', payload);
        },
        captureSelfie: function (payload) {
            return request('camera.selfie', payload);
        },
        downloadFile: function (payload) {
            return request('file.download', payload);
        },
        verifyBiometric: function (payload) {
            return request('biometric.verify', payload);
        },
        setBadge: function (count) {
            return request('notification.badge', { count: count });
        }
    };

    window.dispatchEvent(new CustomEvent('kurmigo:bridge-ready', {
        detail: { host: host(), capabilities: capabilities.slice() }
    }));
})(window);
