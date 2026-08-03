import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { loadHomepageAnimations } from '../../resources/js/app.js';
import {
    HERO_ENTRANCE_EASING,
    HERO_ENTRANCE_STAGES,
    initializeHomepageAnimations,
    initializeSectionReveals,
} from '../../resources/js/homepage-animations.js';

function createHarness({ missing = [], reducedMotion = false } = {}) {
    const elements = new Map(HERO_ENTRANCE_STAGES.map((stage, index) => [
        stage.selector,
        missing.includes(stage.selector) ? [] : [{ name: index === 5 ? 'credit-and-indicators' : stage.selector }],
    ]));
    const hero = {
        dataset: {},
        querySelectorAll: (selector) => elements.get(selector) ?? [],
    };
    const root = {
        querySelector: (selector) => selector === '[data-home-hero]' ? hero : null,
        querySelectorAll: () => [],
    };
    const windowObject = { matchMedia: () => ({ matches: reducedMotion }) };
    const calls = [];
    const animate = (sequence) => calls.push(sequence);

    return { animate, calls, elements, hero, root, windowObject };
}

function createRevealSection({ actionCount = 1, itemCount = 3, withHeading = true } = {}) {
    const heading = withHeading ? { name: 'heading' } : null;
    const items = Array.from({ length: itemCount }, (_, index) => ({ name: `item-${index}` }));
    const actions = Array.from({ length: actionCount }, (_, index) => ({ name: `action-${index}` }));
    const group = { querySelectorAll: () => items };

    return {
        actions,
        dataset: {},
        heading,
        items,
        querySelector: (selector) => ({
            '[data-reveal-group]': group,
            '[data-reveal-heading]': heading,
        })[selector] ?? null,
        querySelectorAll: (selector) => selector === '[data-reveal-actions]' ? actions : [],
    };
}

function createRevealHarness({ reducedMotion = false, sections = [] } = {}) {
    const animations = [];
    const observers = [];
    const staggerCalls = [];
    const staggerDelay = (index) => index * 0.07;
    const root = { querySelectorAll: () => sections };
    const windowObject = { matchMedia: () => ({ matches: reducedMotion }) };
    const animate = (sequence) => animations.push(sequence);
    const inView = (targets, callback, options) => observers.push({ callback, options, targets });
    const stagger = (duration) => {
        staggerCalls.push(duration);

        return staggerDelay;
    };

    return {
        animate,
        animations,
        inView,
        observers,
        root,
        sections,
        stagger,
        staggerCalls,
        staggerDelay,
        windowObject,
    };
}

test('Motion loads only when the homepage marker exists', async () => {
    let imports = 0;
    const importer = async () => { imports++; };

    assert.equal(loadHomepageAnimations({ querySelector: () => null }, importer), null);
    assert.equal(imports, 0);

    await loadHomepageAnimations({ querySelector: () => ({}) }, importer);
    assert.equal(imports, 1);
});

test('hero entrance preserves the required order, timings, and easing', () => {
    const harness = createHarness();

    assert.equal(initializeHomepageAnimations(harness.root, harness.windowObject, harness.animate), true);
    assert.equal(harness.calls.length, 1);

    const sequence = harness.calls[0];
    assert.deepEqual(
        sequence.map(([elements]) => elements[0].name),
        HERO_ENTRANCE_STAGES.map((stage, index) => index === 5 ? 'credit-and-indicators' : stage.selector),
    );
    assert.deepEqual(sequence.map(([, keyframes]) => keyframes), HERO_ENTRANCE_STAGES.map(({ keyframes }) => keyframes));
    assert.deepEqual(sequence.map(([, , options]) => options.duration), [0.65, 0.4, 0.6, 0.5, 0.45, 0.4]);
    assert.deepEqual(sequence.map(([, , options]) => options.at), [0, 0.18, 0.32, 0.48, 0.64, 0.82]);
    sequence.forEach(([, , options]) => assert.deepEqual(options.ease, HERO_ENTRANCE_EASING));
    assert.ok(Math.max(...sequence.map(([, , options]) => options.at + options.duration)) <= 1.5);
});

test('hero entrance runs only once per page load', () => {
    const harness = createHarness();

    assert.equal(initializeHomepageAnimations(harness.root, harness.windowObject, harness.animate), true);
    assert.equal(initializeHomepageAnimations(harness.root, harness.windowObject, harness.animate), false);
    assert.equal(harness.calls.length, 1);
});

test('reduced motion bypasses animation and leaves final styles untouched', () => {
    const harness = createHarness({ reducedMotion: true });
    const elementsBefore = structuredClone([...harness.elements.values()]);

    assert.equal(initializeHomepageAnimations(harness.root, harness.windowObject, harness.animate), false);
    assert.equal(harness.calls.length, 0);
    assert.deepEqual([...harness.elements.values()], elementsBefore);
});

test('missing optional hero elements are omitted without stopping the sequence', () => {
    const missing = [
        '[data-hero-entrance-cta]',
        '[data-hero-entrance-credit], [data-hero-entrance-indicators]',
    ];
    const harness = createHarness({ missing });

    assert.equal(initializeHomepageAnimations(harness.root, harness.windowObject, harness.animate), true);
    assert.equal(harness.calls[0].length, HERO_ENTRANCE_STAGES.length - missing.length);
});

test('entrance hooks stay off carousel-managed transform elements', () => {
    const template = readFileSync(
        new URL('../../resources/views/components/home/hero.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(template, /data-homepage-motion/);
    assert.match(template, /data-hero-entrance-visual/);
    assert.doesNotMatch(
        template,
        /<[^>]*(?:data-hero-track|data-hero-image-slide|data-hero-content-slide)[^>]*data-hero-entrance|<[^>]*data-hero-entrance[^>]*(?:data-hero-track|data-hero-image-slide|data-hero-content-slide)/,
    );
    assert.doesNotMatch(template, /data-hero-entrance-(?:eyebrow|title|summary|cta|credit|indicators)[^>]*opacity-0/);
});

test('all four homepage sections register with the required viewport trigger', () => {
    const sections = Array.from({ length: 4 }, () => createRevealSection());
    const harness = createRevealHarness({ sections });

    assert.equal(initializeSectionReveals(
        harness.root,
        harness.windowObject,
        harness.animate,
        harness.inView,
        harness.stagger,
    ), 4);
    assert.equal(harness.observers.length, 1);
    assert.deepEqual(harness.observers[0].targets, sections);
    assert.deepEqual(harness.observers[0].options, {
        amount: 0.2,
        margin: '0px 0px -10% 0px',
    });
});

test('section reveal runs once with heading, staggered cards, and early CTA timing', () => {
    const section = createRevealSection({ itemCount: 4 });
    const harness = createRevealHarness({ sections: [section] });

    initializeSectionReveals(
        harness.root,
        harness.windowObject,
        harness.animate,
        harness.inView,
        harness.stagger,
    );
    harness.observers[0].callback(section);
    harness.observers[0].callback(section);

    assert.equal(harness.animations.length, 1);
    assert.equal(harness.staggerCalls[0], 0.07);

    const [heading, cards, actions] = harness.animations[0];
    assert.deepEqual(heading[1], { opacity: [0, 1], y: [16, 0] });
    assert.deepEqual(heading[2], { at: 0, duration: 0.55, ease: HERO_ENTRANCE_EASING });
    assert.deepEqual(cards[0], section.items);
    assert.deepEqual(cards[1], { opacity: [0, 1], y: [20, 0] });
    assert.equal(cards[2].at, 0.15);
    assert.equal(cards[2].delay, harness.staggerDelay);
    assert.equal(cards[2].duration, 0.6);
    assert.deepEqual(cards[2].ease, HERO_ENTRANCE_EASING);
    assert.deepEqual(actions[0], section.actions);
    assert.deepEqual(actions[1], { opacity: [0, 1], y: [10, 0] });
    assert.deepEqual(actions[2], { at: 0.25, duration: 0.45, ease: HERO_ENTRANCE_EASING });
    assert.ok(actions[2].at > cards[2].at);
    assert.ok(cards[2].at + cards[2].duration + harness.staggerDelay(3) < 1);
});

test('reduced motion bypasses section observers and animations', () => {
    const harness = createRevealHarness({
        reducedMotion: true,
        sections: [createRevealSection()],
    });

    assert.equal(initializeSectionReveals(
        harness.root,
        harness.windowObject,
        harness.animate,
        harness.inView,
        harness.stagger,
    ), 0);
    assert.equal(harness.observers.length, 0);
    assert.equal(harness.animations.length, 0);
});

test('a missing optional CTA leaves the heading and card reveal intact', () => {
    const section = createRevealSection({ actionCount: 0 });
    const harness = createRevealHarness({ sections: [section] });

    initializeSectionReveals(
        harness.root,
        harness.windowObject,
        harness.animate,
        harness.inView,
        harness.stagger,
    );
    harness.observers[0].callback(section);

    assert.equal(harness.animations[0].length, 2);
});

test('reveal markup uses neutral wrappers and preserves existing interactions', () => {
    const componentNames = [
        'monitoring-dashboard',
        'research-outputs',
        'trending-news',
        'upcoming-events',
    ];
    const templates = componentNames.map((name) => readFileSync(
        new URL(`../../resources/views/components/home/${name}.blade.php`, import.meta.url),
        'utf8',
    ));
    const module = readFileSync(
        new URL('../../resources/js/homepage-animations.js', import.meta.url),
        'utf8',
    );
    const eventCard = readFileSync(
        new URL('../../resources/views/components/cards/event-card.blade.php', import.meta.url),
        'utf8',
    );
    const hero = readFileSync(
        new URL('../../resources/views/components/home/hero.blade.php', import.meta.url),
        'utf8',
    );
    const footer = readFileSync(
        new URL('../../resources/views/components/footer/site-footer.blade.php', import.meta.url),
        'utf8',
    );

    templates.forEach((template) => {
        assert.match(template, /data-reveal-section/);
        assert.match(template, /data-reveal-heading/);
        assert.match(template, /data-reveal-actions/);
        assert.match(template, /data-reveal-group/);
        assert.match(template, /<div[^>]*data-reveal-item/);
        assert.doesNotMatch(template, /<x-(?:cards|ui)\.[^>]*data-reveal-(?:item|actions)/);
    });
    assert.match(templates[0], /lg:hover:grow-\[1\.5\]/);
    assert.match(templates[0], /lg:focus-within:grow-\[1\.5\]/);
    assert.match(eventCard, /lg:hover:-translate-y-1/);
    assert.match(eventCard, /lg:focus-within:-translate-y-1/);
    assert.doesNotMatch(module, /addEventListener\(['"]scroll/);
    assert.doesNotMatch(hero, /data-reveal-/);
    assert.doesNotMatch(footer, /data-reveal-/);
});
