class NotificationBadgeManager {
    constructor(options = {}) {
        this.appName = options.appName || 'UE Connect';
        this.baseTitle = options.baseTitle || document.title || this.appName;
        this.unreadCount = Number(options.initialUnreadCount || 0);
        this.maxVisibleCount = options.maxVisibleCount || 99;

        this.originalFaviconHref = this.getCurrentFaviconHref();

        this.update();
        this.bindVisibilityHandler();
    }

    setCount(count) {
        this.unreadCount = Math.max(0, Number(count || 0));
        this.update();
    }

    increment(amount = 1) {
        this.setCount(this.unreadCount + amount);
    }

    decrement(amount = 1) {
        this.setCount(this.unreadCount - amount);
    }

    clear() {
        this.setCount(0);
    }

    getDisplayCount() {
        if (this.unreadCount > this.maxVisibleCount) {
            return `${this.maxVisibleCount}+`;
        }

        return String(this.unreadCount);
    }

    update() {
        this.updateTitle();
        this.updateAppBadge();
        this.updateFaviconBadge();
    }

    updateTitle() {
        if (this.unreadCount > 0) {
            document.title = `(${this.getDisplayCount()}) ${this.baseTitle}`;
            return;
        }

        document.title = this.baseTitle;
    }

    async updateAppBadge() {
        try {
            if (!('setAppBadge' in navigator) || !('clearAppBadge' in navigator)) {
                return;
            }

            if (this.unreadCount > 0) {
                await navigator.setAppBadge(this.unreadCount);
            } else {
                await navigator.clearAppBadge();
            }
        } catch (error) {
            console.debug('App badge API unavailable or blocked.', error);
        }
    }

    getCurrentFaviconHref() {
        const favicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
        return favicon?.href || '/favicon.ico';
    }

    setFaviconHref(href) {
        let favicon = document.querySelector('link[rel="icon"]');

        if (!favicon) {
            favicon = document.createElement('link');
            favicon.rel = 'icon';
            document.head.appendChild(favicon);
        }

        favicon.href = href;
    }

    updateFaviconBadge() {
        if (this.unreadCount <= 0) {
            this.setFaviconHref(this.originalFaviconHref);
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = this.originalFaviconHref;

        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = 64;
            canvas.height = 64;

            const ctx = canvas.getContext('2d');

            ctx.drawImage(img, 0, 0, 64, 64);

            // badge circle
            ctx.beginPath();
            ctx.arc(46, 18, 16, 0, Math.PI * 2);
            ctx.fillStyle = '#ef4444';
            ctx.fill();

            // badge text
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 18px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const text = this.unreadCount > 9 ? '9+' : String(this.unreadCount);
            ctx.fillText(text, 46, 18);

            this.setFaviconHref(canvas.toDataURL('image/png'));
        };

        img.onerror = () => {
            // CORS or error, skip.
        };
    }

    bindVisibilityHandler() {
        document.addEventListener('visibilitychange', () => {
            this.update();
        });
    }
}

window.NotificationBadgeManager = NotificationBadgeManager;
