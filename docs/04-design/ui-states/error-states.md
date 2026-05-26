# error-states.md

## 1. Purpose
Describe how recoverable and non-recoverable errors are presented.

## 2. Design Decision
Use direct messaging, clear recovery actions, and calm visual emphasis.

## 3. Rationale
Error states should help users recover quickly without panic or ambiguity.

## 4. Rules
- Explain the problem in plain language.
- Offer the best recovery action first.
- Avoid vague or technical error copy.

## 5. Do / Don't
Do: be clear and actionable.

Don't: say only that something went wrong.

## 6. Tokens / Specs
Use danger color sparingly, with strong text contrast and a single primary recovery action.

## 7. Component / Screen Impact
Impacts forms, network failures, save actions, and critical workflow interruptions.

## 8. QA Checklist
- The error is understandable.
- Recovery is obvious.
- The state does not feel alarming unless it truly is.

## 9. AI Prompt Notes
Ask for an error state that is calm, precise, and recovery-oriented.