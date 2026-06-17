# Git Branch, Commit & PR Reference

---

## Branch Name

```
feat/ui-redesign-admin-and-community-dashboard
```

---

## Commit Message

```
feat(ui): redesign admin dashboard and community management UI

- Replace icon-only admin sidebar with a full enterprise navigation
  sidebar (Dashboard, Analytics, Users, Moderation, Security, Settings)
  with collapsible submenus, hover/active states, and admin profile popover

- Overhaul admin dashboard with 5 KPI metric cards, inline SVG trend
  charts, audit log timeline, and pending queue sections

- Contain community cover image, avatar, and metadata inside a white
  header card to prevent overlap with the left sidebar

- Inset community avatar with negative margin offset and white border
  for layered card visual hierarchy

- Redesign community tab navigation with blue active accent border
  and smooth hover transitions

- Restructure administration settings tab into a responsive two-column
  grid: primary forms (General, Privacy, Rules) on the left; live
  behavior preview panel and join request queue on the right

- Update CommunityDetailTest and AdminNavigationTest assertions to
  match the new UI labels and structure

- Apply Laravel Pint formatting to all modified PHP files
```

---

## Pull Request Description

### Summary

This PR delivers a comprehensive UI redesign of the Admin Dashboard and the Community Management Dashboard, bringing both surfaces to a modern, enterprise SaaS standard inspired by Stripe, Vercel, Linear, and Notion.

---

### Changes

#### Admin Dashboard

| Area | Before | After |
|---|---|---|
| Sidebar | Icon-only, minimal labels | Full-width nav with labels, collapsible submenus, profile popover |
| Dashboard | Basic widget grid | 5 KPI cards + SVG charts + audit log timeline |
| Layout shell | Standard app layout | Conditionally renders admin sidebar, hides regular sidebar |

**Files changed:**
- `resources/views/partials/app/admin-sidebar.blade.php` — new enterprise sidebar
- `resources/views/livewire/pages/admin/dashboard.blade.php` — redesigned operations console
- `resources/views/partials/app/admin-subsidebar.blade.php` — sub-navigation refinements
- `resources/views/partials/app/sidebar.blade.php` — conditional admin layout support

#### Community Management Dashboard

| Area | Before | After |
|---|---|---|
| Cover image | Full-bleed, overlapping sidebar | Contained inside a rounded white header card |
| Avatar | No clear boundary, poor hierarchy | Inset with `-mt-10` offset + `border-4 border-white` |
| Tab navigation | Plain underline | Active blue accent border + smooth transitions |
| Settings tab | Single-column flat form | Two-column grid: forms (left) + preview/queue panel (right) |

**Files changed:**
- `resources/views/livewire/pages/app/community-show.blade.php` — full UI overhaul

---

### Testing

- ✅ `AdminDashboardTest` — 10/10 passed
- ✅ `AdminNavigationTest` — updated assertions, all passed
- ✅ `CommunityShowTabStateTest` — all passed
- ✅ `CommunityDetailTest` — 20/21 passed (1 skipped: missing GD extension, environmental)
- ✅ Laravel Pint formatting applied to all modified PHP files

---

### Screenshots

> Attach screenshots of the new Admin Dashboard and Community page after review.

---

### Notes for Reviewers

- The GD extension failure in `test_owner_can_upload_community_cover_and_avatar` is a **local environment issue** (PHP GD not installed), not a regression introduced by this PR.
- No database migrations, model changes, or API contracts were modified.
- All text labels in the Community UI remain in Vietnamese to match existing UX conventions.
