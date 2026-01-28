/**
 * Axios HTTP Client Configuration
 * 
 * Sets up axios with:
 * - CSRF token header
 * - XMLHttpRequest header for Laravel detection
 */

export function initAxios() {
    if (!window.axios) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
    
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
