/**
 * Mobile Bottom Sheet / Action Sheet Controller
 *
 * Supports focus trapping, background scroll locking, backdrop dismissal,
 * touch swipe-down to close, and keyboard safety.
 *
 * Key design decisions:
 * - The sheet is TELEPORTED to <body> on open so position:fixed is never
 *   cancelled by ancestor overflow/transform stacking contexts.
 * - Backdrop handler references are stored so they can be cleanly removed.
 * - trapFocus runs after the open transition delay so the sheet is visible.
 * - Works safely with Livewire re-renders (sheet is returned to origin on close).
 */
import { trapFocus } from './focus-trap';

let activeUntrap         = null;
let lastActiveElement    = null;
let backdropClickHandler = null;
let isOpen               = false;

/** Original parent and sibling of the teleported sheet (for restore on close). */
let sheetOrigin = null;

// Touch swipe tracking
let touchStartY = 0;

/** Lazily create/retrieve the singleton backdrop element. */
function getOrCreateBackdrop() {
    let backdrop = document.querySelector('.ue-sheet-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'ue-sheet-backdrop';
        document.body.appendChild(backdrop);
    }
    return backdrop;
}

export function initBottomSheets() {
    window.closeActiveBottomSheet = closeActiveBottomSheet;

    // Click listener for sheet triggers (event delegation — survives Livewire morphs)
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-ue-sheet-trigger]');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const sheetId = trigger.getAttribute('data-ue-sheet-trigger');
            openBottomSheet(sheetId, trigger);
        }
    });

    // ESC to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            e.preventDefault();
            closeActiveBottomSheet();
        }
    });
}

export function openBottomSheet(sheetId, triggerEl = null) {
    const sheet = document.querySelector(`[data-ue-sheet="${sheetId}"]`);
    if (!sheet) return;

    // Close any already-open sheet first
    if (isOpen) {
        closeActiveBottomSheet();
    }

    isOpen = true;
    lastActiveElement = triggerEl || document.activeElement;

    // ── Teleport sheet to <body> ──────────────────────────────────────────────
    // This ensures position:fixed is never cancelled by ancestor
    // overflow, clip, or transform contexts inside the feed.
    sheetOrigin = {
        parent:      sheet.parentNode,
        nextSibling: sheet.nextSibling,
    };
    document.body.appendChild(sheet);
    // ─────────────────────────────────────────────────────────────────────────

    const backdrop = getOrCreateBackdrop();

    // Remove any stale backdrop handler before adding a fresh one
    if (backdropClickHandler) {
        backdrop.removeEventListener('click', backdropClickHandler);
    }
    backdropClickHandler = () => closeActiveBottomSheet();
    backdrop.addEventListener('click', backdropClickHandler);

    // Prevent background scroll
    document.body.style.overflow = 'hidden';

    // Activate with rAF so transitions fire correctly after teleport
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            backdrop.classList.add('active');
            sheet.classList.add('active');
            sheet.setAttribute('aria-hidden', 'false');
        });
    });

    // Trap focus AFTER the slide-in transition so elements are visible/measurable
    setTimeout(() => {
        if (isOpen) {
            activeUntrap = trapFocus(sheet);
        }
    }, 250);

    // Touch swipe-down to close
    sheet.addEventListener('touchstart', onTouchStart, { passive: true });
    sheet.addEventListener('touchend',   onTouchEnd,   { passive: true });
}

export function closeActiveBottomSheet() {
    if (!isOpen) return;
    isOpen = false;

    const activeSheet = document.querySelector('[data-ue-sheet].active');
    const backdrop    = document.querySelector('.ue-sheet-backdrop');

    if (activeSheet) {
        activeSheet.classList.remove('active');
        activeSheet.setAttribute('aria-hidden', 'true');

        activeSheet.removeEventListener('touchstart', onTouchStart);
        activeSheet.removeEventListener('touchend',   onTouchEnd);

        // ── Restore sheet to its original DOM position after transition ──────
        const origin = sheetOrigin;
        sheetOrigin = null;

        if (origin && origin.parent && document.body.contains(origin.parent)) {
            setTimeout(() => {
                // Only restore if the parent is still in the DOM (Livewire morph
                // may have removed it; in that case silently discard the element)
                if (document.body.contains(origin.parent)) {
                    origin.parent.insertBefore(activeSheet, origin.nextSibling);
                }
            }, 300); // match sheet slide-out duration
        }
        // ─────────────────────────────────────────────────────────────────────
    }

    if (backdrop) {
        backdrop.classList.remove('active');
        if (backdropClickHandler) {
            backdrop.removeEventListener('click', backdropClickHandler);
            backdropClickHandler = null;
        }
    }

    // Restore scroll
    document.body.style.overflow = '';

    // Release focus trap
    if (activeUntrap) {
        activeUntrap();
        activeUntrap = null;
    }

    // Return focus to trigger with slight delay so slide-out transition completes
    if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
        setTimeout(() => {
            lastActiveElement?.focus();
            lastActiveElement = null;
        }, 50);
    }
}

// ── Touch swipe-down gesture ─────────────────────────────────────────────────

function onTouchStart(e) {
    touchStartY = e.changedTouches[0].clientY;
}

function onTouchEnd(e) {
    const deltaY = e.changedTouches[0].clientY - touchStartY;
    // Close if swiped down more than 80px
    if (deltaY > 80) {
        closeActiveBottomSheet();
    }
}
