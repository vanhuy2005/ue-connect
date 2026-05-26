# permission-states.md

## 1. Purpose
Describe the UI pattern for camera, notification, location, and other permissions.

## 2. Design Decision
Explain why the permission matters before asking for it.

## 3. Rationale
Permission prompts convert better when users understand the benefit and the impact.

## 4. Rules
- Ask for permission only when the feature needs it.
- Explain value in plain language.
- Provide a graceful fallback when permission is denied.

## 5. Do / Don't
Do: ask with context.

Don't: surface permissions without a reason.

## 6. Tokens / Specs
Use a compact modal or sheet with a single primary action and a clear secondary option.

## 7. Component / Screen Impact
Impacts permission modals, onboarding gates, and feature-specific prompts.

## 8. QA Checklist
- The user understands why the permission matters.
- The fallback path is clear.
- Denial does not break the app.

## 9. AI Prompt Notes
Ask for a permission prompt that feels respectful and contextual.