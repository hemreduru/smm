
import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '@coreui/coreui/dist/js/coreui.bundle.min.js';

// Alert Utilities
import Swal from 'sweetalert2';
import toastr from 'toastr';

window.Swal = Swal;
window.toastr = toastr;

// Configure Toastr
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "5000",
};

// Global confirm delete handler for SweetAlert2
document.addEventListener('DOMContentLoaded', () => {
    // Intercept forms with data-confirm-delete
    const deleteForms = document.querySelectorAll('form[data-confirm-delete]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm-message') || 'Are you sure you want to delete this item?';
            const confirmButtonText = this.getAttribute('data-confirm-text') || 'Yes, delete it!';

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Handle Session Flashes from Backend
    if (window.flashSuccess) {
        toastr.success(window.flashSuccess);
    }
    if (window.flashError) {
        toastr.error(window.flashError);
    }
    if (window.flashWarning) {
        toastr.warning(window.flashWarning);
    }
    if (window.flashInfo) {
        toastr.info(window.flashInfo);
    }
});
