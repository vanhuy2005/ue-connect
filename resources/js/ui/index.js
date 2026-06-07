/**
 * UEConnect UI Systems Entrypoint
 * 
 * Central entrypoint that imports and coordinates initialization for all
 * interactive client systems, including Livewire SPA navigation support.
 */
import { initDropdowns } from './dropdown';
import { initBottomSheets } from './bottom-sheet';
import { initToastSystem } from './toast';
import { initOptimisticActions } from './optimistic-actions';
import { initLayoutGuards } from './layout-guards';
import { initReducedMotion } from './reduced-motion';
import { initPageLoading } from './page-loading';
import { initLivewireRequestFeedback } from './livewire-request-feedback';
import { initCustomSelects } from './custom-select';

function initAllUI() {
    if (window.__ue_ui_initialized) {
        initCustomSelects();

        // Scan layouts in development on navigation
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            setTimeout(() => {
                if (window.UELayoutGuards) {
                    window.UELayoutGuards.scan();
                }
            }, 1000);
        }
        return;
    }

    window.__ue_ui_initialized = true;

    initReducedMotion();
    initDropdowns();
    initBottomSheets();
    initToastSystem();
    initOptimisticActions();
    initPageLoading();
    initLivewireRequestFeedback();
    initCustomSelects();
    initLayoutGuards();

    // Trigger dev scanner automatically in development
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        setTimeout(() => {
            if (window.UELayoutGuards) {
                window.UELayoutGuards.scan();
            }
        }, 1000);
    }
}

// Initial page load trigger
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllUI);
} else {
    initAllUI();
}

// Essential Livewire SPA navigated morph listener
document.addEventListener('livewire:navigated', initAllUI);
