<?php

return [
    /**
     * These are the keys for authentication (VAPID).
     * These keys must be safely stored in your environment.
     */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@ue-connect.test'), // Can be a mailto: or a URL.
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
];
