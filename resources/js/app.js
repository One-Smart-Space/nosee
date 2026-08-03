import './navigation-state.js';
import './mobile-navigation.js';
import './hero-carousel.js';
import './newsletter.js';

export function loadHomepageAnimations(documentObject, importer = () => import('./homepage-animations.js')) {
    return documentObject.querySelector('[data-homepage-motion]') ? importer() : null;
}

if (typeof document !== 'undefined') {
    // Keep Motion out of every non-homepage bundle execution path.
    loadHomepageAnimations(document);
}
