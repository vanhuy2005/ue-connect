/**
 * Toast Notification System Manager
 * 
 * Exposes window.UEToast.show() supporting stacking, auto-dismiss,
 * custom actions (e.g., Undo), and accessibility.
 */
export function initToastSystem() {
    window.UEToast = {
        show: function (options = {}) {
            const {
                type = 'info', // 'success', 'danger', 'info'
                message = '',
                actionLabel = '',
                onAction = null,
                duration = 4000
            } = options;

            // Select or build container
            let region = document.querySelector('.ue-toast-region');
            if (!region) {
                region = document.createElement('div');
                region.className = 'ue-toast-region';
                document.body.appendChild(region);
            }

            // Stack max 3 toasts at any time
            const currentToasts = region.querySelectorAll('.ue-toast');
            if (currentToasts.length >= 3) {
                currentToasts[0].remove();
            }

            // Build individual toast
            const toast = document.createElement('div');
            toast.className = `ue-toast ue-toast--${type}`;
            toast.setAttribute('role', type === 'danger' ? 'alert' : 'status');
            toast.setAttribute('aria-live', 'polite');

            // Message element
            const textSpan = document.createElement('span');
            textSpan.textContent = message;
            toast.appendChild(textSpan);

            // Optional action callback
            if (actionLabel && onAction) {
                const actionBtn = document.createElement('button');
                actionBtn.type = 'button';
                actionBtn.className = 'ue-toast__action';
                actionBtn.textContent = actionLabel;
                actionBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    onAction();
                    toast.remove();
                });
                toast.appendChild(actionBtn);
            }

            region.appendChild(toast);

            // Handle auto-dismissal
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 300ms ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    };
}
