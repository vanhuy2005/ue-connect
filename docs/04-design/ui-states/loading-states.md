# loading-states.md

## 1. Purpose
Describe loading and in-progress UI behavior.

## 2. Design Decision
Use skeletons or placeholders that preserve layout and reduce perceived wait time.

## 3. Rationale
Loading states should reassure users that the app is working without causing layout jumps.

## 4. Rules
- Keep skeleton shapes close to final content structure.
- Avoid spinner-only loading where content shape is known.
- Preserve button and form layout during loading.

## 5. Do / Don't
Do: keep the page stable while data loads.

Don't: let content jump around after load.

## 6. Tokens / Specs
Use neutral placeholder tones and stable spacing matching the final layout.

## 7. Component / Screen Impact
Impacts feed cards, lists, profile pages, buttons, and form sections.

## 8. QA Checklist
- Layout does not shift dramatically after load.
- Loading affordance is visible.
- Skeletons feel like the final page structure.

## 9. AI Prompt Notes
Ask for polished skeleton states that match the final UI geometry.