// Track PWA events simply via console for now, can be hooked to analytics later
window.trackPwaEvent = function (eventName, data = {}) {
    const payload = {
        event: eventName,
        platform: getPlatform(),
        browser: getBrowser(),
        isStandalone: window.matchMedia('(display-mode: standalone)').matches,
        timestamp: new Date().toISOString(),
        ...data
    };
    
    // In a real app, you might do: fetch('/api/track', { method: 'POST', body: JSON.stringify(payload) })
    console.info('[PWA Tracking]', payload);
};

function getPlatform() {
    const ua = navigator.userAgent || navigator.vendor || window.opera;
    if (/android/i.test(ua)) return 'android';
    if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) return 'ios';
    return 'desktop';
}

function getBrowser() {
    const ua = navigator.userAgent;
    if (ua.indexOf('Edg') > -1) return 'edge';
    if (ua.indexOf('Chrome') > -1) return 'chrome';
    if (ua.indexOf('Safari') > -1) return 'safari';
    if (ua.indexOf('Firefox') > -1) return 'firefox';
    return 'unknown';
}

function urlBase64ToUint8Array(base64String) {
    if (!base64String) return null;
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function buffersMatch(left, right) {
    if (!left || !right || left.byteLength !== right.byteLength) {
        return false;
    }

    const leftView = new Uint8Array(left);
    const rightView = new Uint8Array(right);

    return leftView.every((value, index) => value === rightView[index]);
}

async function sendPushSubscription(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        return {
            success: false,
            message: 'Không tìm thấy CSRF token để đăng ký thông báo đẩy.'
        };
    }

    const response = await fetch('/app/notifications/push-subscriptions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(subscription)
    });

    if (!response.ok) {
        return {
            success: false,
            message: 'Máy chủ chưa lưu được đăng ký thông báo đẩy.'
        };
    }

    return { success: true };
}

async function revokePushSubscription(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken || !subscription?.endpoint) {
        return { success: false };
    }

    const response = await fetch('/app/notifications/push-subscriptions', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ endpoint: subscription.endpoint })
    });

    return { success: response.ok };
}

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then((registration) => {
            console.log('SW registered: ', registration);
            
            // Handle updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // Notify user about new version here if needed
                        console.log('New version available. Refresh to update.');
                        // In a real app: show a toast "Đã có phiên bản mới, tải lại để cập nhật"
                        // Then: newWorker.postMessage({ type: 'SKIP_WAITING' }); window.location.reload();
                    }
                });
            });
        }).catch((registrationError) => {
            console.log('SW registration failed: ', registrationError);
        });
    });
}

// Alpine.js store for PWA state
document.addEventListener('alpine:init', () => {
    Alpine.store('pwa', {
        deferredPrompt: null,
        isStandalone: window.matchMedia('(display-mode: standalone)').matches,
        showBanner: false,

        init() {
            // Listen for beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent the mini-infobar from appearing on mobile
                e.preventDefault();
                // Stash the event so it can be triggered later.
                this.deferredPrompt = e;
                
                // Show banner if not dismissed and not in standalone
                this.checkBannerEligibility();
            });

            // Listen for successful install
            window.addEventListener('appinstalled', (e) => {
                this.deferredPrompt = null;
                this.showBanner = false;
                window.trackPwaEvent('pwa_app_installed');
            });
            
            // Check display mode changes
            window.matchMedia('(display-mode: standalone)').addEventListener('change', (evt) => {
                this.isStandalone = evt.matches;
                if (this.isStandalone) {
                    window.trackPwaEvent('pwa_opened_standalone');
                    this.showBanner = false;
                }
            });

            // Initial tracking if opened in standalone
            if (this.isStandalone) {
                window.trackPwaEvent('pwa_opened_standalone');
            } else {
                this.checkBannerEligibility();
            }

            // Re-subscribe automatically if permission already granted
            if ('Notification' in window && Notification.permission === 'granted') {
                this.subscribeToPushNotifications();
            }
        },

        checkBannerEligibility() {
            if (this.isStandalone) return;
            
            const dismissedAt = localStorage.getItem('ue_pwa_install_dismissed_at');
            const neverShow = localStorage.getItem('ue_pwa_install_never_show');
            
            if (neverShow === 'true') return;
            
            if (dismissedAt) {
                const daysSinceDismiss = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60 * 24);
                // Don't show again for 7 days
                if (daysSinceDismiss < 7) return;
            }
            
            // Only show on mobile
            const platform = getPlatform();
            if (platform === 'android' || platform === 'ios') {
                this.showBanner = true;
                window.trackPwaEvent('pwa_banner_shown');
            }
        },

        async install() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                
                if (outcome === 'accepted') {
                    window.trackPwaEvent('pwa_install_accepted');
                } else {
                    window.trackPwaEvent('pwa_install_dismissed');
                }
                
                this.deferredPrompt = null;
                this.showBanner = false;
            } else {
                // If no prompt is available (e.g. iOS or not met criteria), redirect to install page
                window.location.href = '/install';
            }
        },

        dismissBanner(neverShow = false) {
            this.showBanner = false;
            window.trackPwaEvent('pwa_banner_dismissed', { neverShow });
            
            if (neverShow) {
                localStorage.setItem('ue_pwa_install_never_show', 'true');
            } else {
                localStorage.setItem('ue_pwa_install_dismissed_at', Date.now().toString());
            }
        },

        async subscribeToPushNotifications() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.warn('Push messaging is not supported.');
                return {
                    success: false,
                    message: 'Trình duyệt này không hỗ trợ thông báo đẩy.'
                };
            }

            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.warn('Notification permission denied.');
                    return {
                        success: false,
                        message: 'Trình duyệt chưa cấp quyền gửi thông báo.'
                    };
                }

                const registration = await navigator.serviceWorker.ready;
                
                const vapidPublicKeyMeta = document.querySelector('meta[name="vapid-public-key"]');
                if (!vapidPublicKeyMeta) {
                    console.error('VAPID public key meta tag not found.');
                    return {
                        success: false,
                        message: 'Thiếu cấu hình VAPID public key.'
                    };
                }
                
                const vapidPublicKey = vapidPublicKeyMeta.getAttribute('content');
                const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                let subscription = await registration.pushManager.getSubscription();
                const currentApplicationServerKey = subscription?.options?.applicationServerKey;

                if (subscription && currentApplicationServerKey && !buffersMatch(currentApplicationServerKey, convertedVapidKey)) {
                    await subscription.unsubscribe();
                    subscription = null;
                }

                if (!subscription) {
                    try {
                        subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: convertedVapidKey
                        });
                    } catch (error) {
                        if (error.name === 'InvalidStateError') {
                            const staleSubscription = await registration.pushManager.getSubscription();

                            if (staleSubscription) {
                                await staleSubscription.unsubscribe();
                            }

                            subscription = await registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: convertedVapidKey
                            });
                        } else {
                            throw error;
                        }
                    }
                }

                const result = await sendPushSubscription(subscription);
                if (!result.success) {
                    return result;
                }
                
                window.trackPwaEvent('pwa_push_subscribed');

                return { success: true };
            } catch (error) {
                console.error('Error subscribing to push notifications', error);

                return {
                    success: false,
                    message: 'Không thể đăng ký thông báo đẩy. Vui lòng thử lại.'
                };
            }
        },

        async unsubscribeFromPushNotifications() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                return { success: true };
            }

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (!subscription) {
                    return { success: true };
                }

                await revokePushSubscription(subscription);
                await subscription.unsubscribe();
                window.trackPwaEvent('pwa_push_unsubscribed');

                return { success: true };
            } catch (error) {
                console.error('Error unsubscribing from push notifications', error);

                return {
                    success: false,
                    message: 'Không thể tắt thông báo đẩy trên trình duyệt.'
                };
            }
        }
    });
});
