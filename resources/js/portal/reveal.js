/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Reveal Module
 * Handles scroll-based reveal animations for elements marked with [data-reveal].
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (intersection observer integration)
 */

const revealElements = document.querySelectorAll('[data-reveal]');

if (revealElements.length) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-portal-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.15,
            rootMargin: '0px 0px -10% 0px',
        }
    );

    revealElements.forEach((el) => observer.observe(el));
}
