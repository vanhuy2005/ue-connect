# notifications.md

## 1. Purpose
Describe how alerts, updates, and system notifications are presented.

## 2. Design Decision
Use clear categorization, readable timestamps, and consistent state treatment.

## 3. Rationale
Notifications should feel useful, not noisy or punitive.

## 4. Rules
- Group similar notifications together when possible.
- Show unread state clearly.
- Keep actions or links easy to find.

## 5. Do / Don't
Do: make notification intent obvious.

Don't: flood the user with unprioritized updates.

## 6. Tokens / Specs
Use badge indicators, subtle dividers, and calm surface colors.

## 7. Component / Screen Impact
Impacts notification rows, badges, icons, timestamps, and empty state messaging.

## 8. QA Checklist
- Unread and read states are distinct.
- Important actions are not hidden.
- The list stays scannable.

## 9. AI Prompt Notes
Ask for a clear notification center with strong hierarchy and minimal clutter.