# offline-states.md

## 1. Purpose
Describe how the app behaves when connectivity is unavailable.

## 2. Design Decision
Use a clear offline notice and retry path without blocking local context unnecessarily.

## 3. Rationale
Users need to understand whether the app is down or only their connection is unstable.

## 4. Rules
- State the connection problem clearly.
- Offer retry or cached-content handling when possible.
- Keep the mood calm.

## 5. Do / Don't
Do: reassure the user that the issue may be temporary.

Don't: show a blank or frozen screen without explanation.

## 6. Tokens / Specs
Use neutral or warning accents with a single recovery action.

## 7. Component / Screen Impact
Impacts global banners, full-screen fallback states, and retry controls.

## 8. QA Checklist
- Offline status is recognizable.
- Retry action is visible.
- The user still knows what happened.

## 9. AI Prompt Notes
Ask for an offline screen that is informative and not dramatic.