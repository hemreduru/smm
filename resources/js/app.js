import './bootstrap';

// Flash Message Listener
document.addEventListener('DOMContentLoaded', () => {
    // Check for global flash messages if set in layout
    if (window.flashMessages) {
        if (window.flashMessages.success) toastr.success(window.flashMessages.success);
        if (window.flashMessages.error) toastr.error(window.flashMessages.error);
        if (window.flashMessages.warning) toastr.warning(window.flashMessages.warning);
        if (window.flashMessages.info) toastr.info(window.flashMessages.info);
    }

    // Theme Mode Switcher (Single Button Toggle & CSS Swap)
    const themeToggle = document.getElementById('kt_theme_mode_toggle');
    const pluginsLink = document.getElementById('kt_plugins_bundle');
    const styleLink = document.getElementById('kt_style_bundle');

    if (themeToggle) {
        themeToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const currentMode = this.getAttribute('data-kt-value'); // This acts as "next mode"
            const url = "/theme/" + currentMode;
            const icon = this.querySelector('i');

            // 1. Swap Stylesheets
            if (currentMode === 'dark') {
                if (pluginsLink) pluginsLink.href = pluginsLink.href.replace('plugins.bundle.css', 'plugins.dark.bundle.css');
                if (styleLink) styleLink.href = styleLink.href.replace('style.bundle.css', 'style.dark.bundle.css');

                // Update Button State for next click
                this.setAttribute('data-kt-value', 'light');
                if (icon) {
                    icon.classList.remove('bi-moon-fill');
                    icon.classList.add('bi-brightness-high');
                }

                document.documentElement.setAttribute('data-bs-theme', 'dark');

            } else {
                if (pluginsLink) pluginsLink.href = pluginsLink.href.replace('plugins.dark.bundle.css', 'plugins.bundle.css');
                if (styleLink) styleLink.href = styleLink.href.replace('style.dark.bundle.css', 'style.bundle.css');

                // Update Button State for next click
                this.setAttribute('data-kt-value', 'dark');
                if (icon) {
                    icon.classList.remove('bi-brightness-high');
                    icon.classList.add('bi-moon-fill');
                }

                document.documentElement.setAttribute('data-bs-theme', 'light');
            }

            // 2. Persist to Backend (No Reload)
            fetch(url).catch(error => console.error('Error setting theme:', error));
        });
    }
});
