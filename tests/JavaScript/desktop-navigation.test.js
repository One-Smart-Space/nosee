import assert from 'node:assert/strict';
import test from 'node:test';

import {
    initializeNavigationStates,
    shouldUseTransparentNavigation,
} from '../../resources/js/navigation-state.js';

test('transparent navigation switches after 24 pixels', () => {
    assert.equal(shouldUseTransparentNavigation(false, 0), false);
    assert.equal(shouldUseTransparentNavigation(true, 0), true);
    assert.equal(shouldUseTransparentNavigation(true, 24), true);
    assert.equal(shouldUseTransparentNavigation(true, 25), false);
});

test('one scroll listener keeps desktop and mobile states synchronized', () => {
    const pairs = Array.from({ length: 2 }, () => ({
        transparentState: { hidden: true },
        compactState: { hidden: false },
    }));
    let scrollListener;
    let listenerCount = 0;
    const navigations = pairs.map((pair) => ({
        dataset: { transparent: 'true' },
        querySelector: (selector) => selector.includes('transparent') ? pair.transparentState : pair.compactState,
    }));
    const windowObject = {
        scrollY: 0,
        addEventListener: (event, listener) => {
            assert.equal(event, 'scroll');
            listenerCount += 1;
            scrollListener = listener;
        },
    };

    initializeNavigationStates(navigations, windowObject);
    assert.equal(listenerCount, 1);
    pairs.forEach(({ transparentState, compactState }) => {
        assert.equal(transparentState.hidden, false);
        assert.equal(compactState.hidden, true);
    });

    windowObject.scrollY = 25;
    scrollListener();
    pairs.forEach(({ transparentState, compactState }) => {
        assert.equal(transparentState.hidden, true);
        assert.equal(compactState.hidden, false);
    });

    windowObject.scrollY = 0;
    scrollListener();
    pairs.forEach(({ transparentState, compactState }) => {
        assert.equal(transparentState.hidden, false);
        assert.equal(compactState.hidden, true);
    });
});

test('non-transparent navigation starts compact', () => {
    const transparentState = { hidden: false };
    const compactState = { hidden: true };
    const navigation = {
        dataset: { transparent: 'false' },
        querySelector: (selector) => selector.includes('transparent') ? transparentState : compactState,
    };
    const windowObject = {
        scrollY: 0,
        addEventListener: () => assert.fail('compact navigation must not register a scroll listener'),
    };

    initializeNavigationStates([navigation], windowObject);
    assert.equal(transparentState.hidden, true);
    assert.equal(compactState.hidden, false);
});
