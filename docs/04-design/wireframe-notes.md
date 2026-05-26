---
title: "UX Principles"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "deprecated-pointer"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / UX"
canonical_source_of_truth:
  - "00-design-foundation-roadmap.md"
  - "01-brand-attributes.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "14-interaction-states.md"
  - "15-motion-system.md"
---

# UX Principles

> Deprecated pointer file.

This file is kept only for compatibility with older documentation references.

Do not add new UX decisions here.

UEConnect UX principles are now split into focused canonical files.

## Canonical Source of Truth

| UX Area | Source |
|---|---|
| Design foundation roadmap | [00-design-foundation-roadmap.md](00-design-foundation-roadmap.md) |
| Brand and product personality | [01-brand-attributes.md](01-brand-attributes.md) |
| Content tone and microcopy | [16-content-tone.md](16-content-tone.md) |
| Interaction behavior | [14-interaction-states.md](14-interaction-states.md) |
| Motion behavior | [15-motion-system.md](15-motion-system.md) |
| Accessibility | [17-accessibility-rules.md](17-accessibility-rules.md) |
| Responsive behavior | [18-responsive-rules.md](18-responsive-rules.md) |

## Rules

- Do not define new UX principles here.
- Do not define new content tone rules here.
- Do not define accessibility or responsive behavior here.
- Update the relevant canonical file instead.
- Keep this file as an index for old links only.

## UEConnect UX Summary

UEConnect UX should be:

```txt
clear
trusted
student-first
accessible
mobile-first
privacy-aware
connection-focused
non-romantic
calm in serious flows
efficient in admin flows

For detailed decisions, use the canonical files above.


---

## `wireframe-notes.md`

```md
---
title: "Wireframe Notes"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "deprecated-pointer"
priority: "P1"
last_updated: "2026-05-26"
owner: "Product Design / UX / UI"
canonical_source_of_truth:
  - "examples/mobile-feed-wireframe.md"
  - "examples/desktop-feed-wireframe.md"
  - "examples/component-showcase.md"
  - "page-specs/page-spec-index.md"
  - "page-specs/onboarding.md"
  - "page-specs/home-feed.md"
  - "page-specs/discovery.md"
  - "page-specs/messaging.md"
  - "page-specs/mentor.md"
  - "page-specs/clubs.md"
---

# Wireframe Notes

> Deprecated pointer file.

This file is kept only for compatibility with older documentation references.

Do not add new wireframe decisions here.

Wireframe notes should live in either:

```txt
examples/

or the matching:

page-specs/*.md
Canonical Source of Truth
Wireframe Area	Source
Mobile feed wireframe	examples/mobile-feed-wireframe.md
Desktop feed wireframe	examples/desktop-feed-wireframe.md
Component showcase	examples/component-showcase.md
Page spec index	page-specs/page-spec-index.md
Onboarding screen	page-specs/onboarding.md
Home feed screen	page-specs/home-feed.md
Discovery screen	page-specs/discovery.md
Messaging screen	page-specs/messaging.md
Mentor screen	page-specs/mentor.md
Community / clubs screen	page-specs/clubs.md
Rules
Do not put annotated wireframes directly in this file.
Put reusable visual examples in examples/.
Put screen-specific wireframe notes in the matching page-specs/*.md.
Keep wireframe notes aligned with information-architecture.md.
Keep layout decisions aligned with 18-responsive-rules.md.
Keep component decisions aligned with 12-component-primitives.md and 13-component-variants.md.
Recommended Wireframe Documentation Pattern

For each wireframe file, use:

1. Purpose
2. Screen / route
3. User role
4. Layout structure
5. Key sections
6. Primary actions
7. Secondary actions
8. Empty/loading/error states
9. Mobile behavior
10. Desktop behavior
11. Accessibility notes
12. Open questions
Final Rule

If a wireframe note changes a screen behavior, update the matching page spec.

If a wireframe note changes navigation hierarchy, update information-architecture.md.

If a wireframe note changes tokens/components, update the relevant design system canonical file.

Do not let wireframe notes become a secret second product spec. That is how documentation becomes folklore.


---

## Nên giữ file nào là source thật?

Chốt lại cho sạch:

| File | Status đúng |
|---|---|
| `brand-guideline.md` | Deprecated pointer |
| `design-system.md` | Deprecated pointer |
| `information-architecture.md` | Source of truth |
| `ux-principles.md` | Deprecated pointer |
| `wireframe-notes.md` | Deprecated pointer |

Tức là chỉ **`information-architecture.md`** còn được phép chứa decision thật. Bốn file còn lại chỉ trỏ sang f
