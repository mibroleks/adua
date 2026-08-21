/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * User Menu Module
 *
 * Responsibilities:
 * - Toggle authenticated user menu.
 * - Maintain accessible ARIA state.
 * - Close when clicking outside.
 * - Close when pressing Escape.
 * - Restore focus to the toggle after closing.
 *
 * Status: ✅ Production Ready
 * Version: 2.0
 */

const initializeUserMenu = () => {
    const toggle = document.querySelector('[data-user-menu-toggle]');
    const menu = document.querySelector('[data-user-menu]');

    if (!toggle || !menu) {
        return;
    }

    const closeMenu = ({ restoreFocus = false } = {}) => {
        toggle.setAttribute('aria-expanded', 'false');
        menu.hidden = true;

        if (restoreFocus) {
            toggle.focus();
        }
    };

    const openMenu = () => {
        toggle.setAttribute('aria-expanded', 'true');
        menu.hidden = false;
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();

        const isOpen =
            toggle.getAttribute('aria-expanded') === 'true';

        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    menu.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', () => {
        closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu({ restoreFocus: true });
        }
    });

    closeMenu();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeUserMenu);
} else {
    initializeUserMenu();
}