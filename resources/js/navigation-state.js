export const shouldUseTransparentNavigation = (transparent, scrollY) => transparent && scrollY <= 24;

export function initializeNavigationStates(navigations, windowObject = window) {
    // Cache both navigation variants so one scroll listener keeps them synchronized.
    const states = [...navigations].map((navigation) => ({
        navigation,
        transparent: navigation.dataset.transparent === 'true',
        expandedOnly: [...navigation.querySelectorAll('[data-navigation-expanded-only]')],
    }));

    const update = () => {
        states.forEach((state) => {
            const expanded = shouldUseTransparentNavigation(state.transparent, windowObject.scrollY);

            state.navigation.dataset.navigationMode = expanded ? 'expanded' : 'compact';
            state.expandedOnly.forEach((element) => {
                element.inert = !expanded;
            });
        });
    };

    update();

    if (states.some((state) => state.transparent)) {
        windowObject.addEventListener('scroll', update, { passive: true });
    }
}

if (typeof document !== 'undefined') {
    // Initialize all navigation variants together to avoid duplicate scroll listeners.
    initializeNavigationStates(document.querySelectorAll('[data-scroll-navigation]'));
}
