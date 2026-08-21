/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Navigation Module
 *
 * Responsibilities:
 * - Mobile navigation toggle.
 * - Close mobile navigation after selecting a link.
 * - Close mobile navigation with Escape.
 * - Maintain accessible ARIA state.
 * - Track authenticated header scroll state.
 *
 * Status: ✅ Production Ready
 * Version: 2.0
 */

const initializeNavigation = () => {
    const toggle = document.querySelector(
        '[data-mobile-menu-toggle]'
    );

    const menu = document.querySelector(
        '[data-mobile-menu]'
    );

    const header = document.querySelector(
        '#site-header, .portal-topbar'
    );

    const closeMobileMenu = ({
        restoreFocus = false
    } = {}) => {
        if (!toggle || !menu) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'false');
        menu.hidden = true;

        document.body.classList.remove(
            'mobile-menu-open'
        );

        if (restoreFocus) {
            toggle.focus();
        }
    };

    const openMobileMenu = () => {
        if (!toggle || !menu) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'true');
        menu.hidden = false;

        document.body.classList.add(
            'mobile-menu-open'
        );
    };

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const isOpen =
                toggle.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        menu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                closeMobileMenu();
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMobileMenu({
                    restoreFocus: true
                });
            }
        });

        closeMobileMenu();
    }

    if (header) {
        const updateHeader = () => {
            header.classList.toggle(
                'is-scrolled',
                window.scrollY > 12
            );
        };

        updateHeader();

        window.addEventListener(
            'scroll',
            updateHeader,
            { passive: true }
        );
    }
};

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeNavigation
    );
} else {
    initializeNavigation();
}