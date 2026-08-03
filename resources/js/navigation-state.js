export const shouldUseTransparentNavigation = (transparent, scrollY) => transparent && scrollY <= 24;

export function initializeNavigationStates(navigations, windowObject = window) {
    // Cache both visual states so desktop and mobile navigation share one scroll update.
    const states = [...navigations].map((navigation) => ({
        transparent: navigation.dataset.transparent === 'true',
        transparentState: navigation.querySelector('[data-navigation-state="transparent"]'),
        compactState: navigation.querySelector('[data-navigation-state="compact"]'),
    }));

    // Show the transparent header only when the page allows it and remains near the top.
    const update = () => {
        states.forEach((state) => {
            const showTransparent = shouldUseTransparentNavigation(state.transparent, windowObject.scrollY);

            state.transparentState.hidden = !showTransparent;
            state.compactState.hidden = showTransparent;
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
