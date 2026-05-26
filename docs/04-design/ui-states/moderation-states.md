# moderation-states.md

## 1. Purpose
Describe moderated, restricted, pending-review, and hidden-content states.

## 2. Design Decision
Use clear but non-accusatory messaging that explains the status and next step.

## 3. Rationale
Moderation UI must preserve trust while avoiding shame or ambiguity.

## 4. Rules
- State what happened in plain language.
- Include next steps where possible.
- Avoid exposing sensitive internal review details.

## 5. Do / Don't
Do: keep moderation feedback calm and respectful.

Don't: use a punitive tone.

## 6. Tokens / Specs
Use warning and neutral tokens carefully, with readable support copy.

## 7. Component / Screen Impact
Impacts hidden post notices, account restrictions, review pending messages, and admin feedback surfaces.

## 8. QA Checklist
- The message is understandable.
- Next steps are clear.
- The user is not unnecessarily alarmed.

## 9. AI Prompt Notes
Ask for a moderation state that is firm, fair, and calm.