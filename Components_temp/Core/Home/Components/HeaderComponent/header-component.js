// Header Component JavaScript
let context = {CONTEXT};

    console.log('Header component loaded.');

    // Initialize header functionality
    initializeThemeToggle();
    initializeNotificationClick();
    initializeUserInfo();
    initializeDropdownButtons();
    
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
    const userDropdown = document.getElementById('user-dropdown');

    if (userInfo) {
        userInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleUserMenu();
        });
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (userDropdown && userInfo && !userInfo.contains(e.target)) {
            closeUserMenu();
        }
    });
}

/**
 * Initialize dropdown button clicks
 */
function initializeDropdownButtons() {
    const logoutBtn = document.getElementById('logout-btn');
    const profileBtn = document.getElementById('user-profile-btn');
    const settingsBtn = document.getElementById('user-settings-btn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleLogout();
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showUserProfile();
        });
    }

    if (settingsBtn) {
        settingsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showUserSettings();
        });
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
 * Toggle user dropdown menu
 */
function toggleUserMenu() {
    const userInfo = document.getElementById('user-info');
    const userDropdown = document.getElementById('user-dropdown');

    if (userInfo && userDropdown) {
        userInfo.classList.toggle('active');
        userDropdown.classList.toggle('active');
    }
}

/**
 * Close user dropdown menu
 */
function closeUserMenu() {
    const userInfo = document.getElementById('user-info');
    const userDropdown = document.getElementById('user-dropdown');

    if (userInfo && userDropdown) {
        userInfo.classList.remove('active');
        userDropdown.classList.remove('active');
    }
}

/**
 * Handle logout with confirmation
 */
async function handleLogout() {
    console.log('[Header] Iniciando logout...');
    console.log('[Header] ConfirmationService disponible:', typeof window.ConfirmationService);
    console.log('[Header] AlertService disponible:', typeof window.AlertService);

    // Cerrar el dropdown
    closeUserMenu();

    // Mostrar confirmación usando ConfirmationService
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.logout()
        : confirm('¿Estás seguro de que deseas cerrar sesión?');

    console.log('[Header] Usuario confirmó logout:', confirmed);

    if (!confirmed) {
        console.log('[Header] Logout cancelado por el usuario');
        return;
    }

    try {
        // Mostrar loading
        if (window.AlertService) {
            window.AlertService.loading('Cerrando sesión...');
        }

        // Llamar al endpoint de logout
        const response = await fetch('/api/auth/admin/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            // Cerrar loading antes de mostrar error
            if (window.AlertService) {
                window.AlertService.close();
            }
            throw new Error(result.msj || 'Error al cerrar sesión');
        }

        // Logout exitoso: redirigir inmediatamente SIN esperar al toast
        // El loading se cierra automáticamente al cambiar de página
        window.location.href = '/login';

    } catch (error) {
        console.error('[Header] Error en logout:', error);

        if (window.AlertService) {
            await window.AlertService.error(error.message || 'Error al cerrar sesión');
        } else {
            alert('Error al cerrar sesión: ' + error.message);
        }
    }
}

/**
 * Show user profile (placeholder)
 */
function showUserProfile() {
    console.log('[Header] Mostrar perfil de usuario');
    closeUserMenu();

    if (window.AlertService) {
        window.AlertService.info('Función de perfil próximamente disponible');
    }
}

/**
 * Show user settings (placeholder)
 */
function showUserSettings() {
    console.log('[Header] Mostrar configuración de usuario');
    closeUserMenu();

    if (window.AlertService) {
        window.AlertService.info('Función de configuración próximamente disponible');
    }
}

// Export functions for external use
window.headerComponent = {
    initializeThemeToggle,
    initializeNotificationClick,
    initializeUserInfo,
    updateUserInfo,
    updateNotificationBadge,
    toggleUserMenu,
    closeUserMenu
};

// Exponer funciones globalmente para onclick
window.handleLogout = handleLogout;
window.showUserProfile = showUserProfile;
window.showUserSettings = showUserSettings;