const SWIPE_THRESHOLD = 50;

export function initializeProfileCarousel(carousel, windowObject = window) {
    const viewport = carousel.querySelector('[data-profile-viewport]');
    const track = carousel.querySelector('[data-profile-track]');
    const slides = [...carousel.querySelectorAll('[data-profile-slide]')];
    const indicators = [...carousel.querySelectorAll('[data-profile-indicator]')];
    const indicatorGroup = carousel.querySelector('[data-profile-indicators]');
    const arrowGroup = carousel.querySelector('[data-profile-arrows]');
    const previousButton = carousel.querySelector('[data-profile-previous]');
    const nextButton = carousel.querySelector('[data-profile-next]');
    const tabletQuery = windowObject.matchMedia('(min-width: 40rem)');
    const desktopQuery = windowObject.matchMedia('(min-width: 64rem)');
    let profilesPerPage = desktopQuery.matches ? 3 : tabletQuery.matches ? 2 : 1;
    let currentIndex = 0;
    let pointerStart;

    if (! viewport || ! track || slides.length === 0) {
        return null;
    }

    const pageCount = () => Math.ceil(slides.length / profilesPerPage);

    const update = () => {
        const totalPages = pageCount();
        const activePage = Math.floor(currentIndex / profilesPerPage);
        const lastVisibleProfile = Math.min(currentIndex + profilesPerPage, slides.length);

        track.style.transform = `translateX(-${slides[currentIndex].offsetLeft}px)`;

        slides.forEach((slide, index) => {
            const isVisible = index >= currentIndex && index < lastVisibleProfile;

            slide.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            slide.inert = ! isVisible;
        });

        indicators.forEach((indicator, index) => {
            const isAvailable = index < totalPages;
            const isActive = index === activePage;
            const firstProfile = index * profilesPerPage + 1;
            const lastProfile = Math.min(firstProfile + profilesPerPage - 1, slides.length);

            indicator.hidden = ! isAvailable;
            indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
            indicator.setAttribute('aria-label', firstProfile === lastProfile
                ? `Show profile ${firstProfile}`
                : `Show profiles ${firstProfile} to ${lastProfile}`);
            indicator.classList.toggle('bg-brand', isActive);
            indicator.classList.toggle('bg-disabled', ! isActive);
        });

        if (indicatorGroup) {
            indicatorGroup.hidden = totalPages <= 1;
        }

        if (arrowGroup) {
            arrowGroup.hidden = totalPages <= 1;
        }
    };

    const showProfile = (requestedIndex) => {
        currentIndex = (requestedIndex + slides.length) % slides.length;
        update();
    };

    previousButton?.addEventListener('click', () => showProfile(currentIndex - 1));
    nextButton?.addEventListener('click', () => showProfile(currentIndex + 1));

    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => showProfile(index * profilesPerPage));
    });

    viewport.addEventListener('pointerdown', (event) => {
        if (! tabletQuery.matches && event.isPrimary !== false) {
            pointerStart = { x: event.clientX, y: event.clientY };
        }
    });

    viewport.addEventListener('pointerup', (event) => {
        if (! pointerStart || tabletQuery.matches) {
            pointerStart = undefined;

            return;
        }

        const horizontalDistance = pointerStart.x - event.clientX;
        const verticalDistance = pointerStart.y - event.clientY;

        pointerStart = undefined;

        if (Math.abs(horizontalDistance) >= SWIPE_THRESHOLD && Math.abs(horizontalDistance) > Math.abs(verticalDistance)) {
            showProfile(currentIndex + (horizontalDistance > 0 ? 1 : -1));
        }
    });

    viewport.addEventListener('pointercancel', () => {
        pointerStart = undefined;
    });

    const handleViewportChange = () => {
        profilesPerPage = desktopQuery.matches ? 3 : tabletQuery.matches ? 2 : 1;
        update();
    };

    tabletQuery.addEventListener('change', handleViewportChange);
    desktopQuery.addEventListener('change', handleViewportChange);
    windowObject.addEventListener?.('resize', update);
    update();

    return {
        currentIndex: () => currentIndex,
        pageCount,
        showProfile,
    };
}

if (typeof document !== 'undefined') {
    document.querySelectorAll('[data-profile-carousel]').forEach((carousel) => {
        initializeProfileCarousel(carousel);
    });
}
