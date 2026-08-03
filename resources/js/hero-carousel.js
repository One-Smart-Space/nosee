const AUTOPLAY_DELAY = 6000;
const TRANSITION_DURATION = 400;
const SWIPE_THRESHOLD = 50;

export function initializeHeroCarousel(carousel, windowObject = window, documentObject = document) {
    // Cache this carousel's elements and environment so every instance manages its own state.
    const track = carousel.querySelector('[data-hero-track]');
    const imageSlides = [...carousel.querySelectorAll('[data-hero-image-slide]')];
    const contentSlides = [...carousel.querySelectorAll('[data-hero-content-slide]')];
    const indicators = [...carousel.querySelectorAll('[data-hero-indicator]')];
    const desktopQuery = windowObject.matchMedia('(min-width: 64rem)');
    const reducedMotionQuery = windowObject.matchMedia('(prefers-reduced-motion: reduce)');
    let currentIndex = 0;
    let autoplayTimer;
    let transitionTimer;
    let isTransitioning = false;
    let isHovered = false;
    let hasFocus = false;
    let pointerStart;

    // Keep autoplay limited to eligible desktop sessions and restart it from a fresh delay.
    const clearAutoplay = () => {
        if (autoplayTimer !== undefined) {
            windowObject.clearTimeout(autoplayTimer);
            autoplayTimer = undefined;
        }
    };

    const canAutoplay = () => (
        contentSlides.length > 1
        && desktopQuery.matches
        && ! reducedMotionQuery.matches
        && ! documentObject.hidden
        && ! isHovered
        && ! hasFocus
    );

    // Synchronize the image track, fixed content, and indicators in one state update.
    const updateSlides = () => {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        imageSlides.forEach((slide, index) => {
            slide.setAttribute('aria-hidden', index === currentIndex ? 'false' : 'true');
        });

        contentSlides.forEach((slide, index) => {
            const isActive = index === currentIndex;

            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            slide.inert = ! isActive;
            slide.classList.toggle('pointer-events-none', ! isActive);
            slide.classList.toggle('opacity-0', ! isActive);
            slide.classList.toggle('opacity-100', isActive);
            slide.classList.toggle('blur-sm', ! isActive);
            slide.classList.toggle('blur-none', isActive);
        });

        indicators.forEach((indicator, index) => {
            const isActive = index === currentIndex;

            indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
            indicator.classList.toggle('bg-inverse', isActive);
            indicator.classList.toggle('bg-transparent', ! isActive);
        });
    };

    const scheduleAutoplay = () => {
        clearAutoplay();

        if (canAutoplay()) {
            autoplayTimer = windowObject.setTimeout(() => {
                autoplayTimer = undefined;
                showSlide(currentIndex + 1);
                scheduleAutoplay();
            }, AUTOPLAY_DELAY);
        }
    };

    // Wrap slide indexes and block new changes until the current transition finishes.
    const showSlide = (requestedIndex, manual = false) => {
        const nextIndex = (requestedIndex + contentSlides.length) % contentSlides.length;

        if (nextIndex === currentIndex) {
            if (manual) {
                scheduleAutoplay();
            }

            return false;
        }

        if (isTransitioning) {
            return false;
        }

        currentIndex = nextIndex;
        updateSlides();

        if (! reducedMotionQuery.matches) {
            isTransitioning = true;
            transitionTimer = windowObject.setTimeout(() => {
                isTransitioning = false;
                transitionTimer = undefined;
            }, TRANSITION_DURATION);
        }

        if (manual) {
            scheduleAutoplay();
        }

        return true;
    };

    if (contentSlides.length <= 1) {
        return { currentIndex: () => currentIndex, showSlide };
    }

    // Bind indicator navigation and pause autoplay while the user interacts with the hero.
    indicators.forEach((indicator) => {
        indicator.addEventListener('click', () => {
            showSlide(Number(indicator.dataset.slideIndex), true);
        });
    });

    carousel.addEventListener('mouseenter', () => {
        isHovered = true;
        clearAutoplay();
    });

    carousel.addEventListener('mouseleave', () => {
        isHovered = false;
        scheduleAutoplay();
    });

    carousel.addEventListener('focusin', () => {
        hasFocus = true;
        clearAutoplay();
    });

    carousel.addEventListener('focusout', (event) => {
        if (! carousel.contains(event.relatedTarget)) {
            hasFocus = false;
            scheduleAutoplay();
        }
    });

    // Treat deliberate horizontal pointer gestures as mobile swipe navigation.
    carousel.addEventListener('pointerdown', (event) => {
        if (! desktopQuery.matches && event.isPrimary !== false) {
            pointerStart = { x: event.clientX, y: event.clientY };
        }
    });

    carousel.addEventListener('pointerup', (event) => {
        if (! pointerStart || desktopQuery.matches) {
            pointerStart = undefined;

            return;
        }

        const horizontalDistance = pointerStart.x - event.clientX;
        const verticalDistance = pointerStart.y - event.clientY;

        pointerStart = undefined;

        if (Math.abs(horizontalDistance) >= SWIPE_THRESHOLD && Math.abs(horizontalDistance) > Math.abs(verticalDistance)) {
            showSlide(currentIndex + (horizontalDistance > 0 ? 1 : -1), true);
        }
    });

    carousel.addEventListener('pointercancel', () => {
        pointerStart = undefined;
    });

    // Re-evaluate timers when tab visibility, viewport size, or motion preferences change.
    documentObject.addEventListener('visibilitychange', scheduleAutoplay);
    desktopQuery.addEventListener('change', scheduleAutoplay);
    reducedMotionQuery.addEventListener('change', () => {
        if (reducedMotionQuery.matches && transitionTimer !== undefined) {
            windowObject.clearTimeout(transitionTimer);
            transitionTimer = undefined;
            isTransitioning = false;
        }

        scheduleAutoplay();
    });

    scheduleAutoplay();

    return { currentIndex: () => currentIndex, showSlide };
}

if (typeof document !== 'undefined') {
    // Initialize every server-rendered hero independently.
    document.querySelectorAll('[data-hero-carousel]').forEach((carousel) => {
        initializeHeroCarousel(carousel);
    });
}
