/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Logout Module
 *
 * Responsibilities:
 * - Prevent accidental logout.
 * - Confirm before submitting logout form.
 *
 * Status: ✅ Production Ready
 * Version: 2.0
 */

const initializeLogout = () => {
    const logoutForms = document.querySelectorAll(
        '[data-logout-form]'
    );

    if (!logoutForms.length) {
        return;
    }

    logoutForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const confirmed = window.confirm(
                'Are you sure you want to log out?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLogout);
} else {
    initializeLogout();
}