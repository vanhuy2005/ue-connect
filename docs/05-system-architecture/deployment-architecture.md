# Deployment Architecture

Status: Source of truth

Summary: Kiến trúc production UEConnect trên Azure VM, Docker, host Nginx và GitHub Actions.

---

## 1. Overview

UEConnect production is a single deployable Laravel application running in Docker on an Azure VM. The VM owns public network traffic through host Nginx; Docker is bound only to loopback.

```txt
GitHub Actions
  -> SSH
  -> Azure VM (/opt/ueconnect)
  -> Docker container: ueconnect
  -> In-container Nginx
  -> PHP-FPM / Laravel Reverb / Queue Worker
```

Public request path:

```txt
User Browser
  -> https://ueconnect.io.vn
  -> Azure VM host Nginx :443
  -> http://127.0.0.1:10000
  -> Docker Nginx :10000
  -> PHP-FPM :9000 or Reverb :8080
```

---

## 2. Production Nodes

| Node | Responsibility |
|---|---|
| GitHub Actions | CI, Docker build validation, production deploy orchestration |
| Azure VM | Production host, Docker runtime, host Nginx, SSL, `.env.production` |
| Docker container | Laravel app runtime, internal Nginx, PHP-FPM, Reverb, queue worker |
| SQL Server | Primary relational database |
| Redis | Optional high-performance cache and queue backend |
| Cloudinary + R2 | Public media delivery and object storage |
| Mail provider | Transactional email |
| Browser Push | Web push notifications through VAPID keys |
| AI services | Gemini/OpenRouter/Ollama/Qdrant/BGE endpoints when enabled |

---

## 3. Azure VM Runtime

Current production constants:

| Item | Value |
|---|---|
| Domain | `ueconnect.io.vn` |
| App path | `/opt/ueconnect` |
| Env file | `/opt/ueconnect/.env.production` |
| Container | `ueconnect` |
| Image tag | `ueconnect:<commit-sha>` |
| Docker bind | `127.0.0.1:10000:10000` |
| Public ports | Host Nginx owns `80/443` |

The VM keeps deployment state simple:

- Repository clone remains in `/opt/ueconnect`.
- GitHub Actions resets the working tree to the commit being deployed.
- Docker image is built on the VM from the checked-out source.
- Old container is replaced with a new `ueconnect` container.
- Unused Docker images are pruned after successful deploy.

---

## 4. Container Runtime

`Dockerfile` builds a PHP 8.3 FPM image with:

- Laravel dependencies from Composer.
- Node 22 and Vite production assets.
- Microsoft ODBC Driver 18 and SQL Server PHP extensions.
- Nginx.
- Supervisor.

`docker/start.sh` performs startup work:

- Render Nginx config from `docker/nginx.conf.template`.
- Run migrations.
- Import Career Pathway data if missing.
- Clear Laravel caches.
- Fix writable directory permissions.
- Start Supervisord.

Supervisord keeps these processes alive:

```txt
php-fpm
nginx
php artisan reverb:start --host=0.0.0.0 --port=8080
php artisan queue:work --queue=high,default,broadcasts,notifications,media,ai
```

---

## 5. Realtime Routing

Host Nginx proxies all public traffic to Docker on `127.0.0.1:10000`.

Inside the container, Nginx handles:

- `/build/*` as immutable Vite assets.
- normal HTTP routes through Laravel/PHP-FPM.
- WebSocket upgrade requests under `/app/*` to Reverb on `127.0.0.1:8080`.

Production Echo/Reverb env should point clients at the HTTPS domain:

```env
BROADCAST_CONNECTION=reverb
REVERB_HOST=ueconnect.io.vn
REVERB_PORT=443
REVERB_SCHEME=https
```

---

## 6. CI/CD Deployment Boundary

CI validates:

- PHP dependencies install.
- Node dependencies install.
- Laravel app can prepare `.env`.
- Vite assets build.
- PHPUnit test suite passes.
- Docker image builds.

Deploy validates:

- Server repo exists.
- `.env.production` exists.
- Docker image contains Vite manifest/assets.
- New container responds.
- Host Nginx is healthy.
- Docker does not expose public `80/443`.
- HTTPS domain serves the app and CSS asset.
- Reverb WebSocket connection can be established.
- VAPID keys are configured.

---

## 7. Scaling Notes

The current architecture is intentionally simple and VM-centered. It is appropriate for a campus MVP, but these are the pressure points:

- Docker build happens on the production VM, so CPU/RAM spikes during deploy.
- Reverb, queue workers, PHP-FPM, and Nginx share the same container resources.
- Horizontal scaling would require shared session/cache/queue infrastructure and Reverb scaling design.
- Rollback is currently manual by redeploying a known-good commit SHA.
- Host Nginx and SSL renewal are VM responsibilities, not app responsibilities.

Future scaling paths:

- Move image build to a registry and pull images on the VM.
- Split queue workers into separate containers.
- Use managed Redis for cache/queue/broadcast scaling.
- Add a dedicated rollback workflow.
- Add server monitoring and alerting for disk, memory, queue depth, and Reverb health.
