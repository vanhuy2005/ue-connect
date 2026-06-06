/**
 * Global Livewire request feedback.
 *
 * Keeps async interactions visibly alive without blocking page-level controls.
 */
export function initLivewireRequestFeedback() {
    if (window.__ue_livewire_feedback_initialized) {
        return;
    }

    window.__ue_livewire_feedback_initialized = true;

    let pending = 0;
    let showTimer = null;
    let hideTimer = null;

    const setBusy = () => {
        window.clearTimeout(hideTimer);
        window.clearTimeout(showTimer);

        showTimer = window.setTimeout(() => {
            document.body.classList.add('ue-livewire-busy');
            document.body.setAttribute('aria-busy', 'true');
        }, 80);
    };

    const clearBusy = () => {
        window.clearTimeout(showTimer);
        window.clearTimeout(hideTimer);

        hideTimer = window.setTimeout(() => {
            if (pending > 0) {
                return;
            }

            document.body.classList.remove('ue-livewire-busy');

            if (!document.body.classList.contains('ue-is-navigating')) {
                document.body.removeAttribute('aria-busy');
            }
        }, 120);
    };

    const increment = () => {
        pending += 1;
        setBusy();
    };

    const decrement = () => {
        pending = Math.max(0, pending - 1);

        if (pending === 0) {
            clearBusy();
        }
    };

    const fail = () => {
        decrement();

        window.dispatchEvent(new CustomEvent('ue:toast', {
            detail: {
                type: 'danger',
                message: 'Có lỗi xảy ra. Vui lòng thử lại.',
            },
        }));
    };

    const registerLivewireHook = () => {
        if (!window.Livewire || window.__ue_livewire_request_hook_registered) {
            return;
        }

        window.__ue_livewire_request_hook_registered = true;

        window.Livewire.hook('request', ({ succeed, fail: requestFail }) => {
            increment();

            succeed(() => {
                decrement();
            });

            requestFail(() => {
                fail();
            });
        });
    };

    document.addEventListener('livewire:init', registerLivewireHook);
    registerLivewireHook();

    document.addEventListener('livewire-upload-start', increment);
    document.addEventListener('livewire-upload-finish', decrement);
    document.addEventListener('livewire-upload-cancel', decrement);
    document.addEventListener('livewire-upload-error', fail);

    window.addEventListener('pageshow', () => {
        pending = 0;
        clearBusy();
    });
}
