import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { initializeHeroCarousel } from '../../resources/js/hero-carousel.js';

function createElement(classes = []) {
    const attributes = new Map();
    const listeners = new Map();
    const classNames = new Set(classes);

    return {
        dataset: {},
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
        listenerCount: () => [...listeners.values()].reduce((total, eventListeners) => total + eventListeners.length, 0),
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
        change(nextMatches) {
            this.matches = nextMatches;
            listeners.forEach((listener) => listener({ matches: nextMatches }));
        },
    };
}

function createHarness({ count = 3, desktop = true, reducedMotion = false } = {}) {
    const track = createElement();
    const imageSlides = Array.from({ length: count }, () => createElement());
    const contentSlides = Array.from({ length: count }, (_, index) => createElement(
        index === 0 ? ['opacity-100', 'blur-none'] : ['pointer-events-none', 'opacity-0', 'blur-sm'],
    ));
    const indicators = count > 1
        ? Array.from({ length: count }, (_, index) => {
            const indicator = createElement(index === 0 ? ['bg-inverse'] : ['bg-transparent']);

            indicator.dataset.slideIndex = String(index);
            indicator.setAttribute('aria-current', index === 0 ? 'true' : 'false');

            return indicator;
        })
        : [];
    const carousel = createElement();
    const desktopQuery = createMediaQuery(desktop);
    const reducedMotionQuery = createMediaQuery(reducedMotion);
    const documentListeners = new Map();
    const documentObject = {
        hidden: false,
        addEventListener: (event, listener) => documentListeners.set(event, listener),
    };
    const timers = new Map();
    let nextTimerId = 1;
    const windowObject = {
        clearTimeout: (id) => timers.delete(id),
        matchMedia: (query) => query.includes('prefers-reduced-motion') ? reducedMotionQuery : desktopQuery,
        setTimeout(callback, delay) {
            const id = nextTimerId++;

            timers.set(id, { callback, delay });

            return id;
        },
    };
    const elements = [track, ...imageSlides, ...contentSlides, ...indicators];

    carousel.contains = (element) => elements.includes(element);
    carousel.querySelector = (selector) => selector === '[data-hero-track]' ? track : null;
    carousel.querySelectorAll = (selector) => ({
        '[data-hero-image-slide]': imageSlides,
        '[data-hero-content-slide]': contentSlides,
        '[data-hero-indicator]': indicators,
    })[selector] ?? [];

    const controller = initializeHeroCarousel(carousel, windowObject, documentObject);
    const timerCount = (delay) => [...timers.values()].filter((timer) => timer.delay === delay).length;
    const runTimer = (delay) => {
        const timer = [...timers.entries()].find(([, value]) => value.delay === delay);

        assert.ok(timer, `Expected a ${delay}ms timer`);
        timers.delete(timer[0]);
        timer[1].callback();
    };

    return {
        carousel,
        contentSlides,
        controller,
        desktopQuery,
        documentListeners,
        documentObject,
        imageSlides,
        indicators,
        reducedMotionQuery,
        runTimer,
        timerCount,
        track,
    };
}

function swipe(carousel, fromX, toX, fromY = 0, toY = 0) {
    carousel.dispatch('pointerdown', { clientX: fromX, clientY: fromY, isPrimary: true });
    carousel.dispatch('pointerup', { clientX: toX, clientY: toY, isPrimary: true });
}

test('a single slide does not start carousel behavior', () => {
    const harness = createHarness({ count: 1 });

    assert.equal(harness.controller.currentIndex(), 0);
    assert.equal(harness.timerCount(6000), 0);
    assert.equal(harness.carousel.listenerCount(), 0);
});

test('the first image is prioritized and later images use native lazy loading', () => {
    const template = readFileSync(
        new URL('../../resources/views/components/home/hero.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(template, /@if \(\$loop->first\)\s+fetchpriority="high"\s+@else\s+loading="lazy"/);
    assert.match(template, /h-\[78svh\]/);
    assert.match(template, /class="relative h-full w-full shrink-0 overflow-hidden"[\s\S]*data-hero-image-slide/);
});

test('desktop autoplay advances every six seconds and wraps to the first slide', () => {
    const harness = createHarness();

    assert.equal(harness.timerCount(6000), 1);

    harness.runTimer(6000);
    assert.equal(harness.controller.currentIndex(), 1);
    assert.equal(harness.track.style.transform, 'translateX(-100%)');
    harness.runTimer(400);

    harness.runTimer(6000);
    assert.equal(harness.controller.currentIndex(), 2);
    harness.runTimer(400);

    harness.runTimer(6000);
    assert.equal(harness.controller.currentIndex(), 0);
});

test('hover, focus, and tab visibility pause and resume autoplay', () => {
    const harness = createHarness();

    harness.carousel.dispatch('mouseenter');
    assert.equal(harness.timerCount(6000), 0);
    harness.carousel.dispatch('mouseleave');
    assert.equal(harness.timerCount(6000), 1);

    harness.carousel.dispatch('focusin');
    assert.equal(harness.timerCount(6000), 0);
    harness.carousel.dispatch('focusout', { relatedTarget: harness.indicators[0] });
    assert.equal(harness.timerCount(6000), 0);
    harness.carousel.dispatch('focusout', { relatedTarget: null });
    assert.equal(harness.timerCount(6000), 1);

    harness.documentObject.hidden = true;
    harness.documentListeners.get('visibilitychange')();
    assert.equal(harness.timerCount(6000), 0);
    harness.documentObject.hidden = false;
    harness.documentListeners.get('visibilitychange')();
    assert.equal(harness.timerCount(6000), 1);
});

test('indicator navigation updates every active state and restarts autoplay', () => {
    const harness = createHarness();

    harness.indicators[2].dispatch('click');

    assert.equal(harness.controller.currentIndex(), 2);
    assert.equal(harness.timerCount(6000), 1);
    assert.equal(harness.indicators[0].getAttribute('aria-current'), 'false');
    assert.equal(harness.indicators[2].getAttribute('aria-current'), 'true');
    assert.equal(harness.indicators[2].classList.contains('bg-inverse'), true);
    assert.equal(harness.contentSlides[0].inert, true);
    assert.equal(harness.contentSlides[2].inert, false);
    assert.equal(harness.imageSlides[2].getAttribute('aria-hidden'), 'false');
});

test('mobile swipes in both directions and wraps from the first slide to the last', () => {
    const harness = createHarness({ desktop: false });

    assert.equal(harness.timerCount(6000), 0);
    swipe(harness.carousel, 100, 40);
    assert.equal(harness.controller.currentIndex(), 1);
    harness.runTimer(400);

    swipe(harness.carousel, 40, 100);
    assert.equal(harness.controller.currentIndex(), 0);
    harness.runTimer(400);

    swipe(harness.carousel, 40, 100);
    assert.equal(harness.controller.currentIndex(), 2);
});

test('mobile swipe requires 50 horizontal pixels and ignores vertical gestures', () => {
    const harness = createHarness({ desktop: false });

    swipe(harness.carousel, 100, 51);
    assert.equal(harness.controller.currentIndex(), 0);

    swipe(harness.carousel, 100, 40, 0, 80);
    assert.equal(harness.controller.currentIndex(), 0);

    swipe(harness.carousel, 100, 50);
    assert.equal(harness.controller.currentIndex(), 1);
});

test('reduced motion disables autoplay and transition locking', () => {
    const harness = createHarness({ desktop: false, reducedMotion: true });

    assert.equal(harness.timerCount(6000), 0);
    swipe(harness.carousel, 100, 40);
    swipe(harness.carousel, 100, 40);

    assert.equal(harness.controller.currentIndex(), 2);
    assert.equal(harness.timerCount(400), 0);
    assert.equal(harness.track.style.transform, 'translateX(-200%)');
});

test('rapid interaction cannot overlap an active transition', () => {
    const harness = createHarness({ desktop: false });

    swipe(harness.carousel, 100, 40);
    harness.indicators[2].dispatch('click');
    assert.equal(harness.controller.currentIndex(), 1);

    harness.runTimer(400);
    harness.indicators[2].dispatch('click');
    assert.equal(harness.controller.currentIndex(), 2);
});

test('desktop and reduced-motion changes update autoplay without duplicate timers', () => {
    const harness = createHarness({ desktop: false });

    harness.desktopQuery.change(true);
    assert.equal(harness.timerCount(6000), 1);

    harness.reducedMotionQuery.change(true);
    assert.equal(harness.timerCount(6000), 0);

    harness.reducedMotionQuery.change(false);
    assert.equal(harness.timerCount(6000), 1);
});
