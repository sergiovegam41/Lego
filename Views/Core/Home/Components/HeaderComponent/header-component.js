// Header Component JavaScript
let context = {CONTEXT};

    console.log('Header component loaded');
    
    // Initialize header functionality
    initializeThemeToggle();
    initializeNotificationClick();
    initializeUserInfo();
    
    // Use data from PHP if available
    if (context && context.arg) {
        updateUserInfo(context.arg.user);
        updateNotificationBadge(context.arg.notifications);
    }

/**
 * Initialize theme toggle functionality
 */
function initializeThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    
    if (themeToggle && window.themeManager) {
        themeToggle.addEventListener('click', function() {
            // Visual feedback
            this.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                window.themeManager.toggle();
                this.style.transform = '';
            }, 100);
        });
        
        // Add hover effect
        themeToggle.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        themeToggle.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    }
}

/**
 * Initialize notification button functionality
 */
function initializeNotificationClick() {
    const notificationBtn = document.getElementById('notification-btn');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Visual feedback
            this.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                this.style.transform = '';
                // Here you can add notification panel toggle logic
                showNotifications();
            }, 100);
        });
    }
}

/**
 * Initialize user info click functionality
 */
function initializeUserInfo() {
    const userInfo = document.getElementById('user-info');
    
    if (userInfo) {
        userInfo.addEventListener('click', function() {
            // Visual feedback
            this.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                this.style.transform = '';
                // Here you can add user menu toggle logic
                showUserMenu();
            }, 100);
        });
        
        // Add cursor pointer
        userInfo.style.cursor = 'pointer';
    }
}

/**
 * Update user information from PHP data
 */
function updateUserInfo(userData) {
    if (!userData) return;
    
    const userName = document.querySelector('.user-name');
    const userRole = document.querySelector('.user-role');
    const userAvatar = document.querySelector('.user-avatar ion-icon');
    
    if (userName) userName.textContent = userData.name;
    if (userRole) userRole.textContent = userData.role;
    if (userAvatar) userAvatar.setAttribute('name', userData.avatar);
}

/**
 * Update notification badge from PHP data
 */
function updateNotificationBadge(notificationData) {
    if (!notificationData) return;
    
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = notificationData.count;
        badge.style.display = notificationData.count > 0 ? 'flex' : 'none';
    }
}

/**
 * Show notifications panel (implement your logic here)
 */
function showNotifications() {
    console.log('Show notifications panel - implement your logic here');
    // Example: toggle notification panel, show dropdown, etc.
}

/**
 * Show user menu (implement your logic here)
 */
function showUserMenu() {
    console.log('Show user menu - implement your logic here');
    // Example: show user dropdown with logout, settings, etc.
}

// Export functions for external use
window.headerComponent = {
    initializeThemeToggle,
    initializeNotificationClick,
    initializeUserInfo,
    updateUserInfo,
    updateNotificationBadge
};