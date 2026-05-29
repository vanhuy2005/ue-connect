/**
 * High-Fidelity Optimistic Action Helper
 * 
 * Instantly increments count values and toggles CSS class indicators
 * on standard buttons, using micro-animations before requests complete.
 * Fully compatible with Livewire's DOM diffing (re-renders override DOM).
 */
export function initOptimisticActions() {
    window.UEOptimistic = {
        toggle: function (button, options = {}) {
            if (!button) return;

            const {
                selectedClass = 'ue-action-button--selected',
                countSelector = '[data-count]',
                delta = 1
            } = options;

            const isSelected = button.classList.contains(selectedClass);
            const countEl = button.querySelector(countSelector);
            let currentVal = 0;

            if (countEl) {
                currentVal = parseInt(countEl.textContent.trim()) || 0;
            }

            // Optimistic Toggle
            if (isSelected) {
                button.classList.remove(selectedClass);
                button.setAttribute('aria-pressed', 'false');
                if (countEl) {
                    countEl.textContent = Math.max(0, currentVal - delta);
                }
            } else {
                button.classList.add(selectedClass);
                button.setAttribute('aria-pressed', 'true');
                if (countEl) {
                    countEl.textContent = currentVal + delta;
                }
            }

            // Pop Scale Micro-animation (Threads-like click feedback)
            const icon = button.querySelector('svg');
            if (icon) {
                icon.style.transform = 'scale(var(--motion-like-scale, 1.14))';
                icon.style.transition = 'transform 120ms cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                setTimeout(() => {
                    icon.style.transform = '';
                }, 120);
            }
        }
    };
}
