/**
 * Local Development Layout Diagnostic Guards
 * 
 * Inspects DOM for elements causing horizontal page scrollbars or clipped popovers.
 */
export function initLayoutGuards() {
    window.UELayoutGuards = {
        scan: function () {
            const docWidth = document.documentElement.clientWidth || window.innerWidth;
            const overflowElements = [];
            
            // 1. Scan for horizontal viewport overflows
            document.querySelectorAll('*').forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.right > docWidth && el.offsetWidth > 0) {
                    overflowElements.push({
                        element: el,
                        width: rect.width,
                        right: rect.right
                    });
                }
            });

            if (overflowElements.length > 0) {
                console.warn(`[UELayoutGuards] ${overflowElements.length} elements are overflowing the horizontal viewport width of ${docWidth}px:`);
                overflowElements.forEach((item, idx) => {
                    console.warn(`  #${idx + 1}:`, item.element, `Width: ${item.width}px, Right: ${item.right}px`);
                });
            }

            // 2. Scan for popover menus clipped by overflow hidden parents
            const activeMenus = document.querySelectorAll('[data-ue-menu]');
            activeMenus.forEach(menu => {
                let parent = menu.parentElement;
                while (parent && parent !== document.body) {
                    const style = window.getComputedStyle(parent);
                    if (style.overflow === 'hidden' || style.overflowX === 'hidden' || style.overflowY === 'hidden') {
                        console.warn('[UELayoutGuards] Popover menu might be clipped by parent with overflow:hidden:', {
                            menu: menu,
                            parent: parent
                        });
                        break;
                    }
                    parent = parent.parentElement;
                }
            });
        }
    };
}
