---
title: "Design System"
module: "04-design"
product: "UEConnect"
version: "1.0"
status: "deprecated-pointer"
priority: "P0"
last_updated: "2026-05-26"
owner: "Product Design / Design System / Frontend"
canonical_source_of_truth:
  - "03-color-system.md"
  - "04-gradient-policy.md"
  - "05-typography-system.md"
  - "06-spacing-system.md"
  - "07-radius-system.md"
  - "08-shadow-elevation-system.md"
  - "09-border-system.md"
  - "10-icon-system.md"
  - "11-logo-usage-system.md"
  - "12-component-primitives.md"
  - "13-component-variants.md"
  - "14-interaction-states.md"
  - "15-motion-system.md"
  - "16-content-tone.md"
  - "17-accessibility-rules.md"
  - "18-responsive-rules.md"
  - "19-design-token-documentation.md"
  - "20-agent-prompt-guide.md"
---

# Design System

> Deprecated pointer file.

This file is kept only for compatibility with older documentation references.

Do not expand this file into a second design system.

The UEConnect design system is split into canonical source-of-truth files so that each design decision has one clear owner and one clear place to update.

## Canonical Source of Truth

| Area | Source |
|---|---|
| Colors | [03-color-system.md](03-color-system.md) |
| Gradients | [04-gradient-policy.md](04-gradient-policy.md) |
| Typography | [05-typography-system.md](05-typography-system.md) |
| Spacing | [06-spacing-system.md](06-spacing-system.md) |
| Radius | [07-radius-system.md](07-radius-system.md) |
| Shadow / elevation | [08-shadow-elevation-system.md](08-shadow-elevation-system.md) |
| Borders | [09-border-system.md](09-border-system.md) |
| Icons | [10-icon-system.md](10-icon-system.md) |
| Logo usage | [11-logo-usage-system.md](11-logo-usage-system.md) |
| Component primitives | [12-component-primitives.md](12-component-primitives.md) |
| Component variants | [13-component-variants.md](13-component-variants.md) |
| Interaction states | [14-interaction-states.md](14-interaction-states.md) |
| Motion | [15-motion-system.md](15-motion-system.md) |
| Content tone | [16-content-tone.md](16-content-tone.md) |
| Accessibility | [17-accessibility-rules.md](17-accessibility-rules.md) |
| Responsive behavior | [18-responsive-rules.md](18-responsive-rules.md) |
| Design tokens | [19-design-token-documentation.md](19-design-token-documentation.md) |
| AI agent prompt usage | [20-agent-prompt-guide.md](20-agent-prompt-guide.md) |

## Rules

- Do not define new component rules here.
- Do not define new token values here.
- Do not copy sections from canonical files into this file.
- Any design system update must happen in the relevant canonical file.
- This file may only be used as an index for old references.

## Implementation Rule

When building UI, follow this order:

```txt
1. design-token-documentation.md
2. component-primitives.md
3. component-variants.md
4. interaction-states.md
5. accessibility-rules.md
6. responsive-rules.md
