# Design Implementation Notes

**Branch:** `feat/design-system-core`
**Date:** 2026-05-27
**Author:** Design System Agent

---

## Scope

Implemented design system core only. No full product page, database change, auth business rule, realtime event, or SQL Server schema change.

---

## Sources Followed

- `docs/04-design/01-brand-attributes.md`
- `docs/04-design/02-brand-identity-hcmue.md`
- `docs/04-design/03-color-system.md`
- `docs/04-design/04-gradient-policy.md`
- `docs/04-design/05-typography-system.md`
- `docs/04-design/06-spacing-system.md`
- `docs/04-design/07-radius-system.md`
- `docs/04-design/08-shadow-elevation-system.md`
- `docs/04-design/09-border-system.md`
- `docs/04-design/12-component-primitives.md`
- `docs/04-design/13-component-variants.md`
- `docs/04-design/14-interaction-states.md`
- `docs/04-design/17-accessibility-rules.md`
- `docs/04-design/18-responsive-rules.md`
- `docs/04-design/19-design-token-documentation.md`
- `docs/04-design/20-agent-prompt-guide.md`

---

## Assumptions & Decisions

### 1. Tailwind Version — Mixed v3/v4 Situation

**Finding:** `package.json` has `tailwindcss@3.4.19` AND `@tailwindcss/vite@4.3.0`. However, `vite.config.js` uses `laravel-vite-plugin` (not `@tailwindcss/vite`), and `tailwind.config.js` exists. The actual build pipeline runs **Tailwind v3** via PostCSS.

**Decision:** Kept Tailwind v3 config format. Did not upgrade/downgrade any package. The `@tailwindcss/vite@4` package is installed but not connected in this project's build pipeline. This should be resolved in a future infrastructure task.

**TODO:** Clarify intended Tailwind version and migrate config accordingly in a dedicated branch.

---

### 2. Font Loading

`Be Vietnam Pro` is loaded via Google Fonts in the layout `<head>`. Weights 400/500/600/700 only. `font-display: swap` is specified via the Google Fonts URL parameter.

**Alternative:** Self-host the font files for enterprise/offline use. Left as a TODO comment in the layouts.

---

### 3. No Dark Mode

Full dark mode is not implemented. The design docs do not yet define complete dark token values. CSS variables are structured so a future `[data-theme="dark"] {}` override block could be added without breaking the current token system.

---

### 4. Icon System

Lucide icon package is not installed. Used minimal inline SVG component (`x-ui.icon`) with ~30 core icons covering the design system's needs. This avoids adding an unplanned npm dependency.

**TODO:** When an icon package is officially selected, replace `resources/views/components/ui/icon.blade.php` with the package integration.

---

### 5. Breeze Component Coexistence

Existing `resources/views/components/*.blade.php` (Breeze) are **not modified**. New `x-ui.*` and `x-brand.*` namespaces are additive only. Auth pages continue to use Breeze components.

---

### 6. Brand Assets

All four logo assets existed in `docs/04-design/` and were copied to `public/images/brand/`:

- `horizontal-logo.png` → `public/images/brand/ueconnect-logo-horizontal.png`
- `icon-mark.png` → `public/images/brand/ueconnect-mark.png`
- `primary-logo-nobg.png` → `public/images/brand/ueconnect-logo.png`
- `fav-icon.png` → `public/images/brand/favicon.png`

The `x-brand.logo` component checks `file_exists()` before rendering an `<img>` tag, falling back to a CSS text mark to prevent 404 images.

---

### 7. Shell Layout and Breeze Navigation

The existing Breeze `<livewire:layout.navigation />` component in `app.blade.php` was replaced with new `partials/app/topbar`, `partials/app/sidebar`, and `partials/app/mobile-bottom-nav`. The Breeze livewire navigation component is no longer loaded in the app layout.

**Impact:** The `dashboard.blade.php` view (which extends `layouts.app`) will now use the new shell. If the dashboard view relied on the Breeze nav for anything, it should be tested.

**TODO:** Verify `dashboard.blade.php` renders correctly under the new shell.

---

### 8. Unimplemented Route Placeholders

Shell nav links for `Khám phá`, `Tin nhắn`, `Cộng đồng`, `Mentor`, `Thông báo` use `href="#"` placeholders. Only `dashboard` and `profile` routes (which exist) are used with `route()`.

---

## Non-goals (Not Implemented in This Branch)

- Home Feed page
- Auth business rule implementation
- HCMUE email validation
- SQL Server migration or schema change
- PWA service worker
- Realtime events (Reverb/Echo)
- Notification system
- Admin dashboard
- Dark mode
- Messaging UI
- Profile UI
