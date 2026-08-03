export function initializeNewsletterForm(
    form,
    fetcher = globalThis.fetch,
    formDataFactory = (target) => new FormData(target),
) {
    // Resolve the controls from the same footer so multiple instances stay independent.
    const footer = form.closest('[data-site-footer]');
    const email = form.querySelector('[data-newsletter-email]');
    const submitButton = form.querySelector('[data-newsletter-submit]');
    const error = footer?.querySelector('[data-newsletter-error]');
    const dialog = footer?.querySelector('[data-newsletter-dialog]');
    const dismissButton = dialog?.querySelector('[data-newsletter-dismiss]');
    let submitting = false;

    if (!email || !submitButton || !error || !dialog || !dismissButton) {
        return null;
    }

    // Submit validated form data to Laravel before showing confirmation.
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (submitting || !form.reportValidity()) {
            return;
        }

        submitting = true;
        submitButton.disabled = true;
        error.hidden = true;
        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetcher(form.action, {
                method: form.method.toUpperCase(),
                body: formDataFactory(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Newsletter subscription failed.');
            }

            form.reset();
            dialog.showModal();
        } catch {
            error.hidden = false;
        } finally {
            submitting = false;
            submitButton.disabled = false;
            form.removeAttribute('aria-busy');
        }
    });

    // Support the dismissal button, native Escape handling, and backdrop clicks.
    dismissButton.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        const bounds = dialog.getBoundingClientRect();
        const clickedBackdrop = event.clientX < bounds.left
            || event.clientX > bounds.right
            || event.clientY < bounds.top
            || event.clientY > bounds.bottom;

        if (clickedBackdrop) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => email.focus());

    return { dialog };
}

if (typeof document !== 'undefined') {
    // Initialize each enabled newsletter form independently.
    document.querySelectorAll('[data-newsletter-form]').forEach((form) => {
        initializeNewsletterForm(form);
    });
}
