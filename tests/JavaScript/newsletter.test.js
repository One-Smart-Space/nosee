import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { initializeNewsletterForm } from '../../resources/js/newsletter.js';

function createElement() {
    const listeners = new Map();

    return {
        hidden: false,
        addEventListener(event, listener) {
            const eventListeners = listeners.get(event) ?? [];

            eventListeners.push(listener);
            listeners.set(event, eventListeners);
        },
        async dispatch(event, detail = {}) {
            await Promise.all((listeners.get(event) ?? []).map((listener) => listener(detail)));
        },
    };
}

function createHarness(response = { ok: true }) {
    const email = createElement();
    const submitButton = createElement();
    const error = createElement();
    const dialog = createElement();
    const dismissButton = createElement();
    const requests = [];
    const attributes = new Map();
    let resets = 0;
    let modalOpens = 0;
    let modalCloses = 0;
    let emailFocuses = 0;

    error.hidden = true;
    email.focus = () => { emailFocuses++; };
    dialog.open = false;
    dialog.close = () => {
        dialog.open = false;
        modalCloses++;
        dialog.dispatch('close');
    };
    dialog.showModal = () => {
        dialog.open = true;
        modalOpens++;
    };
    dialog.getBoundingClientRect = () => ({ bottom: 300, left: 100, right: 300, top: 100 });
    dialog.querySelector = (selector) => selector === '[data-newsletter-dismiss]' ? dismissButton : null;

    const footer = {
        querySelector(selector) {
            return {
                '[data-newsletter-dialog]': dialog,
                '[data-newsletter-error]': error,
            }[selector] ?? null;
        },
    };
    const form = createElement();

    form.action = '/newsletter/subscribe';
    form.method = 'post';
    form.closest = () => footer;
    form.querySelector = (selector) => ({
        '[data-newsletter-email]': email,
        '[data-newsletter-submit]': submitButton,
    })[selector] ?? null;
    form.removeAttribute = (name) => attributes.delete(name);
    form.reportValidity = () => true;
    form.reset = () => { resets++; };
    form.setAttribute = (name, value) => attributes.set(name, value);

    const fetcher = async (url, options) => {
        requests.push({ options, url });

        return response;
    };
    const formData = { email: 'reader@example.com' };

    initializeNewsletterForm(form, fetcher, () => formData);

    return {
        attributes,
        dialog,
        dismissButton,
        email,
        error,
        form,
        formData,
        get emailFocuses() { return emailFocuses; },
        get modalCloses() { return modalCloses; },
        get modalOpens() { return modalOpens; },
        requests,
        get resets() { return resets; },
        submitButton,
    };
}

test('successful subscription posts the form and opens confirmation', async () => {
    const harness = createHarness();

    await harness.form.dispatch('submit', { preventDefault() {} });

    assert.equal(harness.requests.length, 1);
    assert.equal(harness.requests[0].url, '/newsletter/subscribe');
    assert.equal(harness.requests[0].options.method, 'POST');
    assert.equal(harness.requests[0].options.body, harness.formData);
    assert.equal(harness.requests[0].options.headers.Accept, 'application/json');
    assert.equal(harness.resets, 1);
    assert.equal(harness.modalOpens, 1);
    assert.equal(harness.error.hidden, true);
    assert.equal(harness.submitButton.disabled, false);
    assert.equal(harness.attributes.has('aria-busy'), false);
});

test('failed subscription keeps the form visible and reports an error', async () => {
    const harness = createHarness({ ok: false });

    await harness.form.dispatch('submit', { preventDefault() {} });

    assert.equal(harness.error.hidden, false);
    assert.equal(harness.modalOpens, 0);
    assert.equal(harness.resets, 0);
    assert.equal(harness.submitButton.disabled, false);
});

test('dismiss button and backdrop clicks close the confirmation modal', async () => {
    const harness = createHarness();

    harness.dialog.showModal();
    await harness.dismissButton.dispatch('click');
    assert.equal(harness.modalCloses, 1);
    assert.equal(harness.emailFocuses, 1);

    harness.dialog.showModal();
    await harness.dialog.dispatch('click', { clientX: 50, clientY: 200 });
    assert.equal(harness.modalCloses, 2);

    harness.dialog.showModal();
    await harness.dialog.dispatch('click', { clientX: 200, clientY: 200 });
    assert.equal(harness.modalCloses, 2);
});

test('footer markup contains the requested confirmation and native dialog', () => {
    const template = readFileSync(
        new URL('../../resources/views/components/footer/site-footer.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(template, /<dialog[\s\S]*data-newsletter-dialog/);
    assert.match(template, /<dialog[\s\S]*class="[^"]*m-auto/);
    assert.match(template, /Thank you for subscribing! Look out for NOSEE's newletter in your mailbox/);
    assert.match(template, /data-newsletter-dismiss/);
});
