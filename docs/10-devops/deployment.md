# Production Deployment Runbook

Status: Source of truth

Summary: Runbook deploy UEConnect production trên Azure VM bằng GitHub Actions, Docker và host Nginx.

---

## 1. Deployment Model

Production primary hiện là **Azure VM VPS**, không phải Render.

```txt
GitHub main
  -> CI workflow
  -> Deploy Azure VM workflow
  -> SSH to Azure VM
  -> git reset --hard <commit-sha>
  -> docker build
  -> restart ueconnect container
  -> smoke checks
```

Runtime shape:

| Item | Value |
|---|---|
| VM app path | `/opt/ueconnect` |
| Env file | `/opt/ueconnect/.env.production` |
| Container | `ueconnect` |
| Image tag | `ueconnect:<commit-sha>` |
| App port | `10000` |
| Docker bind | `127.0.0.1:10000:10000` |
| Public domain | `ueconnect.io.vn` |
| Public proxy | Host Nginx owns `80/443` |

First-time VM setup lives in [`azure-vm-setup.md`](azure-vm-setup.md).

---

## 2. Automated Deploy Flow

Workflow: `.github/workflows/deploy-azure-vm.yml`

Triggers:

- Automatically after the `CI` workflow completes successfully on `main`.
- Manually via `workflow_dispatch`.

The workflow deploys over SSH using:

- `AZURE_VM_HOST`
- `AZURE_VM_USER`
- `AZURE_VM_SSH_KEY`

The workflow sets:

- `APP_DIR=/opt/ueconnect`
- `APP_PORT=10000`
- `DOMAIN=ueconnect.io.vn`
- `GIT_REF=<workflow_run.head_sha or github.sha>`

---

## 3. What The Deploy Script Does

On the Azure VM, the workflow:

1. Verifies `/opt/ueconnect/.git` exists.
2. Fetches `origin main`.
3. Resets the working tree to the exact commit SHA.
4. Requires `.env.production`.
5. Builds `ueconnect:<commit-sha>` from `Dockerfile`.
6. Verifies Vite build artifacts inside the new image.
7. Removes the old `ueconnect` container.
8. Starts the new container with:

```bash
docker run -d \
  --name ueconnect \
  --restart unless-stopped \
  --env-file .env.production \
  -e PORT=10000 \
  -p 127.0.0.1:10000:10000 \
  ueconnect:<commit-sha>
```

9. Waits until `http://127.0.0.1:10000` responds.
10. Verifies the container is running.
11. Verifies Vite manifest, CSS, and JS assets.
12. Checks `sudo nginx -t` and host Nginx service status.
13. Fails if Docker owns public `80/443`.
14. Verifies HTTPS through host Nginx using `ueconnect.io.vn`.
15. Tests Laravel Reverb with a real WebSocket client inside the container.
16. Checks push routes and VAPID key configuration.
17. Prunes unused Docker images.

---

## 4. Container Startup

`docker/start.sh` runs inside the container:

1. Renders Nginx config from `docker/nginx.conf.template` using `$PORT`.
2. Runs `php artisan migrate --force`.
3. Imports Career Pathway data only when `CareerProgram` is empty.
4. Runs `php artisan optimize:clear`.
5. Fixes `storage` and `bootstrap/cache` permissions.
6. Starts Supervisord.

Supervisord starts:

- `php-fpm`
- `nginx -g "daemon off;"`
- `php artisan reverb:start --host=0.0.0.0 --port=8080`
- `php artisan queue:work --queue=high,default,broadcasts,notifications,media,ai --sleep=1 --tries=3 --timeout=90 --max-jobs=1000`

---

## 5. Manual Verification Commands

Run on the Azure VM:

```bash
cd /opt/ueconnect
docker ps --filter "name=ueconnect"
curl -fsSI http://127.0.0.1:10000
sudo nginx -t
sudo systemctl is-active --quiet nginx
curl -fsSIk --resolve ueconnect.io.vn:443:127.0.0.1 https://ueconnect.io.vn >/dev/null
docker exec -w /var/www/html ueconnect php artisan route:list
```

Check assets:

```bash
docker exec -w /var/www/html ueconnect test -f public/build/manifest.json
docker exec -w /var/www/html ueconnect sh -lc 'find public/build/assets -type f -name "*.css" | grep -q .'
docker exec -w /var/www/html ueconnect sh -lc 'find public/build/assets -type f -name "*.js" | grep -q .'
```

Check VAPID config:

```bash
docker exec -w /var/www/html ueconnect php artisan tinker --execute='if (empty(config("webpush.vapid.public_key")) || empty(config("webpush.vapid.private_key"))) { echo "Missing VAPID keys!\n"; exit(1); } echo "VAPID Keys configuration OK\n";'
```

---

## 6. Troubleshooting

### `.env.production is missing`

Create `/opt/ueconnect/.env.production` on the VM. Do not commit it.

### Docker build passes but app does not respond

Check logs:

```bash
docker logs --tail=200 ueconnect
docker exec -w /var/www/html ueconnect php artisan about
```

Common causes:

- Invalid `APP_KEY`.
- Database connection failure.
- Migration failure.
- Missing required extension or external service credential.

### CSS or JS assets missing

The deploy workflow already checks manifest, CSS, and JS. If it fails, inspect:

```bash
docker run --rm ueconnect:<commit-sha> sh -lc 'ls -la public/build && find public/build/assets -maxdepth 1 -type f'
```

### Host Nginx conflict

Docker must bind only to loopback `127.0.0.1:10000`. If Docker owns public `80/443`, stop the wrong container and restore host Nginx ownership.

```bash
sudo ss -tulpn | grep -E ':80|:443|:10000'
sudo nginx -t
sudo systemctl reload nginx
```

### WebSocket timeout

Verify:

- `BROADCAST_CONNECTION=reverb`
- `REVERB_APP_KEY` is present in `.env.production`
- `REVERB_HOST=ueconnect.io.vn`
- `REVERB_PORT=443`
- `REVERB_SCHEME=https`
- Host Nginx forwards `Upgrade` and `Connection` headers.
- In-container Nginx routes websocket `/app/*` requests to Reverb on `127.0.0.1:8080`.

### Rollback

There is no dedicated rollback workflow yet. Practical rollback is:

```bash
cd /opt/ueconnect
git fetch --prune origin main
git reset --hard <known-good-sha>
docker build --tag ueconnect:<known-good-sha> .
docker rm -f ueconnect
docker run -d --name ueconnect --restart unless-stopped --env-file .env.production -e PORT=10000 -p 127.0.0.1:10000:10000 ueconnect:<known-good-sha>
```

Then rerun the smoke checks above.
