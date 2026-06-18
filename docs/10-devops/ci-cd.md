# CI/CD

Status: Source of truth

Summary: GitHub Actions pipeline for validating and deploying UEConnect.

---

## 1. Workflows

UEConnect currently uses two GitHub Actions workflows:

| Workflow | File | Purpose |
|---|---|---|
| `CI` | `.github/workflows/ci.yml` | Validate code, build assets, run tests, and build Docker image |
| `Deploy Azure VM` | `.github/workflows/deploy-azure-vm.yml` | Deploy the tested commit to the Azure VM production server |

Production deploy is intentionally gated by CI. A push to `main` must pass `CI` before `Deploy Azure VM` runs automatically.

---

## 2. CI Workflow

Triggers:

- Pull requests.
- Pushes to `main`.

Concurrency:

```txt
group: ci-${{ github.ref }}
cancel-in-progress: true
```

Jobs:

### `test`

Runs on `ubuntu-latest`:

1. Checkout source.
2. Setup PHP `8.3`.
3. Install PHP extensions: `bcmath`, `exif`, `gd`, `intl`, `mbstring`, `pcntl`, `pdo`, `sqlite`, `zip`.
4. Setup Node `22` with npm cache.
5. Run `composer install --no-interaction --prefer-dist --no-progress`.
6. Run `npm ci`.
7. Copy `.env.example` to `.env`.
8. Generate `APP_KEY`.
9. Clear config.
10. Run `npm run build`.
11. Run `php artisan test --compact`.

### `docker`

Runs after `test` passes:

```bash
docker build --tag ueconnect:${{ github.sha }} .
```

This catches Dockerfile/build regressions before production deploy.

---

## 3. Deploy Azure VM Workflow

Triggers:

- `workflow_run` after `CI` completes on `main`.
- Manual `workflow_dispatch`.

Gate:

```txt
github.event_name == 'workflow_dispatch'
or github.event.workflow_run.conclusion == 'success'
```

Concurrency:

```txt
group: deploy-azure-vm
cancel-in-progress: false
```

`cancel-in-progress: false` prevents overlapping production deploys from interrupting each other mid-container-restart.

Deploy environment:

```txt
environment: production
```

Required GitHub secrets:

| Secret | Purpose |
|---|---|
| `AZURE_VM_HOST` | Azure VM public host/IP |
| `AZURE_VM_USER` | SSH user |
| `AZURE_VM_SSH_KEY` | Private SSH key for deploy |

The workflow deploys over SSH using `appleboy/ssh-action@v1.2.0`.

---

## 4. Production Constants

The workflow currently deploys with:

```txt
APP_DIR=/opt/ueconnect
APP_PORT=10000
DOMAIN=ueconnect.io.vn
CONTAINER_NAME=ueconnect
IMAGE_TAG=ueconnect:<commit-sha>
```

If any of these change, update together:

- `.github/workflows/deploy-azure-vm.yml`
- Host Nginx site config on the Azure VM
- [`azure-vm-setup.md`](azure-vm-setup.md)
- [`deployment.md`](deployment.md)

---

## 5. Deployment Checks

The deploy workflow fails fast when:

- `/opt/ueconnect/.git` does not exist.
- `/opt/ueconnect/.env.production` is missing.
- Docker image build fails.
- Vite manifest, CSS, or JS assets are missing.
- The new container does not respond on `127.0.0.1:10000`.
- Host Nginx config is invalid or inactive.
- Docker binds public `80/443`.
- HTTPS through `ueconnect.io.vn` fails.
- Reverb WebSocket cannot establish a connection.
- VAPID keys are missing.

---

## 6. Manual Deploy

Use GitHub Actions `workflow_dispatch` on `Deploy Azure VM` when:

- Re-running a failed deploy after fixing server state.
- Deploying the current branch/ref from the GitHub UI.
- Validating changes after manual VM maintenance.

Do not manually edit production code on the VM except for emergency recovery. Normal deploys should always go through GitHub Actions.
