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

import './pwa';

// Initialize notification badge
document.addEventListener('DOMContentLoaded', () => {
    window.ueNotificationBadge = new window.NotificationBadgeManager({
        appName: 'UE Connect',
        baseTitle: document.title || 'UE Connect',
        initialUnreadCount: window.ueInitialUnreadCount || 0,
    });
});
