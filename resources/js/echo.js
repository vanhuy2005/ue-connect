import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const metaKey = document.querySelector('meta[name="reverb-app-key"]')?.content;
const metaHost = document.querySelector('meta[name="reverb-host"]')?.content;
const metaPort = document.querySelector('meta[name="reverb-port"]')?.content;
const metaScheme = document.querySelector('meta[name="reverb-scheme"]')?.content;

const appKey = metaKey || import.meta.env.VITE_REVERB_APP_KEY;
const host = metaHost || import.meta.env.VITE_REVERB_HOST;
const port = metaPort || import.meta.env.VITE_REVERB_PORT || 80;
const wssPort = metaPort || import.meta.env.VITE_REVERB_PORT || 443;
const scheme = metaScheme || import.meta.env.VITE_REVERB_SCHEME || 'https';

if (appKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: appKey,
        wsHost: host,
        wsPort: port,
        wssPort: wssPort,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
