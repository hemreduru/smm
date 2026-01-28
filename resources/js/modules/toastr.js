/**
 * Toastr Flash Message Handler
 * 
 * Displays flash messages from server-side session via Toastr.
 * Reads from window.flashMessages object set in the layout.
 */

export function initFlashMessages() {
    if (!window.flashMessages) return;

    const toast = window.toastr || toastr;
    if (!toast) {
        console.warn('Toastr not loaded');
        return;
    }

    // Configure toastr options
    toast.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
        extendedTimeOut: 2000,
    };

    // Show messages based on type
    if (window.flashMessages.success) toast.success(window.flashMessages.success);
    if (window.flashMessages.error) toast.error(window.flashMessages.error);
    if (window.flashMessages.warning) toast.warning(window.flashMessages.warning);
    if (window.flashMessages.info) toast.info(window.flashMessages.info);
}

/**
 * Global helper: Show success toast
 */
export function showSuccess(message) {
    if (window.toastr) toastr.success(message);
}

/**
 * Global helper: Show error toast
 */
export function showError(message) {
    if (window.toastr) toastr.error(message);
}

/**
 * Global helper: Show warning toast
 */
export function showWarning(message) {
    if (window.toastr) toastr.warning(message);
}

/**
 * Global helper: Show info toast
 */
export function showInfo(message) {
    if (window.toastr) toastr.info(message);
}
