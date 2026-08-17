/**
 * Ygrace Tech
 * University Admissions Portal
 *
 * Carousel Module
 * Handles hero/programme highlight carousel functionality.
 *
 * Status: ✅ Production Ready
 * Version: 1.0 (basic carousel integration)
 */

const carousels = document.querySelectorAll('[data-carousel]');

carousels.forEach((carousel) => {
    const slides = carousel.querySelectorAll('[data-slide]');
    const prevButton = carousel.querySelector('[data-carousel-prev]');
    const nextButton = carousel.querySelector('[data-carousel-next]');
    const indicators = carousel.querySelectorAll('[data-carousel-indicator]');

    let currentIndex = 0;

    const showSlide = (index) => {
        slides.forEach((slide, i) => {
            slide.hidden = i !== index;
            slide.classList.toggle('active', i === index);
        });

        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });

        currentIndex = index;
    };

    const nextSlide = () => {
        const newIndex = (currentIndex + 1) % slides.length;
        showSlide(newIndex);
    };

    const prevSlide = () => {
        const newIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(newIndex);
    };

    if (nextButton) nextButton.addEventListener('click', nextSlide);
    if (prevButton) prevButton.addEventListener('click', prevSlide);

    indicators.forEach((indicator, i) => {
        indicator.addEventListener('click', () => showSlide(i));
    });

    // Auto-play every 6 seconds
    setInterval(nextSlide, 6000);

    // Initialize
    showSlide(0);
});
