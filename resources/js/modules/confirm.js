/**
 * Declarative SweetAlert2 Confirmation System
 * 
 * Usage (buttons/links):
 * <button data-confirm="true" 
 *         data-confirm-title="Delete?" 
 *         data-confirm-text="This cannot be undone"
 *         data-confirm-button="Yes, delete"
 *         data-confirm-cancel="Cancel"
 *         data-confirm-method="delete"
 *         data-confirm-url="/items/1">Delete</button>
 *
 * Usage (forms):
 * <form data-confirm="true" data-confirm-title="Submit?">...</form>
 */

export function initConfirmations() {
    // Handle click events on confirmation elements
    document.addEventListener('click', handleClickConfirmation);
    
    // Handle form submissions with confirmation
    document.addEventListener('submit', handleFormConfirmation);
}

function handleClickConfirmation(e) {
    const trigger = e.target.closest('[data-confirm="true"]');
    if (!trigger) return;

    // Skip if it's a form (forms are handled on submit)
    if (trigger.tagName === 'FORM') return;

    e.preventDefault();
    e.stopPropagation();

    showConfirmation(trigger, false);
}

function handleFormConfirmation(e) {
    const form = e.target.closest('form[data-confirm="true"]');
    if (!form) return;

    // Check if already confirmed
    if (form.dataset.confirmed === 'true') {
        form.dataset.confirmed = 'false';
        return;
    }

    e.preventDefault();
    showConfirmation(form, true);
}

function showConfirmation(element, isForm) {
    const Swal = window.Swal;
    if (!Swal) {
        console.error('SweetAlert2 not loaded');
        if (isForm) element.submit();
        return;
    }

    const config = getConfirmConfig(element);

    Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        showCancelButton: true,
        confirmButtonColor: config.confirmColor,
        cancelButtonColor: config.cancelColor,
        confirmButtonText: config.confirmButton,
        cancelButtonText: config.cancelButton,
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            if (isForm) {
                element.dataset.confirmed = 'true';
                element.submit();
            } else {
                executeAction(element);
            }
        }
    });
}

function getConfirmConfig(element) {
    const defaults = window.confirmDefaults || {
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        confirmButton: 'Yes, proceed',
        cancelButton: 'Cancel',
    };

    return {
        title: element.dataset.confirmTitle || defaults.title,
        text: element.dataset.confirmText || defaults.text,
        confirmButton: element.dataset.confirmButton || defaults.confirmButton,
        cancelButton: element.dataset.confirmCancel || defaults.cancelButton,
        icon: element.dataset.confirmIcon || 'warning',
        confirmColor: element.dataset.confirmColor || '#3085d6',
        cancelColor: element.dataset.cancelColor || '#d33',
    };
}

function executeAction(element) {
    const url = element.dataset.confirmUrl || element.getAttribute('href');
    const method = (element.dataset.confirmMethod || 'get').toLowerCase();

    if (!url) {
        console.error('No URL specified for confirmation action');
        return;
    }

    if (method === 'get') {
        window.location.href = url;
        return;
    }

    // For non-GET methods, create and submit a form
    submitMethodForm(url, method);
}

function submitMethodForm(url, method) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        appendHiddenInput(form, '_token', csrfToken.getAttribute('content'));
    }

    // Method spoofing for DELETE, PUT, PATCH
    if (['delete', 'put', 'patch'].includes(method)) {
        appendHiddenInput(form, '_method', method.toUpperCase());
    }

    document.body.appendChild(form);
    form.submit();
}

function appendHiddenInput(form, name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
}

/**
 * Programmatic confirmation dialog
 * 
 * @param {Object} options - { title, text, icon, confirmButton, cancelButton }
 * @returns {Promise<boolean>}
 */
export function confirmAction(options = {}) {
    const Swal = window.Swal;
    if (!Swal) {
        console.error('SweetAlert2 not loaded');
        return Promise.resolve(false);
    }

    const defaults = window.confirmDefaults || {
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        confirmButton: 'Yes, proceed',
        cancelButton: 'Cancel',
    };

    return Swal.fire({
        title: options.title || defaults.title,
        text: options.text || defaults.text,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: options.confirmColor || '#3085d6',
        cancelButtonColor: options.cancelColor || '#d33',
        confirmButtonText: options.confirmButton || defaults.confirmButton,
        cancelButtonText: options.cancelButton || defaults.cancelButton,
        reverseButtons: true,
    }).then((result) => result.isConfirmed);
}
