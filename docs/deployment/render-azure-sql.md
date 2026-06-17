# Render Backup Plan

Status: Backup only

Summary: Phương án dự phòng khi Azure VM production không khả dụng. Render không phải production primary của UEConnect.

---

## 1. When To Use This

Use Render only as a temporary fallback when:

- Azure VM is unavailable for an extended period.
- Host Nginx/SSL or Docker runtime on the VM cannot be restored quickly.
- A demo/staging environment is needed while production VM maintenance is ongoing.

Normal production deployment must follow:

- [`../10-devops/azure-vm-setup.md`](../10-devops/azure-vm-setup.md)
- [`../10-devops/deployment.md`](../10-devops/deployment.md)
- [`../10-devops/ci-cd.md`](../10-devops/ci-cd.md)

---

## 2. Backup Architecture

Render can run the same Dockerfile as a web service. External services remain outside Render:

- SQL Server database.
- Cloudflare R2 and Cloudinary media storage.
- Mail provider.
- Web push VAPID keys.
- AI/RAG services if enabled.

Because Render backup may have different networking and resource limits, use it as a temporary recovery target, not as the default operating model.

---

## 3. Render Service Shape

Suggested setup:

| Setting | Value |
|---|---|
| Language/runtime | Docker |
| Dockerfile path | `Dockerfile` |
| Build context | `.` |
| Branch | emergency/backup branch or known-good main commit |
| Region | Closest available region to users/database |
| Instance type | Paid tier recommended for reliability |

Render provides the public port through `$PORT`. The container already renders Nginx config from `docker/nginx.conf.template`, so no app code changes should be required.

---

## 4. Required Environment Variables

Configure Render environment variables from the same groups documented in [`../10-devops/environment-variables.md`](../10-devops/environment-variables.md):

- Application: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`.
- SQL Server: `DB_CONNECTION=sqlsrv`, host, port, database, username, password, encryption settings.
- Cache/queue/session drivers.
- Reverb/broadcasting variables if realtime is enabled on the backup service.
- Mail provider variables.
- R2 and Cloudinary media variables.
- Microsoft SSO variables if login is enabled.
- VAPID keys.
- AI/RAG variables if enabled.

Never copy secrets into this document, issue comments, screenshots, or commit history.

---

## 5. Database Notes

If using Azure SQL or another SQL Server from Render:

- Prefer firewall rules restricted to Render's outbound IPs when available.
- Avoid opening `0.0.0.0/0` for production data.
- If a temporary broad firewall rule is required during an incident, document it in the incident notes and remove it as soon as the VM is restored.

---

## 6. Operational Limits

Render backup may differ from the Azure VM primary model:

- Cold starts can affect free or low-tier services.
- Local container storage is ephemeral.
- Outbound IP stability depends on plan/provider capability.
- Long-running Reverb and queue workloads may need separate services or a larger instance.
- Host-level Nginx checks from the Azure VM workflow do not apply.

Because of these differences, keep Render as a recovery option and move traffic back to Azure VM after the incident is resolved.

---

## 7. Return To Primary

When Azure VM is healthy again:

1. Restore DNS to `ueconnect.io.vn` on the Azure VM public IP if it was changed.
2. Verify `/opt/ueconnect/.env.production`.
3. Trigger `Deploy Azure VM` manually from GitHub Actions.
4. Run smoke checks in [`../10-devops/deployment.md`](../10-devops/deployment.md).
5. Disable or scale down the Render backup service.
