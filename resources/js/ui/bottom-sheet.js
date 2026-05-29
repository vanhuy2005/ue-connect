/**
 * Mobile Bottom Sheet / Action Sheet Controller
 * 
 * Supports focus trapping, background scroll locking, backdrop dismissal,
 * and keyboard safety.
 */
import { trapFocus } from './focus-trap';

let activeUntrap = null;
let lastActiveElement = null;

export function initBottomSheets() {
    window.closeActiveBottomSheet = closeActiveBottomSheet;

    // Click listener for sheet triggers
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-ue-sheet-trigger]');
        
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            
            const sheetId = trigger.getAttribute('data-ue-sheet-trigger');
            openBottomSheet(sheetId, trigger);
        }
    });

    // Handle ESC keypress close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeActiveBottomSheet();
        }
    });
}

export function openBottomSheet(sheetId, triggerEl = null) {
    const sheet = document.querySelector(`[data-ue-sheet="${sheetId}"]`);
    if (!sheet) return;

    closeActiveBottomSheet();

    lastActiveElement = triggerEl || document.activeElement;

    // Build or select backdrop
    let backdrop = document.querySelector('.ue-sheet-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'ue-sheet-backdrop';
        document.body.appendChild(backdrop);
    }
    
    // Open sheets with brief delay for transitions
    setTimeout(() => {
        backdrop.classList.add('active');
        sheet.classList.add('active');
        sheet.setAttribute('aria-hidden', 'false');
    }, 10);

    // Prevent body page scroll
    document.body.style.overflow = 'hidden';

    // Dismiss on backdrop click
    const backdropCloseHandler = () => closeActiveBottomSheet();
    backdrop.addEventListener('click', backdropCloseHandler, { once: true });

    // Trap focus inside bottom sheet
    activeUntrap = trapFocus(sheet);
}

export function closeActiveBottomSheet() {
    const activeSheet = document.querySelector('[data-ue-sheet].active');
    const backdrop = document.querySelector('.ue-sheet-backdrop.active');

    if (activeSheet) {
        activeSheet.classList.remove('active');
        activeSheet.setAttribute('aria-hidden', 'true');
    }
    if (backdrop) {
        backdrop.classList.remove('active');
    }

    // Restore body scroll
    document.body.style.overflow = '';

    // Untrap Focus
    if (activeUntrap) {
        activeUntrap();
        activeUntrap = null;
    }

    // Restore trigger focus
    if (lastActiveElement) {
        lastActiveElement.focus();
        lastActiveElement = null;
    }
}
