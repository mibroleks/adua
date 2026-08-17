/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Countdown Module
 * Displays a live ticking countdown until the application deadline.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (real countdown integration)
 */

const countdown = document.querySelector('[data-countdown]');

if (countdown) {
    const endValue = countdown.dataset.end;
    const endDate = new Date(endValue).getTime();

    const daysElement = countdown.querySelector('[data-days]');
    const hoursElement = countdown.querySelector('[data-hours]');
    const minutesElement = countdown.querySelector('[data-minutes]');
    const secondsElement = countdown.querySelector('[data-seconds]');

    const pad = (value) => String(value).padStart(2, '0');

    const update = () => {
        const now = Date.now();
        const difference = endDate - now;

        if (difference <= 0) {
            daysElement.textContent = '00';
            hoursElement.textContent = '00';
            minutesElement.textContent = '00';
            secondsElement.textContent = '00';

            // Reload to update status (open/closed)
            window.location.reload();
            return;
        }

        const totalSeconds = Math.floor(difference / 1000);

        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        daysElement.textContent = pad(days);
        hoursElement.textContent = pad(hours);
        minutesElement.textContent = pad(minutes);
        secondsElement.textContent = pad(seconds);
    };

    update();
    setInterval(update, 1000);
}
