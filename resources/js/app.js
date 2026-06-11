/**
 * Global app entry. Realtime Echo is loaded separately by pages that opt in via
 * resources/js/realtime.js.
 */

import "./identity-camera-upload";
import "./ui";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

import './notification-badge-manager';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }).catch(err => {
            console.error('ServiceWorker registration failed: ', err);
        });
    });
}

// Initialize notification badge
document.addEventListener('DOMContentLoaded', () => {
    window.ueNotificationBadge = new window.NotificationBadgeManager({
        appName: 'UE Connect',
        baseTitle: document.title || 'UE Connect',
        initialUnreadCount: window.ueInitialUnreadCount || 0,
    });
});
