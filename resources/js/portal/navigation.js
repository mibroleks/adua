/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Navigation Module
 * Handles mobile menu toggle and header scroll state.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (responsive navigation integration)
 */

const toggle = document.querySelector('[data-mobile-menu-toggle]');
const menu = document.querySelector('[data-mobile-menu]');
const header = document.querySelector('#site-header');

if (toggle && menu) {
    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', String(!isOpen));
        menu.hidden = isOpen;

        document.body.classList.toggle('mobile-menu-open', !isOpen);
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            toggle.setAttribute('aria-expanded', 'false');
            menu.hidden = true;
            document.body.classList.remove('mobile-menu-open');
        });
    });
}

if (header) {
    const updateHeader = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    updateHeader();

    window.addEventListener('scroll', updateHeader, { passive: true });
}
