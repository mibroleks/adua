/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Progress Module
 * Enhances progress indicators with animations and tooltips.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (animated progress integration)
 */

const progressItems = document.querySelectorAll('[data-progress-item]');

if (progressItems.length > 0) {
    progressItems.forEach((item) => {
        const marker = item.querySelector('[data-progress-marker]');
        const tooltip = item.querySelector('[data-progress-tooltip]');

        // Animate marker when entering viewport
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        marker.classList.add('animate-progress');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.5 }
        );
        observer.observe(item);

        // Tooltip on hover/focus
        if (tooltip) {
            item.addEventListener('mouseenter', () => {
                tooltip.hidden = false;
            });
            item.addEventListener('mouseleave', () => {
                tooltip.hidden = true;
            });
            item.addEventListener('focusin', () => {
                tooltip.hidden = false;
            });
            item.addEventListener('focusout', () => {
                tooltip.hidden = true;
            });
        }
    });
}
