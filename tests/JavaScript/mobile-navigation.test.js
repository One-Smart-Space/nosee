import assert from 'node:assert/strict';
import test from 'node:test';

import { initializeMobileNavigation } from '../../resources/js/mobile-navigation.js';

function createElement(documentObject) {
    const attributes = new Map();
    const listeners = new Map();

    return {
        hidden: false,
        addEventListener: (event, listener) => listeners.set(event, listener),
        click: () => listeners.get('click')?.({ preventDefault() {} }),
        closest: () => null,
        focus() {
            documentObject.activeElement = this;
        },
        getAttribute: (name) => attributes.get(name),
        hasAttribute: (name) => attributes.has(name),
        setAttribute: (name, value) => attributes.set(name, value),
    };
}

function createHarness(activeSubmenu = null) {
    const documentListeners = new Map();
    const mediaListeners = new Map();
    const submenus = new Map();
    const documentObject = {
        activeElement: null,
        body: { style: { overflow: 'auto' } },
        addEventListener: (event, listener) => documentListeners.set(event, listener),
        getElementById: (id) => submenus.get(id),
    };

    const menuButton = createElement(documentObject);
    const drawer = createElement(documentObject);
    const overlay = createElement(documentObject);
    const openIcon = createElement(documentObject);
    const closeIcon = createElement(documentObject);
    const firstLink = createElement(documentObject);
    const lastLink = createElement(documentObject);
    const navigationLinks = [firstLink, lastLink];
    const submenuButtons = ['about', 'research', 'data'].map((name) => {
        const submenu = createElement(documentObject);
        const button = createElement(documentObject);

        submenu.hidden = true;
        button.setAttribute('aria-controls', `${name}-submenu`);
        button.setAttribute('aria-expanded', 'false');
        if (name === activeSubmenu) {
            button.setAttribute('data-mobile-active-submenu', '');
        }
        submenus.set(`${name}-submenu`, submenu);

        return button;
    });
    const focusables = [firstLink, ...submenuButtons, lastLink];
    const selectors = new Map([
        ['[data-mobile-menu-button]', menuButton],
        ['[data-mobile-drawer]', drawer],
        ['[data-mobile-overlay]', overlay],
        ['[data-mobile-menu-open-icon]', openIcon],
        ['[data-mobile-menu-close-icon]', closeIcon],
    ]);

    drawer.hidden = true;
    overlay.hidden = true;
    closeIcon.hidden = true;
    drawer.contains = (element) => focusables.includes(element);
    drawer.querySelectorAll = (selector) => selector === 'a[href]' ? navigationLinks : focusables;

    const navigation = {
        querySelector: (selector) => selectors.get(selector),
        querySelectorAll: (selector) => selector === '[data-mobile-submenu-button]' ? submenuButtons : [],
    };
    const windowObject = {
        matchMedia: (query) => {
            assert.equal(query, '(min-width: 64rem)');

            return {
                addEventListener: (event, listener) => mediaListeners.set(event, listener),
            };
        },
    };

    initializeMobileNavigation(navigation, documentObject, windowObject);

    return {
        documentObject,
        documentListeners,
        drawer,
        firstLink,
        lastLink,
        menuButton,
        mediaListeners,
        openIcon,
        closeIcon,
        overlay,
        submenuButtons,
        submenus,
    };
}

test('menu button manages drawer visibility, focus, icons, and body scrolling', () => {
    const harness = createHarness();

    harness.menuButton.click();
    assert.equal(harness.menuButton.getAttribute('aria-expanded'), 'true');
    assert.equal(harness.menuButton.getAttribute('aria-label'), 'Close navigation menu');
    assert.equal(harness.drawer.hidden, false);
    assert.equal(harness.overlay.hidden, false);
    assert.equal(harness.documentObject.body.style.overflow, 'hidden');
    assert.equal(harness.openIcon.hidden, true);
    assert.equal(harness.closeIcon.hidden, false);
    assert.equal(harness.documentObject.activeElement, harness.firstLink);

    harness.menuButton.click();
    assert.equal(harness.menuButton.getAttribute('aria-expanded'), 'false');
    assert.equal(harness.drawer.hidden, true);
    assert.equal(harness.overlay.hidden, true);
    assert.equal(harness.documentObject.body.style.overflow, 'auto');
    assert.equal(harness.documentObject.activeElement, harness.menuButton);
});

test('Escape, overlay, and navigation links close the drawer', () => {
    const harness = createHarness();

    harness.menuButton.click();
    harness.documentListeners.get('keydown')({ key: 'Escape', preventDefault() {} });
    assert.equal(harness.drawer.hidden, true);

    harness.menuButton.click();
    harness.overlay.click();
    assert.equal(harness.drawer.hidden, true);

    harness.menuButton.click();
    harness.firstLink.click();
    assert.equal(harness.drawer.hidden, true);
});

test('focus remains within the open drawer', () => {
    const harness = createHarness();
    let prevented = false;

    harness.menuButton.click();
    harness.lastLink.focus();
    harness.documentListeners.get('keydown')({
        key: 'Tab',
        shiftKey: false,
        preventDefault: () => { prevented = true; },
    });
    assert.equal(prevented, true);
    assert.equal(harness.documentObject.activeElement, harness.firstLink);

    prevented = false;
    harness.documentListeners.get('keydown')({
        key: 'Tab',
        shiftKey: true,
        preventDefault: () => { prevented = true; },
    });
    assert.equal(prevented, true);
    assert.equal(harness.documentObject.activeElement, harness.lastLink);

    prevented = false;
    harness.menuButton.focus();
    harness.documentListeners.get('keydown')({
        key: 'Tab',
        shiftKey: false,
        preventDefault: () => { prevented = true; },
    });
    assert.equal(prevented, true);
    assert.equal(harness.documentObject.activeElement, harness.firstLink);
});

test('only one submenu opens and closing the drawer resets them', () => {
    const harness = createHarness();
    const [aboutButton, researchButton] = harness.submenuButtons;

    harness.menuButton.click();
    aboutButton.click();
    assert.equal(aboutButton.getAttribute('aria-expanded'), 'true');
    assert.equal(harness.submenus.get('about-submenu').hidden, false);

    researchButton.click();
    assert.equal(aboutButton.getAttribute('aria-expanded'), 'false');
    assert.equal(harness.submenus.get('about-submenu').hidden, true);
    assert.equal(researchButton.getAttribute('aria-expanded'), 'true');
    assert.equal(harness.submenus.get('research-submenu').hidden, false);

    harness.menuButton.click();
    harness.submenuButtons.forEach((button) => {
        assert.equal(button.getAttribute('aria-expanded'), 'false');
        assert.equal(harness.submenus.get(button.getAttribute('aria-controls')).hidden, true);
    });
});

test('opening the drawer expands the active submenu', () => {
    const harness = createHarness('research');
    const researchButton = harness.submenuButtons[1];

    harness.menuButton.click();

    assert.equal(researchButton.getAttribute('aria-expanded'), 'true');
    assert.equal(harness.submenus.get('research-submenu').hidden, false);
});

test('entering the desktop breakpoint cleans up an open drawer', () => {
    const harness = createHarness('research');

    harness.menuButton.click();
    harness.mediaListeners.get('change')({ matches: true });

    assert.equal(harness.menuButton.getAttribute('aria-expanded'), 'false');
    assert.equal(harness.drawer.hidden, true);
    assert.equal(harness.overlay.hidden, true);
    assert.equal(harness.documentObject.body.style.overflow, 'auto');
    harness.submenuButtons.forEach((button) => {
        assert.equal(button.getAttribute('aria-expanded'), 'false');
    });
});
