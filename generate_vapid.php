<?php

use Minishlink\WebPush\Vapid;

require 'vendor/autoload.php';
$keys = Vapid::createVapidKeys();
echo 'VAPID_PUBLIC_KEY='.$keys['publicKey'].PHP_EOL;
echo 'VAPID_PRIVATE_KEY='.$keys['privateKey'].PHP_EOL;
