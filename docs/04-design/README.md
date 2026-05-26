# 04-design

This folder is the design documentation hub for UEConnect.

## Canonical Structure

- [00-design-foundation-roadmap.md](00-design-foundation-roadmap.md)
- [01-brand-attributes.md](01-brand-attributes.md)
- [02-brand-identity-hcmue.md](02-brand-identity-hcmue.md)
- [03-color-system.md](03-color-system.md)
- [04-gradient-policy.md](04-gradient-policy.md)
- [05-typography-system.md](05-typography-system.md)
- [06-spacing-system.md](06-spacing-system.md)
- [07-radius-system.md](07-radius-system.md)
- [08-shadow-elevation-system.md](08-shadow-elevation-system.md)
- [09-border-system.md](09-border-system.md)
- [10-icon-system.md](10-icon-system.md)
- [11-logo-usage-system.md](11-logo-usage-system.md)
- [12-component-primitives.md](12-component-primitives.md)
- [13-component-variants.md](13-component-variants.md)
- [14-interaction-states.md](14-interaction-states.md)
- [15-motion-system.md](15-motion-system.md)
- [16-content-tone.md](16-content-tone.md)
- [17-accessibility-rules.md](17-accessibility-rules.md)
- [18-responsive-rules.md](18-responsive-rules.md)
- [19-design-token-documentation.md](19-design-token-documentation.md)
- [20-agent-prompt-guide.md](20-agent-prompt-guide.md)
- [use-cases/use-case-index.md](use-cases/use-case-index.md)
- [page-specs/page-spec-index.md](page-specs/page-spec-index.md)

## Secondary Structure

- [information-architecture.md](information-architecture.md)
- [user-flow/](user-flow)
- [use-cases/](use-cases)
- [page-specs/](page-specs)
- [ui-states/](ui-states)
- [examples/](examples)

## File Quality Rules

Every markdown file in this folder should follow the same review contract:

1. Purpose: what problem the file solves.
2. Design Decision: the final decision, not options.
3. Rationale: why this decision exists and what it prevents.
4. Rules: non-negotiable constraints.
5. Do / Don't: acceptable and forbidden patterns.
6. Tokens / Specs: colors, sizes, spacing, motion, or other parameters.
7. Component / Screen Impact: what UI elements this changes.
8. QA Checklist: how to review correctness.
9. AI Prompt Notes: how to ask AI for a UI that matches this doc.

## Processing Rules For Existing Files

- `brand-guideline.md` becomes a pointer to brand and logo docs.
- `design-system.md` becomes a pointer to foundation docs.
- `information-architecture.md` stays separate and links into page specs.
- `preview.html` remains the live preview, not the source of truth.
- `UE-connect-DESIGN-enterprise-v3.md` is the current consolidated narrative.
- `UE-connect-DESIGN.md` may stay as a legacy working copy or be archived later.
- `use-cases/` defines role-based behavior before page design.
- `user-flow/` defines task sequencing before page-spec composition.
- `page-specs/` defines screen requirements after use cases and flows.
- `ux-principles.md` should be treated as a legacy pointer into the foundation set.
- `wireframe-notes.md` should be moved into `examples/` or the matching page-spec file.

## Review Rule

If a file cannot be reviewed using the nine-section contract above, it is too vague and should be rewritten.