# UEConnect Frontend UI System Production UAT & QA Checklist

This document details the user acceptance testing (UAT) and quality assurance (QA) protocols for the newly hardened production UI system. It serves as the master checklist to verify that all layout structures, micro-interactions, responsive states, and performance markers match modern social standards.

---

## 1. Navigation & App Shell v2 (Social Navigation Architecture)

The old horizontal desktop topbar is removed for the authenticated social experience. Verify the unified, content-focused shell:

- [ ] **No Content Overlay/Clipping**: Ensure page headers and content flow cleanly below layout edges without overlapping sidebars or bottom navs.
- [ ] **Desktop Social Navigation Structure**:
  - [ ] Centered Content Column: Content area restricted to a maximum width of `680px` (`.ue-feed-column`).
  - [ ] Left Sidebar (280px): Stays sticky at the left side of the viewport.
  - [ ] Bottom-Left "Xem thêm" Trigger:
    - [ ] Dynamic Menu Popover: Appears cleanly on click and closes on escape/outside click.
    - [ ] Contains all secondary actions (Settings, Support, Theme Preference, Logout) with zero duplicate links.
- [ ] **Mobile Layout Shell**:
  - [ ] Bottom Navigation: Persistent on all authenticated mobile viewports.
  - [ ] Height Safeguards: Bottom padding buffer of at least `80px` (`.ue-mobile-bottom-spacer`) on all feed pages to prevent the bottom nav from overlaying content.

---

## 2. CSS Transitions, Shimmers, and Motion Reduction

Visual refinement and fluid motion must feel responsive without being distracting.

- [ ] **Shimmer & Hover Transitions**:
  - [ ] Card Hovers: Gently scales post cards and transitions borders without jitter.
  - [ ] Skeleton Cards: Shimmering animation flows smoothly.
  - [ ] Interactive Elements: Press states scale down slightly (`scale-98`) for tactile feedback.
- [ ] **Accessibility (OS Reduced Motion)**:
  - [ ] Standard: Transitions run smoothly at normal speed (`120ms` to `280ms`).
  - [ ] Preference Detected: If `prefers-reduced-motion: reduce` is enabled in the OS:
    - [ ] All transition durations automatically default to `0s`/`instant`.
    - [ ] Shimmer animations stop.
    - [ ] Tactile scales are deactivated.

---

## 3. Touch Target and Accessibility Guidelines

Ensure mobile usability matches high-performance application standards.

- [ ] **Touch Target Size**:
  - [ ] Interaction Targets: Like, comment, share, and save buttons must have an active tap target size of at least `44px` (`.ue-action-button` primitive).
- [ ] **Visual Accessibility**:
  - [ ] Hover State Contrast: All active text elements must maintain high contrast when hovered (e.g. text remains fully readable against white or dark backgrounds).
  - [ ] Select Indicators: Selection and selected button states must not rely solely on color changes (e.g. solid filled icons for liked/saved elements).

---

## 4. Livewire & Optimistic Feedback Interactions

Verify client-side interaction speed and server-side synchronization.

- [ ] **Optimistic State Feedback**:
  - [ ] Like Toggle: Click increments the like counter and fills the icon immediately.
  - [ ] Save Toggle: Click changes the icon immediately to indicate save status.
  - [ ] If Server Request Fails: The UI must automatically rollback to the original state without breaking layout structure.
- [ ] **Central SPA Listener Binding**:
  - [ ] Verify that all Vanilla JS interactions (dropdowns, sheets, optimistic counters) bind and fire correctly after page navigation under Livewire's `livewire:navigated` event hooks.

---

## 5. Content Safety, Empty States, and Accessible Modals

Verify modals, composers, and layout buffers.

- [ ] **Inline Composer**:
  - [ ] Character Limit: Textarea strictly tracks and enforces the `3000` character limit.
  - [ ] Auto-Expansion: Textarea auto-grows as new lines are added without creating dual scrollbars.
- [ ] **FAB Modal Trigger**:
  - [ ] FAB: Persistent in the bottom-right corner of the feed page.
  - [ ] Dialog: Opens a clean, accessible centered composer modal on click.
  - [ ] Focus Trap: Tabbing inside the modal must loop correctly; keyboard focus cannot escape to background page controls.
- [ ] **Empty States**:
  - [ ] Verify that blank feeds or empty lists cleanly display the customized `<x-ui.empty-state>` component with contextual action triggers.
