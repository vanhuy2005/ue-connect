# Redis and Realtime Integration

Status: Source of truth

Summary: Vai trò của Redis trong cache/queue/realtime delivery của UEConnect.

---

## 1. Key Rule

Redis is **not** a replacement for Laravel Reverb.

- Reverb is the WebSocket server that holds client connections and pushes events to browsers.
- Redis is the high-performance backing store for cache and queue workloads.
- Production realtime still requires `BROADCAST_CONNECTION=reverb`.

Current Azure VM production runs Reverb inside the `ueconnect` Docker container through Supervisord:

```txt
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Public clients connect through:

```txt
https://ueconnect.io.vn/app/<REVERB_APP_KEY>
```

Host Nginx proxies public traffic to Docker on `127.0.0.1:10000`; in-container Nginx forwards WebSocket upgrades under `/app/*` to Reverb on `127.0.0.1:8080`.

---

## 2. Local Setup

For local Laragon or Docker development, enable a local Redis service only when you want to test cache/queue behavior.

```env
APP_ENV=local
APP_DEBUG=true

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

Safe local fallback:

```env
CACHE_STORE=file
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
```

---

## 3. Azure VM Production Setup

If Redis is provisioned on the VM or as a managed service, production `.env.production` should use:

```env
APP_ENV=production
APP_DEBUG=false

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

Keep `SESSION_DRIVER=database` unless the Redis service is persistent, monitored, and part of the recovery plan.

If Redis is not available, use database-backed cache/queue:

```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

Avoid `QUEUE_CONNECTION=sync` in production because queue-heavy features can slow web requests.

---

## 4. Worker Runtime

The Docker container runs one queue worker through `docker/supervisord.conf`:

```bash
php /var/www/html/artisan queue:work \
  --queue=high,default,broadcasts,notifications,media,ai \
  --sleep=1 \
  --tries=3 \
  --timeout=90 \
  --max-jobs=1000
```

If queue volume grows, split workers into dedicated containers or services rather than increasing web container complexity indefinitely.

---

## 5. How To Verify

From the Azure VM:

```bash
docker exec -w /var/www/html ueconnect php artisan about
docker exec -w /var/www/html ueconnect php artisan queue:failed
```

If Redis is enabled:

```bash
docker exec -w /var/www/html ueconnect php artisan tinker --execute='echo Illuminate\Support\Facades\Redis::connection()->ping();'
docker exec -w /var/www/html ueconnect php artisan tinker --execute='Illuminate\Support\Facades\Cache::put("redis_test", "ok", 60); echo Illuminate\Support\Facades\Cache::get("redis_test");'
```

Realtime is verified during deploy by opening a real WebSocket connection to Reverb from inside the running container.

---

## 6. Rollback

If Redis fails in production:

1. Update `/opt/ueconnect/.env.production`:

```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

2. Restart the app via GitHub Actions `Deploy Azure VM` or manually recreate the container.
3. Confirm queue and app health.

Do not disable Reverb unless realtime features are intentionally being degraded during an incident.
