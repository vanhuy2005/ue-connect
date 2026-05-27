# Authentication API

This document details the authentication endpoints, OAuth flows, and redirects implemented in UEConnect.

---

## 1. Microsoft SSO OAuth Flow

To maintain high security and trust, UEConnect supports single-sign-on using Microsoft Entra ID (Outlook HCMUE emails).

### 1.1. Redirect to Provider
- **Endpoint**: `GET /auth/microsoft/redirect`
- **Named Route**: `auth.microsoft.redirect`
- **Middleware**: `guest`
- **Description**: Verifies if Microsoft SSO is fully configured and enabled, then redirects the client to the Microsoft Entra ID authorization screen requesting the configured scopes.
- **Requested Scopes**: `openid`, `profile`, `email`, `User.Read`
- **Exceptions**:
  - `AUTH_MICROSOFT_DISABLED`: Returned when the SSO feature flag is turned off in settings.
  - `AUTH_MICROSOFT_CONFIG_MISSING`: Returned when crucial parameters (Client ID, Secret, Redirect URI) are missing.

### 1.2. OAuth Callback
- **Endpoint**: `GET /auth/microsoft/callback`
- **Named Route**: `auth.microsoft.callback`
- **Middleware**: `guest`
- **Description**: Handles the authenticated response returned by Microsoft. Validates the tenant ID, email address domain, maps the identities, and redirects based on the resolved account status.
- **Validations**:
  - **Tenant ID Validation**: Must strictly equal the configured single-tenant `MICROSOFT_TENANT_ID`. Any mismatch or general organizations/common bypass is strictly rejected.
  - **Domain Validation**: The resolved email address must belong to `@hcmue.edu.vn`.
- **Redirects by Account Status**:
  - `suspended` / `banned` / `deleted` → `system.account-restricted`
  - `registered` / `pending_verification` → `verification.status` or `verification.start`
  - `profile_incomplete` → `profile.setup`
  - `active` → `dashboard`

---

## 2. Session Management

### 2.1. User Logout
- **Endpoint**: `POST /logout`
- **Named Route**: `logout`
- **Middleware**: `auth`
- **Description**: Destroys the current authenticated session and redirects the user back to the login screen.
- **Method**: Secure POST (requires CSRF token).