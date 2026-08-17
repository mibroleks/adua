/**
 * File Upload Enhancer
 * Ygrace Tech Admissions Portal
 *
 * Adds filename display to custom file inputs.
 * Works globally for all `.admission-file-input` elements.
 *
 * Status: ✅ Production Ready
 * Version: 1.0
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.admission-file-input').forEach(input => {
        input.addEventListener('change', () => {
            const targetId = input.dataset.filenameTarget;
            const target = document.getElementById(targetId);

            if (!target) return;

            if (input.files && input.files.length > 0) {
                target.textContent = input.files[0].name;
                target.classList.add('has-file');
            } else {
                target.textContent = 'No file selected';
                target.classList.remove('has-file');
            }
        });
    });
});
