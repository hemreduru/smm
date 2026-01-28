/**
 * Main Application Entry Point
 * 
 * Imports and initializes all modules.
 */

import './bootstrap';

// Module imports
import { initFlashMessages, showSuccess, showError, showWarning, showInfo } from './modules/toastr';
import { initThemeSwitcher } from './modules/theme';
import { initConfirmations, confirmAction } from './modules/confirm';
import { initAxios } from './modules/axios';

// Initialize all modules on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initFlashMessages();
    initThemeSwitcher();
    initConfirmations();
    initAxios();
});

// Expose global helpers
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;
window.confirmAction = confirmAction;
