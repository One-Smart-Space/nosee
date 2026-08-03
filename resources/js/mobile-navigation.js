const focusableSelector = 'a[href], button:not([disabled])';

// Return only controls that are currently available inside the drawer.
function focusableElements(drawer) {
    return [...drawer.querySelectorAll(focusableSelector)].filter(
        (element) => element.closest('[hidden]') === null,
    );
}

export function initializeMobileNavigation(navigation, documentObject = document, windowObject = window) {
    // Cache drawer controls and local state for this mobile navigation instance.
    const menuButton = navigation.querySelector('[data-mobile-menu-button]');
    const drawer = navigation.querySelector('[data-mobile-drawer]');
    const overlay = navigation.querySelector('[data-mobile-overlay]');
    const openIcon = navigation.querySelector('[data-mobile-menu-open-icon]');
    const closeIcon = navigation.querySelector('[data-mobile-menu-close-icon]');
    const submenuButtons = [...navigation.querySelectorAll('[data-mobile-submenu-button]')];
    const desktopQuery = windowObject.matchMedia('(min-width: 64rem)');
    let isOpen = false;
    let previousBodyOverflow = '';

    // Keep submenu state mutually exclusive and reset it whenever the drawer closes.
    const resetSubmenus = () => {
        submenuButtons.forEach((button) => {
            button.setAttribute('aria-expanded', 'false');
            documentObject.getElementById(button.getAttribute('aria-controls')).hidden = true;
        });
    };

    const openSubmenu = (button) => {
        button.setAttribute('aria-expanded', 'true');
        documentObject.getElementById(button.getAttribute('aria-controls')).hidden = false;
    };

    // Open and close the drawer while preserving focus and the previous body scroll state.
    const closeDrawer = (restoreFocus = true) => {
        if (!isOpen) {
            return;
        }

        isOpen = false;
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Open navigation menu');
        drawer.hidden = true;
        overlay.hidden = true;
        openIcon.hidden = false;
        closeIcon.hidden = true;
        documentObject.body.style.overflow = previousBodyOverflow;
        resetSubmenus();

        if (restoreFocus) {
            menuButton.focus();
        }
    };

    const openDrawer = () => {
        isOpen = true;
        previousBodyOverflow = documentObject.body.style.overflow;
        menuButton.setAttribute('aria-expanded', 'true');
        menuButton.setAttribute('aria-label', 'Close navigation menu');
        drawer.hidden = false;
        overlay.hidden = false;
        openIcon.hidden = true;
        closeIcon.hidden = false;
        documentObject.body.style.overflow = 'hidden';
        resetSubmenus();
        const activeSubmenu = submenuButtons.find((button) => button.hasAttribute('data-mobile-active-submenu'));

        if (activeSubmenu) {
            openSubmenu(activeSubmenu);
        }

        focusableElements(drawer)[0]?.focus();
    };

    // Bind the menu, overlay, links, and submenu controls to their drawer actions.
    menuButton.addEventListener('click', () => {
        if (isOpen) {
            closeDrawer();
        } else {
            openDrawer();
        }
    });

    overlay.addEventListener('click', closeDrawer);

    drawer.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', closeDrawer);
    });

    submenuButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const shouldOpen = button.getAttribute('aria-expanded') !== 'true';

            resetSubmenus();

            if (shouldOpen) {
                openSubmenu(button);
            }
        });
    });

    // Clean up mobile state when the layout crosses into the desktop breakpoint.
    desktopQuery.addEventListener('change', (event) => {
        if (event.matches) {
            closeDrawer(false);
        }
    });

    // Handle Escape and keep keyboard focus inside the open modal drawer.
    documentObject.addEventListener('keydown', (event) => {
        if (!isOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeDrawer();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const elements = focusableElements(drawer);
        const first = elements[0];
        const last = elements.at(-1);

        if (!drawer.contains(documentObject.activeElement)) {
            event.preventDefault();
            (event.shiftKey ? last : first).focus();
        } else if (event.shiftKey && documentObject.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && documentObject.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}

if (typeof document !== 'undefined') {
    // Initialize each rendered mobile navigation independently.
    document.querySelectorAll('[data-mobile-navigation]').forEach((navigation) => {
        initializeMobileNavigation(navigation);
    });
}
