/*
|--------------------------------------------------------------------------
| YGRACE PORTAL — UNIVERSAL MODAL ENGINE
|--------------------------------------------------------------------------
*/

class PortalModal {
    constructor() {
        this.activeModal = null;
        this.previousFocus = null;

        this.init();
    }

    init() {
        document.addEventListener('click', (event) => {
            const openTrigger = event.target.closest('[data-modal-open]');

            if (openTrigger) {
                event.preventDefault();

                const id = openTrigger.dataset.modalOpen;

                this.open(id);

                return;
            }

            const closeTrigger = event.target.closest('[data-modal-close]');

            if (closeTrigger) {
                event.preventDefault();

                const id =
                    closeTrigger.dataset.modalClose;

                this.close(id);

                return;
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal.id);
            }

            if (
                event.key === 'Tab' &&
                this.activeModal
            ) {
                this.trapFocus(event);
            }
        });
    }


    open(id) {
        const modal =
            document.getElementById(id);

        if (!modal) {
            console.warn(
                `PortalModal: "${id}" was not found.`
            );

            return;
        }

        this.previousFocus =
            document.activeElement;

        this.activeModal = modal;

        modal.classList.add('is-open');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'portal-modal-open'
        );

        const closeButton =
            modal.querySelector(
                '.portal-modal__close'
            );

        requestAnimationFrame(() => {
            closeButton?.focus();
        });
    }


    close(id) {
        const modal =
            document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.remove(
            'is-open'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'portal-modal-open'
        );

        if (this.activeModal === modal) {
            this.activeModal = null;
        }

        this.previousFocus?.focus();

        this.previousFocus = null;
    }


    trapFocus(event) {
        const modal =
            this.activeModal;

        const focusable =
            modal.querySelectorAll(
                'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
            );

        if (!focusable.length) {
            return;
        }

        const first =
            focusable[0];

        const last =
            focusable[focusable.length - 1];

        if (
            event.shiftKey &&
            document.activeElement === first
        ) {
            event.preventDefault();

            last.focus();

            return;
        }

        if (
            !event.shiftKey &&
            document.activeElement === last
        ) {
            event.preventDefault();

            first.focus();
        }
    }
}


document.addEventListener(
    'DOMContentLoaded',
    () => {
        window.portalModal =
            new PortalModal();
    }
);