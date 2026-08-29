import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { initializeProfileCarousel } from '../../resources/js/profile-carousel.js';

function createElement(classes = []) {
    const attributes = new Map();
    const listeners = new Map();
    const classNames = new Set(classes);

    return {
        hidden: false,
        inert: false,
        style: {},
        addEventListener(event, listener) {
            const eventListeners = listeners.get(event) ?? [];

            eventListeners.push(listener);
            listeners.set(event, eventListeners);
        },
        classList: {
            contains: (className) => classNames.has(className),
            toggle(className, force) {
                if (force) {
                    classNames.add(className);
                } else {
                    classNames.delete(className);
                }
            },
        },
        dispatch(event, detail = {}) {
            listeners.get(event)?.forEach((listener) => listener(detail));
        },
        getAttribute: (name) => attributes.get(name),
        setAttribute: (name, value) => attributes.set(name, value),
    };
}

function createMediaQuery(matches) {
    const listeners = [];

    return {
        matches,
        addEventListener(event, listener) {
            assert.equal(event, 'change');
            listeners.push(listener);
        },
        change(matches) {
            this.matches = matches;
            listeners.forEach((listener) => listener({ matches }));
        },
    };
}

function createHarness({ count = 7, viewport = 'mobile' } = {}) {
    const track = createElement();
    const profileViewport = createElement();
    const slides = Array.from({ length: count }, (_, index) => ({
        ...createElement(),
        offsetLeft: index * 100,
    }));
    const indicators = Array.from({ length: count }, (_, index) => createElement(
        index === 0 ? ['bg-brand'] : ['bg-disabled'],
    ));
    const indicatorGroup = createElement();
    const arrowGroup = createElement();
    const previousButton = createElement();
    const nextButton = createElement();
    const tabletQuery = createMediaQuery(viewport !== 'mobile');
    const desktopQuery = createMediaQuery(viewport === 'desktop');
    const windowObject = {
        matchMedia: (query) => query.includes('64rem') ? desktopQuery : tabletQuery,
    };
    const carousel = {
        querySelector: (selector) => ({
            '[data-profile-viewport]': profileViewport,
            '[data-profile-track]': track,
            '[data-profile-indicators]': indicatorGroup,
            '[data-profile-arrows]': arrowGroup,
            '[data-profile-previous]': previousButton,
            '[data-profile-next]': nextButton,
        })[selector] ?? null,
        querySelectorAll: (selector) => ({
            '[data-profile-slide]': slides,
            '[data-profile-indicator]': indicators,
        })[selector] ?? [],
    };
    const controller = initializeProfileCarousel(carousel, windowObject);

    return {
        arrowGroup,
        controller,
        desktopQuery,
        indicatorGroup,
        indicators,
        nextButton,
        previousButton,
        profileViewport,
        slides,
        tabletQuery,
        track,
    };
}

function swipe(viewport, fromX, toX, fromY = 0, toY = 0) {
    viewport.dispatch('pointerdown', { clientX: fromX, clientY: fromY, isPrimary: true });
    viewport.dispatch('pointerup', { clientX: toX, clientY: toY, isPrimary: true });
}

test('desktop groups seven profiles into three accessible pages', () => {
    const harness = createHarness({ viewport: 'desktop' });

    assert.equal(harness.controller.pageCount(), 3);
    assert.equal(harness.track.style.transform, 'translateX(-0px)');
    assert.deepEqual(harness.slides.map((slide) => slide.getAttribute('aria-hidden')), [
        'false', 'false', 'false', 'true', 'true', 'true', 'true',
    ]);
    assert.deepEqual(harness.indicators.map((indicator) => indicator.hidden), [
        false, false, false, true, true, true, true,
    ]);
    assert.equal(harness.indicators[0].getAttribute('aria-label'), 'Show profiles 1 to 3');
});

test('cards fill their responsive slots with only track gaps between them', () => {
    const template = readFileSync(
        new URL('../../resources/views/components/about/profile-carousel.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(template, /class="flex gap-4[^\"]*lg:gap-8" data-profile-track/);
    assert.match(template, /class="w-full shrink-0 sm:w-\[calc\(\(100%-1rem\)\/2\)\] lg:w-\[calc\(\(100%-4rem\)\/3\)\]"/);
    assert.doesNotMatch(template, /(?:lg:)?pr-[48]/);
});

test('desktop arrows move one card and loop in both directions', () => {
    const harness = createHarness({ viewport: 'desktop' });

    harness.nextButton.dispatch('click');
    assert.equal(harness.controller.currentIndex(), 1);
    assert.equal(harness.track.style.transform, 'translateX(-100px)');
    assert.equal(harness.indicators[0].getAttribute('aria-current'), 'true');
    assert.deepEqual(harness.slides.map((slide) => slide.getAttribute('aria-hidden')), [
        'true', 'false', 'false', 'false', 'true', 'true', 'true',
    ]);

    harness.nextButton.dispatch('click');
    harness.nextButton.dispatch('click');
    assert.equal(harness.controller.currentIndex(), 3);
    assert.equal(harness.indicators[1].getAttribute('aria-current'), 'true');

    harness.controller.showProfile(0);
    harness.previousButton.dispatch('click');
    assert.equal(harness.controller.currentIndex(), 6);
    assert.equal(harness.slides[6].getAttribute('aria-hidden'), 'false');
});

test('indicators move by one responsive group', () => {
    const desktopHarness = createHarness({ viewport: 'desktop' });

    desktopHarness.indicators[1].dispatch('click');
    assert.equal(desktopHarness.controller.currentIndex(), 3);
    assert.equal(desktopHarness.track.style.transform, 'translateX(-300px)');

    const harness = createHarness({ viewport: 'tablet' });

    assert.equal(harness.controller.pageCount(), 4);
    harness.indicators[3].dispatch('click');

    assert.equal(harness.controller.currentIndex(), 6);
    assert.equal(harness.track.style.transform, 'translateX(-600px)');
    assert.equal(harness.slides[6].getAttribute('aria-hidden'), 'false');
    assert.equal(harness.indicators[3].getAttribute('aria-current'), 'true');
});

test('mobile swipe navigation loops and ignores short or vertical gestures', () => {
    const harness = createHarness();

    swipe(harness.profileViewport, 100, 40);
    assert.equal(harness.controller.currentIndex(), 1);

    swipe(harness.profileViewport, 40, 100);
    assert.equal(harness.controller.currentIndex(), 0);

    swipe(harness.profileViewport, 40, 100);
    assert.equal(harness.controller.currentIndex(), 6);

    swipe(harness.profileViewport, 100, 51);
    swipe(harness.profileViewport, 100, 40, 0, 80);
    assert.equal(harness.controller.currentIndex(), 6);
});

test('viewport changes preserve the nearest visible profile', () => {
    const harness = createHarness();

    harness.controller.showProfile(4);
    harness.tabletQuery.change(true);
    assert.equal(harness.controller.currentIndex(), 4);
    assert.equal(harness.controller.pageCount(), 4);

    harness.desktopQuery.change(true);
    assert.equal(harness.controller.currentIndex(), 4);
    assert.equal(harness.controller.pageCount(), 3);
});
