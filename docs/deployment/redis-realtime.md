# Redis & Realtime Integration

## Purpose
This document explains the role of Redis within UEConnect's real-time architecture.

**Important Note:** Redis is **not** a replacement for Laravel Reverb.
- Reverb is the WebSocket server responsible for maintaining active client connections and pushing data to the frontend. It requires `BROADCAST_CONNECTION=reverb`.
- Redis is used as the high-performance backing store for `CACHE_STORE` and `QUEUE_CONNECTION` to prevent database bottlenecks when broadcasting many events concurrently.

## Local Setup (Docker / Laragon)
If you are running locally via Docker or Laragon, you can start a local Redis server.
For Laragon, enable the Redis service in the Laragon control panel.

Your local `.env` should look like this:
```env
APP_ENV=local
APP_DEBUG=true

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

REDIS_CLIENT=predis
REDIS_URL=redis://127.0.0.1:6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

## Production Setup (Render)
On Render, you should create a Redis Key-Value store in the same region as your Web Service.

Your production `.env` should look like this:
```env
APP_ENV=production
APP_DEBUG=false

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

REDIS_CLIENT=predis
REDIS_URL=<render-internal-redis-url>
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90
```

### Worker Commands
If Render supports multiple background workers, configure them as:

**Web Service:**
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

**Background Worker Service:**
```bash
php artisan queue:work redis --queue=high,default,broadcasts,notifications,media,ai --sleep=1 --tries=3 --timeout=90 --max-jobs=1000
```

**Reverb Service:**
```bash
php artisan reverb:start --host=0.0.0.0 --port=$PORT
```

*(Note: If using the free tier or a single service, `docker/start.sh` acts as a demo-only fallback starting all three via `nohup` in the background).*

## How to Verify
Run the safe validation command:
```bash
php artisan ueconnect:realtime-health
```

You can also use Tinker to test the connection manually:
```bash
php artisan tinker
> Redis::connection()->ping();
> Cache::put('redis_test', 'ok', 60);
> Cache::get('redis_test');
```

## Rollback Plan
If Redis fails in production, you can fallback to the database safely:
1. Update `.env`:
```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```
2. Run cache clearance commands:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```
3. Restart your Render services.

## Known Limitations
- Free tier Redis on Render (Key-Value) does not guarantee data persistence on restart. That's why `SESSION_DRIVER` remains `database`.
