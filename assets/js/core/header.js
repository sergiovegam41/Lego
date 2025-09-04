/**
 * Header functionality - Theme toggle and interactions
 */

// Initialize header functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeThemeToggle();
    initializeNotificationClick();
});

/**
 * Initialize theme toggle functionality in header
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
    const notificationBtn = document.querySelector('.notification-btn');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Visual feedback
            this.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                this.style.transform = '';
                // Here you can add notification panel toggle logic
                console.log('Notification clicked - implement your notification logic here');
            }, 100);
        });
    }
}

// Export functions for external use
window.headerFunctions = {
    initializeThemeToggle,
    initializeNotificationClick
};