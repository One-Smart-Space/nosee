import { animate } from 'motion/mini';
import { inView, stagger } from 'motion';

export const HERO_ENTRANCE_EASING = [0.22, 1, 0.36, 1];

export const HERO_ENTRANCE_STAGES = [
    { selector: '[data-hero-entrance-visual]', keyframes: { opacity: [0, 1], scale: [1.02, 1] }, duration: 0.65, at: 0 },
    { selector: '[data-hero-entrance-eyebrow]', keyframes: { opacity: [0, 1], y: [12, 0] }, duration: 0.4, at: 0.18 },
    { selector: '[data-hero-entrance-title]', keyframes: { opacity: [0, 1], y: [20, 0] }, duration: 0.6, at: 0.32 },
    { selector: '[data-hero-entrance-summary]', keyframes: { opacity: [0, 1], y: [16, 0] }, duration: 0.5, at: 0.48 },
    { selector: '[data-hero-entrance-cta]', keyframes: { opacity: [0, 1], y: [12, 0] }, duration: 0.45, at: 0.64 },
    { selector: '[data-hero-entrance-credit], [data-hero-entrance-indicators]', keyframes: { opacity: [0, 1] }, duration: 0.4, at: 0.82 },
];

export function buildHeroEntranceSequence(hero) {
    return HERO_ENTRANCE_STAGES.flatMap((stage) => {
        const elements = [...hero.querySelectorAll(stage.selector)];

        return elements.length === 0
            ? []
            : [[elements, stage.keyframes, {
                at: stage.at,
                duration: stage.duration,
                ease: HERO_ENTRANCE_EASING,
            }]];
    });
}

export function initializeHeroEntrance(
    root = document,
    windowObject = window,
    animateFunction = animate,
) {
    const hero = root.querySelector('[data-home-hero]');

    if (!hero || hero.dataset.heroEntrancePlayed === 'true') {
        return false;
    }

    hero.dataset.heroEntrancePlayed = 'true';

    // Reduced-motion sessions retain the untouched server-rendered final state.
    if (windowObject.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return false;
    }

    const sequence = buildHeroEntranceSequence(hero);

    if (sequence.length === 0) {
        return false;
    }

    animateFunction(sequence);

    return true;
}

export function buildSectionRevealSequence(section, staggerFunction = stagger) {
    const heading = section.querySelector('[data-reveal-heading]');
    const group = section.querySelector('[data-reveal-group]');
    const items = group ? [...group.querySelectorAll('[data-reveal-item]')] : [];
    const actions = [...section.querySelectorAll('[data-reveal-actions]')];
    const sequence = [];

    if (heading) {
        sequence.push([[heading], { opacity: [0, 1], y: [16, 0] }, {
            at: 0,
            duration: 0.55,
            ease: HERO_ENTRANCE_EASING,
        }]);
    }

    if (items.length > 0) {
        sequence.push([items, { opacity: [0, 1], y: [20, 0] }, {
            at: 0.15,
            delay: staggerFunction(0.07),
            duration: 0.6,
            ease: HERO_ENTRANCE_EASING,
        }]);
    }

    if (actions.length > 0) {
        sequence.push([actions, { opacity: [0, 1], y: [10, 0] }, {
            at: 0.25,
            duration: 0.45,
            ease: HERO_ENTRANCE_EASING,
        }]);
    }

    return sequence;
}

export function initializeSectionReveals(
    root = document,
    windowObject = window,
    animateFunction = animate,
    inViewFunction = inView,
    staggerFunction = stagger,
) {
    if (windowObject.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return 0;
    }

    const sections = [...root.querySelectorAll('[data-reveal-section]')];

    if (sections.length === 0) {
        return 0;
    }

    // Motion unobserves after this callback; the data flag also prevents manual re-entry.
    inViewFunction(sections, (section) => {
        if (section.dataset.revealPlayed === 'true') {
            return;
        }

        section.dataset.revealPlayed = 'true';
        const sequence = buildSectionRevealSequence(section, staggerFunction);

        if (sequence.length > 0) {
            animateFunction(sequence);
        }
    }, {
        amount: 0.2,
        margin: '0px 0px -10% 0px',
    });

    return sections.length;
}

export function initializeHomepageAnimations(
    root = document,
    windowObject = window,
    animateFunction = animate,
    inViewFunction = inView,
    staggerFunction = stagger,
) {
    const heroStarted = initializeHeroEntrance(root, windowObject, animateFunction);
    const revealCount = initializeSectionReveals(
        root,
        windowObject,
        animateFunction,
        inViewFunction,
        staggerFunction,
    );

    return heroStarted || revealCount > 0;
}

if (typeof document !== 'undefined') {
    initializeHomepageAnimations();
}
