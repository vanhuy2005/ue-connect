/**
 * Accessible Focus Trap Utility
 * 
 * Traps keyboard Focus (Tab/Shift+Tab navigation) inside the active container.
 * Useful for modal dialogs, bottom sheets, and popovers.
 */
export function trapFocus(container) {
    if (!container) return null;

    const focusableSelectors = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';
    
    function getFocusableElements() {
        return Array.from(container.querySelectorAll(focusableSelectors))
            .filter(el => el.offsetWidth > 0 && el.offsetHeight > 0);
    }

    const focusableElements = getFocusableElements();
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    if (firstFocusable) {
        firstFocusable.focus();
    }

    const keydownHandler = function (e) {
        if (e.key !== 'Tab') return;

        const currentElements = getFocusableElements();
        if (currentElements.length === 0) {
            e.preventDefault();
            return;
        }

        const first = currentElements[0];
        const last = currentElements[currentElements.length - 1];

        if (e.shiftKey) {
            if (document.activeElement === first) {
                last.focus();
                e.preventDefault();
            }
        } else {
            if (document.activeElement === last) {
                first.focus();
                e.preventDefault();
            }
        }
    };

    container.addEventListener('keydown', keydownHandler);

    return function untrap() {
        container.removeEventListener('keydown', keydownHandler);
    };
}
