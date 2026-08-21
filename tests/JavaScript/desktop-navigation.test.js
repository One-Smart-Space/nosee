import assert from 'node:assert/strict';
import test from 'node:test';

import {
    initializeNavigationStates,
    shouldUseTransparentNavigation,
} from '../../resources/js/navigation-state.js';

test('expanded navigation switches after 24 pixels', () => {
    assert.equal(shouldUseTransparentNavigation(false, 0), false);
    assert.equal(shouldUseTransparentNavigation(true, 0), true);
    assert.equal(shouldUseTransparentNavigation(true, 24), true);
    assert.equal(shouldUseTransparentNavigation(true, 25), false);
});

test('one scroll listener keeps desktop and mobile states synchronized', () => {
    let scrollListener;
    let listenerCount = 0;
    const expandedOnly = [[], [{ inert: true }]];
    const navigations = expandedOnly.map((elements) => ({
        dataset: { transparent: 'true', navigationMode: 'compact' },
        querySelectorAll: () => elements,
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
    navigations.forEach((navigation) => {
        assert.equal(navigation.dataset.navigationMode, 'expanded');
    });
    assert.equal(expandedOnly[1][0].inert, false);

    windowObject.scrollY = 25;
    scrollListener();
    navigations.forEach((navigation) => {
        assert.equal(navigation.dataset.navigationMode, 'compact');
    });
    assert.equal(expandedOnly[1][0].inert, true);

    windowObject.scrollY = 0;
    scrollListener();
    navigations.forEach((navigation) => {
        assert.equal(navigation.dataset.navigationMode, 'expanded');
    });
    assert.equal(expandedOnly[1][0].inert, false);
});

test('non-transparent desktop navigation starts compact', () => {
    const navigation = {
        dataset: { transparent: 'false', navigationMode: 'expanded' },
        querySelectorAll: () => [],
    };
    const windowObject = {
        scrollY: 0,
        addEventListener: () => assert.fail('compact navigation must not register a scroll listener'),
    };

    initializeNavigationStates([navigation], windowObject);
    assert.equal(navigation.dataset.navigationMode, 'compact');
});
