# Azure VM Setup

Status: Source of truth

Summary: Cách dựng Azure VM production host cho UEConnect trước khi GitHub Actions có thể deploy.

---

## 1. Production Shape

UEConnect production hiện chạy trên một Azure VM dùng Docker để đóng gói ứng dụng, nhưng **host Nginx** vẫn là reverse proxy public duy nhất.

| Item | Current value |
|---|---|
| Domain | `ueconnect.io.vn` |
| App directory | `/opt/ueconnect` |
| Env file | `/opt/ueconnect/.env.production` |
| Container name | `ueconnect` |
| Image tag pattern | `ueconnect:<commit-sha>` |
| App port inside container | `10000` |
| Host Docker bind | `127.0.0.1:10000:10000` |
| Public ports | Host Nginx owns `80` and `443` |
| Deploy workflow | `.github/workflows/deploy-azure-vm.yml` |

Inside the container, Supervisord runs:

- Nginx on `${PORT}`, default `10000`.
- PHP-FPM on `127.0.0.1:9000`.
- Laravel Reverb on `0.0.0.0:8080`.
- Queue worker for `high,default,broadcasts,notifications,media,ai`.

Public traffic flow:

```txt
Browser
  -> https://ueconnect.io.vn
  -> Azure VM host Nginx :443
  -> http://127.0.0.1:10000
  -> Docker container Nginx
  -> Laravel PHP-FPM or Reverb
```

---

## 2. Azure VM Requirements

Use an Ubuntu LTS VM with enough CPU/RAM for Docker build, PHP-FPM, Reverb, queue work, and the Laravel app. Keep the VM in an Azure region close to users and database services.

Install on the VM:

```bash
sudo apt update
sudo apt install -y git curl ca-certificates nginx
```

Install Docker using the official Docker instructions for Ubuntu, then verify:

```bash
docker --version
sudo systemctl status docker
```

The deploy user used by GitHub Actions must be able to:

- SSH into the VM.
- Read and write `/opt/ueconnect`.
- Run Docker commands.
- Run `sudo nginx -t`, `sudo systemctl is-active nginx`, and socket checks used by the deploy workflow.

---

## 3. Domain and Host Nginx

Point DNS for `ueconnect.io.vn` to the Azure VM public IP.

Host Nginx should terminate public HTTP/HTTPS and proxy to Docker on loopback. Docker must not bind public `80` or `443`.

Example site shape:

```nginx
server {
    listen 80;
    server_name ueconnect.io.vn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ueconnect.io.vn;

    ssl_certificate /etc/letsencrypt/live/ueconnect.io.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ueconnect.io.vn/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:10000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }
}
```

After editing:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Use Certbot or the chosen certificate automation for SSL. Keep certificate renewal outside the application container.

---

## 4. Repository Bootstrap

Clone the repository into the production path:

```bash
sudo mkdir -p /opt/ueconnect
sudo chown -R "$USER":"$USER" /opt/ueconnect
git clone git@github.com:<owner>/<repo>.git /opt/ueconnect
cd /opt/ueconnect
```

The deploy workflow requires `/opt/ueconnect/.git` to exist. It deploys by running:

```bash
git fetch --prune origin main
git reset --hard <commit-sha>
```

Configure the VM so it can read the repository, either by deploy key or by a GitHub account key managed for the server.

---

## 5. Production Environment File

Create `/opt/ueconnect/.env.production` directly on the VM. Do not commit this file.

Minimum production groups:

- `APP_NAME`, `APP_ENV=production`, `APP_KEY`, `APP_DEBUG=false`, `APP_URL=https://ueconnect.io.vn`.
- SQL Server database variables.
- Cache, queue, and session drivers.
- Reverb server/client variables.
- Mail provider variables.
- Media storage variables for R2 and Cloudinary if enabled.
- Microsoft SSO variables if enabled.
- VAPID keys for browser push notifications.
- AI/RAG provider variables if production chatbot features are enabled.

Reverb public client variables should match the HTTPS domain:

```env
BROADCAST_CONNECTION=reverb
REVERB_HOST=ueconnect.io.vn
REVERB_PORT=443
REVERB_SCHEME=https
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Keep secrets out of docs, screenshots, shell history, and issue comments.

---

## 6. GitHub Actions Secrets

The deploy workflow needs these repository or environment secrets:

| Secret | Purpose |
|---|---|
| `AZURE_VM_HOST` | Azure VM public IP or DNS host |
| `AZURE_VM_USER` | SSH username on the VM |
| `AZURE_VM_SSH_KEY` | Private SSH key used by GitHub Actions |

The corresponding public key must be installed in the VM user's `~/.ssh/authorized_keys`.

---

## 7. First Manual Smoke Test

Before relying on CI/CD, run one manual build on the VM:

```bash
cd /opt/ueconnect
test -f .env.production
docker build --tag ueconnect:manual .
docker rm -f ueconnect 2>/dev/null || true
docker run -d \
  --name ueconnect \
  --restart unless-stopped \
  --env-file .env.production \
  -e PORT=10000 \
  -p 127.0.0.1:10000:10000 \
  ueconnect:manual
curl -fsSI http://127.0.0.1:10000
sudo nginx -t
curl -fsSIk --resolve ueconnect.io.vn:443:127.0.0.1 https://ueconnect.io.vn >/dev/null
```

After this passes, GitHub Actions can own normal deploys.

---

## 8. Do Not Change Without Reviewing

- Do not expose Docker directly on `0.0.0.0:80` or `0.0.0.0:443`.
- Do not commit `.env.production`.
- Do not remove host Nginx unless the deploy workflow is changed too.
- Do not change the container name or app port without updating `.github/workflows/deploy-azure-vm.yml` and the host Nginx config.
