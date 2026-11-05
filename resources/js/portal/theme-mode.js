/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Personal Theme Mode Module
 *
 * Responsibilities:
 * - Switch between light and dark portal modes.
 * - Save the authenticated user's preference.
 * - Refresh institutional CSS variables after switching.
 * - Maintain accessible selected states.
 * - Preserve the institution's branding preset.
 *
 * Version: 1.0
 */

const initializeThemeMode = () => {
    const selector = document.querySelector(
        '[data-theme-preference]'
    );

    const stylesheet = document.querySelector(
        '[data-portal-theme-stylesheet]'
    );

    if (!selector || !stylesheet) {
        return;
    }

    const buttons = selector.querySelectorAll(
        '[data-theme-mode]'
    );

    const endpoint = selector.dataset.themePreferenceUrl;

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.getAttribute('content');

    const baseStylesheetUrl = stylesheet.dataset.baseUrl;

    if (
        !endpoint ||
        !csrfToken ||
        !baseStylesheetUrl ||
        !buttons.length
    ) {
        return;
    }

    let isSaving = false;

    const updateSelectedMode = (mode) => {
        buttons.forEach((button) => {
            const selected =
                button.dataset.themeMode === mode;

            button.classList.toggle(
                'is-active',
                selected
            );

            button.setAttribute(
                'aria-pressed',
                selected ? 'true' : 'false'
            );
        });
    };

    const setButtonState = (disabled) => {
        buttons.forEach((button) => {
            button.disabled = disabled;
        });
    };

    const refreshStylesheet = (mode) => {
        return new Promise((resolve, reject) => {
            const separator = baseStylesheetUrl.includes('?')
                ? '&'
                : '?';

            const refreshUrl =
                `${baseStylesheetUrl}${separator}refresh=${Date.now()}`;

            const previousUrl = stylesheet.href;

            const handleLoad = () => {
                cleanup();
                resolve();
            };

            const handleError = () => {
                cleanup();
                stylesheet.href = previousUrl;
                reject(new Error('Unable to refresh portal theme.'));
            };

            const cleanup = () => {
                stylesheet.removeEventListener('load', handleLoad);
                stylesheet.removeEventListener('error', handleError);
            };

            stylesheet.addEventListener('load', handleLoad);
            stylesheet.addEventListener('error', handleError);

            stylesheet.href = refreshUrl;
        });
    };

    const saveThemePreference = async (mode) => {
        if (isSaving) {
            return;
        }

        isSaving = true;
        setButtonState(true);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',

                credentials: 'same-origin',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },

                body: JSON.stringify({
                    mode: mode,
                }),
            });

            if (!response.ok) {
                throw new Error(
                    'Unable to save your appearance preference.'
                );
            }

            const result = await response.json();

            if (!result.success || result.mode !== mode) {
                throw new Error(
                    'The appearance preference could not be confirmed.'
                );
            }

            await refreshStylesheet(mode);

            updateSelectedMode(mode);

        } catch (error) {
            console.error(
                'Portal theme preference error:',
                error
            );

            window.dispatchEvent(
                new CustomEvent('portal:theme-error', {
                    detail: {
                        message: error.message,
                    },
                })
            );

        } finally {
            isSaving = false;
            setButtonState(false);
        }
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.themeMode;

            if (!['light', 'dark'].includes(mode)) {
                return;
            }

            if (button.getAttribute('aria-pressed') === 'true') {
                return;
            }

            saveThemePreference(mode);
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeThemeMode
    );
} else {
    initializeThemeMode();
}