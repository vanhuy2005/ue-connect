/**
 * Reduced Motion Preferences Synchronizer
 * 
 * Synchronizes user motion accessibility preferences with the window context.
 */
export function initReducedMotion() {
    const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    
    function syncPreference(e) {
        window.UEReducedMotion = e.matches;
    }
    
    syncPreference(motionQuery);
    
    try {
        motionQuery.addEventListener('change', syncPreference);
    } catch (err) {
        // Fallback for older browsers
        motionQuery.addListener(syncPreference);
    }
}
