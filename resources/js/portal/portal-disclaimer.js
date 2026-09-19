/*
|--------------------------------------------------------------------------
| Admissions Portal Disclaimer Controller
|--------------------------------------------------------------------------
| File Path:
| resources/js/portal/portal-disclaimer.js
|
| Purpose:
| Controls the official admissions disclaimer.
|
| Updated:
| - Stores acknowledgement timestamp in localStorage.
| - Shows disclaimer again after configured repeat interval.
| - Prevents accidental dismissal.
| - Maintains keyboard focus inside the dialog.
| - Restores focus after dismissal.
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {
    const disclaimer = document.querySelector('[data-disclaimer]');
    if (!disclaimer) return;

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */
    const enabled = disclaimer.dataset.enabled === 'true';
    if (!enabled) return;

    const storageKey = disclaimer.dataset.storageKey || 'admissions_portal_disclaimer_acknowledged';

    // Repeat interval options (milliseconds)
    // Uncomment ONE of these depending on the desired frequency:

    // const repeatInterval = 60 * 1000;             // per minute (60 seconds)
    // const repeatInterval = 60 * 60 * 1000;        // per hour (3600 seconds)
    const repeatInterval = 24 * 60 * 60 * 1000;   // per day (86400 seconds)
    // const repeatInterval = 7 * 24 * 60 * 60 * 1000; // per week (default: 7 days)

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */
    const continueButton = disclaimer.querySelector('[data-disclaimer-continue]');
    const dialog = disclaimer.querySelector('[data-disclaimer-dialog]');
    if (!continueButton || !dialog) return;

    /*
    |--------------------------------------------------------------------------
    | Existing Acknowledgement
    |--------------------------------------------------------------------------
    */
    let shouldShow = true;
    try {
        const lastAck = parseInt(localStorage.getItem(storageKey), 10);
        if (!isNaN(lastAck)) {
            const elapsed = Date.now() - lastAck;
            shouldShow = elapsed > repeatInterval;
        }
    } catch (error) {
        shouldShow = true;
    }

    if (!shouldShow) return;

    /*
    |--------------------------------------------------------------------------
    | Focus Management
    |--------------------------------------------------------------------------
    */
    let previouslyFocusedElement = document.activeElement;

    /*
    |--------------------------------------------------------------------------
    | Open
    |--------------------------------------------------------------------------
    */
    document.body.classList.add('portal-disclaimer-open');
    disclaimer.hidden = false;

    requestAnimationFrame(function () {
        dialog.focus();
    });

    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */
    function closeDisclaimer() {
        try {
            localStorage.setItem(storageKey, Date.now().toString());
        } catch (error) {
            // Storage failure fallback
        }

        disclaimer.classList.add('is-closing');
        document.body.classList.remove('portal-disclaimer-open');

        window.setTimeout(function () {
            disclaimer.hidden = true;
            disclaimer.classList.remove('is-closing');

            if (previouslyFocusedElement && typeof previouslyFocusedElement.focus === 'function') {
                previouslyFocusedElement.focus();
            }
        }, 180);
    }

    continueButton.addEventListener('click', closeDisclaimer);

    /*
    |--------------------------------------------------------------------------
    | Keyboard Focus Trap
    |--------------------------------------------------------------------------
    */
    document.addEventListener('keydown', function (event) {
        if (disclaimer.hidden || event.key !== 'Tab') return;

        const focusableElements = disclaimer.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (!focusableElements.length) return;

        const first = focusableElements[0];
        const last = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
});
