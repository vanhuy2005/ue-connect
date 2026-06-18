# Resend Mail and Cloudflare DNS Setup

Status: Source of truth

Summary: Cấu hình Resend cho email gửi đi và Cloudflare DNS cho domain `ueconnect.io.vn`.

---

## 1. Domain Ownership

UEConnect uses `ueconnect.io.vn` as the production domain and `send.ueconnect.io.vn` as the mail-sending subdomain.

If the domain is managed at Mắt Bão, move DNS management to Cloudflare:

1. Open Cloudflare Dashboard.
2. Add site `ueconnect.io.vn`.
3. Choose the required plan.
4. Copy Cloudflare nameservers.
5. Update nameservers in the Mắt Bão domain manager.
6. Wait for DNS propagation.

---

## 2. Web App DNS

Production web traffic should point to the Azure VM public IP.

| Record | Name | Value | Proxy |
|---|---|---|---|
| `A` | `@` | Azure VM public IPv4 | Optional after SSL is verified |
| `A` or `CNAME` | `www` | Azure VM public IPv4 or `ueconnect.io.vn` | Optional after SSL is verified |

The Azure VM host Nginx owns public `80/443` and proxies traffic to Docker on `127.0.0.1:10000`.

If Render is activated as a backup plan during an incident, update DNS temporarily according to [`render-azure-sql.md`](render-azure-sql.md), then restore DNS to the Azure VM after recovery.

---

## 3. Resend Domain

In Resend:

1. Open **Domains**.
2. Add `send.ueconnect.io.vn`.
3. Select the closest suitable region.
4. Copy the DNS records provided by Resend.

In Cloudflare, add the records Resend provides. Typical record groups:

| Purpose | Type | Name example | Notes |
|---|---|---|---|
| DKIM | `TXT` or `CNAME` | `resend._domainkey.send` | Use the exact value from Resend |
| SPF | `TXT` | `send` | Usually includes `amazonses.com` |
| MX | `MX` | `send` | Use the mail server and priority from Resend |
| DMARC | `TXT` | `_dmarc.send` | Start with monitoring, then tighten policy later |

Keep mail DNS records as **DNS only** when the provider requires direct verification.

---

## 4. Laravel Environment

Local `.env` or production `/opt/ueconnect/.env.production`:

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=no-reply@send.ueconnect.io.vn
MAIL_FROM_NAME="${APP_NAME}"
```

Never commit the real `RESEND_API_KEY`. Store it only in `.env.production` or a secret manager.

---

## 5. Verification

After DNS propagation:

1. Confirm Resend marks `send.ueconnect.io.vn` as verified.
2. Trigger a safe test email from the application.
3. Check SPF/DKIM/DMARC pass in the received email headers.
4. Confirm app traffic still resolves to the Azure VM.

Useful DNS checks:

```bash
nslookup ueconnect.io.vn
nslookup -type=TXT send.ueconnect.io.vn
nslookup -type=MX send.ueconnect.io.vn
```
