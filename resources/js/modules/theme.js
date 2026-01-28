/**
 * Theme Mode Switcher
 * 
 * Handles dark/light mode toggle with:
 * - CSS bundle swapping
 * - Bootstrap theme attribute
 * - Backend persistence
 */

export function initThemeSwitcher() {
    const themeToggle = document.getElementById('kt_theme_mode_toggle');
    const pluginsLink = document.getElementById('kt_plugins_bundle');
    const styleLink = document.getElementById('kt_style_bundle');

    if (!themeToggle) return;

    themeToggle.addEventListener('click', function (e) {
        e.preventDefault();
        
        const currentMode = this.getAttribute('data-kt-value');
        const icon = this.querySelector('i');

        if (currentMode === 'dark') {
            applyDarkMode(pluginsLink, styleLink);
            updateToggleButton(this, icon, 'light', 'bi-moon-fill', 'bi-brightness-high');
        } else {
            applyLightMode(pluginsLink, styleLink);
            updateToggleButton(this, icon, 'dark', 'bi-brightness-high', 'bi-moon-fill');
        }

        // Persist to backend
        persistTheme(currentMode);
    });
}

function applyDarkMode(pluginsLink, styleLink) {
    if (pluginsLink) {
        pluginsLink.href = pluginsLink.href.replace('plugins.bundle.css', 'plugins.dark.bundle.css');
    }
    if (styleLink) {
        styleLink.href = styleLink.href.replace('style.bundle.css', 'style.dark.bundle.css');
    }
    document.documentElement.setAttribute('data-bs-theme', 'dark');
}

function applyLightMode(pluginsLink, styleLink) {
    if (pluginsLink) {
        pluginsLink.href = pluginsLink.href.replace('plugins.dark.bundle.css', 'plugins.bundle.css');
    }
    if (styleLink) {
        styleLink.href = styleLink.href.replace('style.dark.bundle.css', 'style.bundle.css');
    }
    document.documentElement.setAttribute('data-bs-theme', 'light');
}

function updateToggleButton(button, icon, nextValue, removeClass, addClass) {
    button.setAttribute('data-kt-value', nextValue);
    if (icon) {
        icon.classList.remove(removeClass);
        icon.classList.add(addClass);
    }
}

function persistTheme(theme) {
    fetch(`/theme/${theme}`).catch(error => {
        console.error('Error persisting theme:', error);
    });
}
