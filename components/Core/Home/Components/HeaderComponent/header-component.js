// Header Component JavaScript
let context = {CONTEXT};

    console.log('Header component loaded.');

    // Initialize header functionality
    initializeThemeToggle();
    initializeNotificationClick();
    initializeUserInfo();
    initializeDropdownButtons();
    initializeParamsBadge();
    
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

        // Cerrar loading si está abierto
        if (window.AlertService) {
            window.AlertService.close();
        }

        // Siempre navegar al home/login, incluso si hay error
        // (si no hay sesión, igual queremos sacar al usuario)
        console.log('[Header] Navegando a home a pesar del error...');
        window.location.href = '/';
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

// ═══════════════════════════════════════════════════════════════════
// PARAMS POPOVER FUNCTIONALITY
// ═══════════════════════════════════════════════════════════════════

/**
 * Initialize params badge - updates when module changes or params change
 */
function initializeParamsBadge() {
    // Update badge initially
    updateParamsBadge();
    
    // Listen for module changes - update badge and close popover
    window.addEventListener('lego:module:activated', () => {
        updateParamsBadge();
        closeParamsPopover(); // Close popover when switching modules
    });
    window.addEventListener('lego:module:closed', updateParamsBadge);
    
    // Poll periodically to catch param changes (backup, less frequent now)
    setInterval(updateParamsBadge, 2000);
}

/**
 * Update the params badge with current count
 * Counts individual values, not just top-level keys
 * e.g., columnFilters with 2 filters = 2, not 1
 */
function updateParamsBadge() {
    const badge = document.getElementById('params-badge');
    if (!badge) return;
    
    const params = window.legoWindowManager?.getParams() || {};
    let count = 0;
    
    for (const [key, value] of Object.entries(params)) {
        if (key === 'columnFilters' && typeof value === 'object') {
            // Count individual column filters
            count += Object.keys(value).length;
        } else if (typeof value === 'object' && value !== null) {
            // For other objects, count their keys
            count += Object.keys(value).length;
        } else {
            // Simple values count as 1
            count += 1;
        }
    }
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

/**
 * Toggle params popover visibility
 */
function toggleParamsPopover() {
    const popover = document.getElementById('params-popover');
    if (!popover) return;

    if (popover.classList.contains('active')) {
        closeParamsPopover();
    } else {
        openParamsPopover();
    }
}

/**
 * Open params popover and populate with data
 */
function openParamsPopover() {
    const popover = document.getElementById('params-popover');
    if (!popover) return;

    // Get active module and its params
    const activeModuleId = window.moduleStore?.getActiveModule();
    const params = window.legoWindowManager?.getParams() || {};

    // Update module info
    const moduleInfo = document.getElementById('params-module-info');
    if (moduleInfo) {
        moduleInfo.innerHTML = activeModuleId 
            ? `<strong>Módulo:</strong> ${escapeHtml(activeModuleId)}`
            : '<em>No hay módulo activo</em>';
    }

    // Populate params content
    const content = document.getElementById('params-content');
    if (content) {
        renderParams(content, params, activeModuleId);
    }

    popover.classList.add('active');

    // Remove existing listener first to prevent duplicates (Bug fix)
    document.removeEventListener('click', handleParamsClickOutside);
    
    // Close on click outside
    setTimeout(() => {
        document.addEventListener('click', handleParamsClickOutside);
    }, 10);
}

/**
 * Close params popover
 */
function closeParamsPopover() {
    const popover = document.getElementById('params-popover');
    if (popover) {
        popover.classList.remove('active');
    }
    document.removeEventListener('click', handleParamsClickOutside);
}

/**
 * Handle click outside popover
 */
function handleParamsClickOutside(e) {
    const popover = document.getElementById('params-popover');
    const container = document.querySelector('.params-popover-container');
    
    if (container && !container.contains(e.target)) {
        closeParamsPopover();
    }
}

/**
 * Format a value in a friendly way (with XSS protection)
 */
function formatValueFriendly(key, value) {
    // Si es un objeto (como columnFilters), formatearlo de manera amigable
    if (typeof value === 'object' && value !== null) {
        // Caso especial: filtros de columnas
        if (key === 'columnFilters') {
            const filters = [];
            for (const [col, filter] of Object.entries(value)) {
                const filterValue = filter.filter || filter.value || JSON.stringify(filter);
                filters.push(`<span class="param-filter-item">${escapeHtml(col)}: <strong>${escapeHtml(String(filterValue))}</strong></span>`);
            }
            return filters.length > 0 
                ? filters.join('<br>') 
                : '<em>Sin filtros</em>';
        }
        
        // Para otros objetos, mostrar pares clave-valor
        const pairs = [];
        for (const [k, v] of Object.entries(value)) {
            const displayValue = typeof v === 'object' ? JSON.stringify(v) : v;
            pairs.push(`${escapeHtml(k)}: ${escapeHtml(String(displayValue))}`);
        }
        return pairs.join('<br>') || '<em>Vacío</em>';
    }
    
    // Valores simples
    return escapeHtml(String(value));
}

/**
 * Get a friendly label for param keys
 */
function getFriendlyKeyLabel(key) {
    const labels = {
        'columnFilters': '🔍 Filtros de columnas',
        'sortModel': '↕️ Ordenamiento',
        'page': '📄 Página',
        'scrollPosition': '📍 Posición scroll'
    };
    return labels[key] || key;
}

/**
 * Render params in the popover content
 */
function renderParams(container, params, moduleId) {
    const keys = Object.keys(params);

    if (keys.length === 0) {
        container.innerHTML = `
            <div class="params-popover__empty">
                <ion-icon name="file-tray-outline"></ion-icon>
                No hay parámetros persistentes
            </div>
        `;
        // Disable clear button
        const clearBtn = document.querySelector('.params-popover__clear-btn');
        if (clearBtn) clearBtn.disabled = true;
        return;
    }

    // Enable clear button
    const clearBtn = document.querySelector('.params-popover__clear-btn');
    if (clearBtn) clearBtn.disabled = false;

    container.innerHTML = keys.map(key => {
        const value = params[key];
        const friendlyLabel = getFriendlyKeyLabel(key);
        const friendlyValue = formatValueFriendly(key, value);
        const escapedKey = escapeHtml(key);
        // Escape for JS string context (replace quotes and backslashes)
        const jsEscapedKey = key.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        
        return `
            <div class="param-item" data-param-key="${escapedKey}">
                <div class="param-item__header">
                    <span class="param-item__key">${escapeHtml(friendlyLabel)}</span>
                    <div class="param-item__actions">
                        <button class="param-item__btn param-item__btn--delete" 
                                onclick="deleteParam('${jsEscapedKey}')" 
                                title="Eliminar">
                            <ion-icon name="trash-outline"></ion-icon>
                        </button>
                    </div>
                </div>
                <div class="param-item__value">${friendlyValue}</div>
            </div>
        `;
    }).join('');
}

/**
 * Delete a single param and refresh popover + module
 */
function deleteParam(key) {
    if (window.legoWindowManager) {
        window.legoWindowManager.removeParam(key);
        console.log(`[Header] Param "${key}" eliminado`);
    }
    // Update badge
    updateParamsBadge();
    
    // Refresh popover content only (not the whole popover to avoid listener issues)
    const popover = document.getElementById('params-popover');
    if (popover && popover.classList.contains('active')) {
        const activeModuleId = window.moduleStore?.getActiveModule();
        const params = window.legoWindowManager?.getParams() || {};
        const content = document.getElementById('params-content');
        if (content) {
            renderParams(content, params, activeModuleId);
        }
    }
    
    // Reload active module to reflect parameter removal
    if (window.legoWindowManager) {
        window.legoWindowManager.reloadActive();
    }
}

/**
 * Clear all params for current module and refresh
 */
function clearAllParams() {
    const activeModuleId = window.moduleStore?.getActiveModule();
    
    if (!activeModuleId) {
        console.warn('[Header] No hay módulo activo');
        return;
    }

    if (window.legoWindowManager) {
        window.legoWindowManager.clearParams();
        console.log(`[Header] Todos los params eliminados para ${activeModuleId}`);
    }

    // Update badge
    updateParamsBadge();

    // Close the popover
    closeParamsPopover();

    // Show feedback
    if (window.AlertService?.toast) {
        window.AlertService.toast('Parámetros limpiados', 'success');
    }

    // Refresh the active module
    if (window.legoWindowManager) {
        window.legoWindowManager.reloadActive();
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export functions for external use
window.headerComponent = {
    initializeThemeToggle,
    initializeNotificationClick,
    initializeUserInfo,
    updateUserInfo,
    updateNotificationBadge,
    toggleUserMenu,
    closeUserMenu,
    toggleParamsPopover,
    closeParamsPopover
};

// Exponer funciones globalmente para onclick
window.handleLogout = handleLogout;
window.showUserProfile = showUserProfile;
window.showUserSettings = showUserSettings;
window.toggleParamsPopover = toggleParamsPopover;
window.closeParamsPopover = closeParamsPopover;
window.deleteParam = deleteParam;
window.clearAllParams = clearAllParams;