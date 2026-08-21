/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Unsaved Changes Module
 * Warns users before leaving a page with unsaved form data.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (form guard integration)
 */

const forms = document.querySelectorAll('[data-unsaved-form]');
let isDirty = false;

forms.forEach((form) => {
    form.addEventListener('input', () => {
        isDirty = true;
    });

    form.addEventListener('submit', () => {
        isDirty = false;
    });
});

// Warn before leaving the page
window.addEventListener('beforeunload', (event) => {
    if (isDirty) {
        event.preventDefault();
        event.returnValue = '';
    }
});
