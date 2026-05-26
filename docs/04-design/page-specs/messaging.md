# messaging.md

## 1. Purpose
Describe the direct messaging experience between users.

## 2. Design Decision
Keep messages lightweight, readable, and clearly separated from feed or discovery UI.

## 3. Rationale
Messaging is a high-frequency task, so readability and state clarity matter more than visual flourish.

## 4. Rules
- Messages must be easy to scan.
- Composer actions should be obvious.
- Conversation state must remain visible.

## 5. Do / Don't
Do: keep the input area stable and obvious.

Don't: use visual effects that distract from message reading.

## 6. Tokens / Specs
Use clear bubbles, readable spacing, and muted background contrast for chat lanes.

## 7. Component / Screen Impact
Impacts message list, chat bubbles, composer, timestamps, attachments, and typing state.

## 8. QA Checklist
- Message flow is easy to follow.
- Composer remains accessible on mobile.
- New message states are visible.

## 9. AI Prompt Notes
Ask for a simple, modern chat interface that still feels like part of UEConnect.