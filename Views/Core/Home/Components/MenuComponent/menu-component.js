let context = {CONTEXT}

// ===== LEGO MOBILE MENU MODULE =====
const LegoMobileMenu = {
    // State management
    state: {
        isOpen: false,
        isMobile: window.innerWidth < 768,
        touchStart: null,
        touchEnd: null,
    },

    // Initialize the mobile menu system
    init() {
        console.log('Initializing Lego Mobile Menu', context);
        this.bindEvents();
        this.handleResize();
        this.setupSwipeGestures();
        this.setupKeyboardAccessibility();
    },

    // Bind all event listeners
    bindEvents() {
        // Mobile toggle button
        const toggleBtn = document.getElementById('mobile-menu-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleMenu());
        }

        // Mobile overlay
        const overlay = document.getElementById('mobile-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => this.closeMenu());
        }

        // Close buttons
        const closeButtons = document.querySelectorAll('.mobile-close');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.closeMenu());
        });

        // Menu items - close menu when clicked on mobile
        const menuItems = document.querySelectorAll('.menu_item_openable');
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => this.handleMenuItemClick(e));
        });

        // Window resize handler
        window.addEventListener('resize', () => this.handleResize());

        // Escape key to close menu
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.state.isOpen) {
                this.closeMenu();
            }
        });
    },

    // Toggle mobile menu
    toggleMenu() {
        if (this.state.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    },

    // Open mobile menu
    openMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            this.state.isOpen = true;

            // Focus management for accessibility
            const firstFocusable = sidebar.querySelector('button, a, input, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }

            // Announce to screen readers
            this.announceToScreenReader('Menu opened');
        }
    },

    // Close mobile menu
    closeMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
            
            this.state.isOpen = false;

            // Return focus to toggle button
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            if (toggleBtn && this.state.isMobile) {
                toggleBtn.focus();
            }

            // Announce to screen readers
            this.announceToScreenReader('Menu closed');
        }
    },

    // Handle menu item clicks
    handleMenuItemClick(event) {
        if (this.state.isMobile && !event.target.closest('.custom-menu-title')) {
            // Only close if it's a final menu item (not a submenu toggle)
            const hasSubMenu = event.target.closest('.menu_item_openable')?.querySelector('.custom-submenu');
            if (!hasSubMenu) {
                setTimeout(() => this.closeMenu(), 100);
            }
        }
    },

    // Handle window resize
    handleResize() {
        const wasMobile = this.state.isMobile;
        this.state.isMobile = window.innerWidth < 768;

        // If switching from mobile to desktop, close mobile menu
        if (wasMobile && !this.state.isMobile && this.state.isOpen) {
            this.closeMenu();
        }

        // Update sidebar behavior
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            if (this.state.isMobile) {
                sidebar.style.width = '';
            } else {
                // Restore desktop width from localStorage
                const savedWidth = localStorage.getItem('sidebarWidth');
                if (savedWidth && savedWidth >= 200 && savedWidth <= 400) {
                    const widthRem = savedWidth / 16;
                    document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                    sidebar.style.width = savedWidth + 'px';
                }
            }
        }
    },

    // Setup swipe gestures for mobile
    setupSwipeGestures() {
        document.addEventListener('touchstart', (e) => {
            this.state.touchStart = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', (e) => {
            this.state.touchEnd = e.changedTouches[0].screenX;
            this.handleSwipe();
        });
    },

    // Handle swipe gestures
    handleSwipe() {
        if (!this.state.touchStart || !this.state.touchEnd) return;

        const swipeThreshold = 50;
        const diff = this.state.touchStart - this.state.touchEnd;

        if (!this.state.isMobile) return;

        // Swipe right to open menu (from left edge)
        if (diff < -swipeThreshold && this.state.touchStart < 50 && !this.state.isOpen) {
            this.openMenu();
        }

        // Swipe left to close menu
        if (diff > swipeThreshold && this.state.isOpen) {
            this.closeMenu();
        }
    },

    // Setup keyboard accessibility
    setupKeyboardAccessibility() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // Trap focus within sidebar when open on mobile
        sidebar.addEventListener('keydown', (e) => {
            if (!this.state.isMobile || !this.state.isOpen) return;

            if (e.key === 'Tab') {
                this.trapFocus(e, sidebar);
            }
        });
    },

    // Trap focus within element
    trapFocus(event, element) {
        const focusableElements = element.querySelectorAll(
            'button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            lastElement.focus();
            event.preventDefault();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            firstElement.focus();
            event.preventDefault();
        }
    },

    // Announce to screen readers
    announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
};

// ===== DESKTOP RESIZE FUNCTIONALITY =====
const LegoDesktopResize = {
    state: {
        isResizing: false,
        startX: 0,
        startWidth: 0
    },

    init() {
        this.bindEvents();
    },

    bindEvents() {
        const handle = document.getElementById('resize-handle');
        if (handle) {
            handle.addEventListener('mousedown', (e) => this.startResize(e));
        }

        document.addEventListener('mousemove', (e) => this.doResize(e));
        document.addEventListener('mouseup', () => this.endResize());
    },

    startResize(event) {
        // Only enable on desktop
        if (window.innerWidth < 768) return;

        const sidebar = document.querySelector('.sidebar');
        if (!sidebar || sidebar.classList.contains('close')) return;

        this.state.isResizing = true;
        this.state.startX = event.clientX;
        this.state.startWidth = sidebar.offsetWidth;

        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';

        event.preventDefault();
        event.stopPropagation();
    },

    doResize(event) {
        if (!this.state.isResizing || window.innerWidth < 768) return;

        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        const newWidth = this.state.startWidth + (event.clientX - this.state.startX);

        if (newWidth >= 200 && newWidth <= 400) {
            sidebar.style.width = newWidth + 'px';
            
            const widthRem = newWidth / 16;
            document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
        }
    },

    endResize() {
        if (!this.state.isResizing) return;

        this.state.isResizing = false;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';

        // Save width (desktop only)
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && window.innerWidth >= 768) {
            localStorage.setItem('sidebarWidth', sidebar.offsetWidth);
        }
    }
};

// ===== SUBMENU TOGGLE FUNCTIONALITY =====
function toggleSubMenu(element) {
    const submenu = element.nextElementSibling;
    const icon = element.querySelector('ion-icon[name="chevron-forward-outline"]');
    
    if (submenu) {
        const isOpen = submenu.style.display === 'block';
        
        if (isOpen) {
            submenu.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            submenu.style.display = 'block';
            if (icon) icon.style.transform = 'rotate(90deg)';
        }

        // Announce state change to screen readers
        const menuName = element.textContent.trim();
        const state = isOpen ? 'collapsed' : 'expanded';
        LegoMobileMenu.announceToScreenReader(`${menuName} menu ${state}`);
    }
}

// ===== INITIALIZE ON DOM READY =====
document.addEventListener('DOMContentLoaded', function() {
    LegoMobileMenu.init();
    LegoDesktopResize.init();
    
    console.log('Lego Menu System initialized successfully');
});

// ===== EXPORT FOR GLOBAL ACCESS =====
window.LegoMenu = {
    mobile: LegoMobileMenu,
    desktop: LegoDesktopResize,
    toggleSubMenu: toggleSubMenu
};