/**
 * Global perceived-performance layer for Livewire SPA navigation.
 *
 * Shows a lightweight progress bar immediately while each page/component owns
 * its own layout-specific skeleton state.
 */
export function initPageLoading() {
    if (window.__ue_page_loading_initialized) {
        return;
    }

    window.__ue_page_loading_initialized = true;

    let hideTimer = null;

    const show = () => {
        window.clearTimeout(hideTimer);
        document.body.classList.add('ue-is-navigating');
        document.body.setAttribute('aria-busy', 'true');
    };

    const hide = () => {
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(() => {
            document.body.classList.remove('ue-is-navigating');
            document.body.removeAttribute('aria-busy');
        }, 180);
    };

    document.addEventListener('livewire:navigate', show);
    document.addEventListener('livewire:navigating', show);
    document.addEventListener('livewire:navigated', hide);
    window.addEventListener('pageshow', hide);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.matches('[data-no-page-loading], [wire\\:submit], [wire\\:submit\\.prevent]')) {
            return;
        }

        show();
    }, true);
}
