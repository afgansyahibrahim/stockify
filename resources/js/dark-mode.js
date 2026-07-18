const STORAGE_KEY = 'color-theme';
const root = document.documentElement;

function getPreferredTheme() {
    const savedTheme = localStorage.getItem(STORAGE_KEY);

    if (savedTheme === 'dark' || savedTheme === 'light') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
}

function updateToggle(theme) {
    const toggleButton = document.getElementById('stockify-theme-toggle');
    const darkIcon = document.getElementById('stockify-theme-dark-icon');
    const lightIcon = document.getElementById('stockify-theme-light-icon');
    const isDark = theme === 'dark';

    if (darkIcon) {
        darkIcon.classList.toggle('hidden', isDark);
    }

    if (lightIcon) {
        lightIcon.classList.toggle('hidden', !isDark);
    }

    if (toggleButton) {
        toggleButton.setAttribute('aria-pressed', String(isDark));
        toggleButton.setAttribute(
            'aria-label',
            isDark ? 'Gunakan mode terang' : 'Gunakan mode gelap'
        );
    }
}

function applyTheme(theme, shouldPersist = true) {
    const isDark = theme === 'dark';

    root.classList.toggle('dark', isDark);
    root.style.colorScheme = isDark ? 'dark' : 'light';

    if (shouldPersist) {
        localStorage.setItem(STORAGE_KEY, theme);
    }

    updateToggle(theme);

    document.dispatchEvent(
        new CustomEvent('dark-mode', {
            detail: { theme },
        })
    );
}

function initializeTheme() {
    const theme = getPreferredTheme();
    const toggleButton = document.getElementById('stockify-theme-toggle');

    // The inline script in the dashboard layout has already applied this
    // theme before painting. This keeps the button and JavaScript state aligned.
    applyTheme(theme, false);

    if (!toggleButton) {
        return;
    }

    toggleButton.addEventListener('click', () => {
        const nextTheme = root.classList.contains('dark')
            ? 'light'
            : 'dark';

        applyTheme(nextTheme);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTheme, {
        once: true,
    });
} else {
    initializeTheme();
}