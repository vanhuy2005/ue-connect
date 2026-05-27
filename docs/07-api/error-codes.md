# API Error Codes

This document details the standard semantic error codes returned by the UEConnect API and authentication modules.

---

## 1. Authentication Error Codes

| Error Code | HTTP Status | Description |
| ---------- | ----------- | ----------- |
| `AUTH_MICROSOFT_DISABLED` | 302 / 422 | Microsoft SSO is disabled on this environment. |
| `AUTH_MICROSOFT_CONFIG_MISSING` | 302 / 422 | Crucial Microsoft SSO client keys or secrets are missing. |
| `AUTH_MICROSOFT_TENANT_MISMATCH` | 302 / 422 | The Azure Directory Tenant ID does not match the configured single-tenant. |
| `AUTH_MICROSOFT_DOMAIN_NOT_ALLOWED` | 302 / 422 | The authenticated Microsoft email domain is not permitted (must be `@hcmue.edu.vn`). |
| `ACCOUNT_RESTRICTED` | 403 | The authenticated account is restricted, suspended, or banned. |
| `VERIFICATION_REQUIRED` | 403 | The requested route is protected and requires a verified identity. |
| `ADMIN_PERMISSION_REQUIRED` | 403 | Access denied due to missing administrative permissions (`review_verification`). |
| `EVIDENCE_NOT_FOUND` | 404 | The requested verification evidence file or entity was not found. |