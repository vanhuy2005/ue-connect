/**
 * Popover / Dropdown Menu Controller
 * 
 * Supports click toggle, outside click dismissal, and Escape close.
 */
export function initDropdowns() {
    // Clean listener for dropdown triggers
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-ue-menu-trigger]');
        
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            
            const menuId = trigger.getAttribute('data-ue-menu-trigger');
            const menu = document.querySelector(`[data-ue-menu="${menuId}"]`);
            
            if (!menu) return;
            
            const isOpen = menu.getAttribute('data-state') === 'open';
            
            closeAllDropdowns();
            
            if (!isOpen) {
                menu.setAttribute('data-state', 'open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        } else {
            // Close if clicked outside
            const clickedInsideMenu = e.target.closest('[data-ue-menu]');
            if (!clickedInsideMenu) {
                closeAllDropdowns();
            }
        }
    });

    // Close on Escape keypress
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
}

export function closeAllDropdowns() {
    document.querySelectorAll('[data-ue-menu]').forEach(menu => {
        menu.setAttribute('data-state', 'closed');
    });
    document.querySelectorAll('[data-ue-menu-trigger]').forEach(trigger => {
        trigger.setAttribute('aria-expanded', 'false');
    });
}
