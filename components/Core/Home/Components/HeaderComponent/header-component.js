// Header Component JavaScript
let context = {CONTEXT};


    // Initialize header functionality
    initializeThemeToggle();
    initializeNotificationClick();
    initializeUserInfo();
    initializeDropdownButtons();
    initializeParamsBadge();
    loadSystemMenuItems();
    
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

    // Cerrar el dropdown
    closeUserMenu();

    // Mostrar confirmación usando ConfirmationService
    const confirmed = window.ConfirmationService
        ? await window.ConfirmationService.logout()
        : confirm('¿Estás seguro de que deseas cerrar sesión?');


    if (!confirmed) {
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
        window.location.href = '/';
    }
}

/**
 * Show user profile (placeholder)
 */
function showUserProfile() {
    closeUserMenu();

    if (window.AlertService) {
        window.AlertService.info('Función de perfil próximamente disponible');
    }
}

/**
 * Show user settings - opens configuration popover
 */
function showUserSettings() {
    closeUserMenu();
    openConfigPopover();
}

/**
 * Load configuration menu items - Configuración manual, no automática
 * El usuario define qué items quiere mostrar aquí
 */
async function loadSystemMenuItems() {
    const container = document.getElementById('config-content');
    if (!container) return;

    // Configuración manual de items para el popover de configuración
    // Estos items pueden estar en cualquier parte del menú, no necesariamente ocultos
    const configItems = [
        {
            id: 'menu-config',
            label: 'Configuración del Menú',
            route: '/component/menu-config',
            icon: 'settings-outline',
            hasChildren: false
        },
        {
            id: 'roles-config',
            label: 'Gestión de Roles',
            route: null, // Es un grupo, no tiene ruta directa
            icon: 'shield-outline',
            hasChildren: true
        },
        {
            id: 'users-config',
            label: 'Gestión de Usuarios',
            route: '/component/users-config',
            icon: 'people-outline',
            hasChildren: false
        }
    ];

    try {
        // Render items with support for nested groups
        const html = await renderSystemMenuItems(configItems);
        console.log('[loadSystemMenuItems] HTML generado:', html.substring(0, 200) + '...');
        container.innerHTML = html;
        
        // Esperar un momento para que el DOM se actualice
        setTimeout(() => {
            // Verificar que los grupos estén en el DOM
            const groupsInDOM = container.querySelectorAll('.config-popover__group');
            console.log('[loadSystemMenuItems] Grupos en DOM después de innerHTML:', groupsInDOM.length);
            
            // Agregar event delegation para los grupos
            setupConfigGroupListeners(container);
        }, 10);

    } catch (error) {
        console.error('[Header] Error cargando items de configuración:', error);
        container.innerHTML = '<div class="config-popover__error">Error al cargar opciones</div>';
    }
}

/**
 * Setup event listeners for configuration groups using event delegation
 */
function setupConfigGroupListeners(container) {
    console.log('[setupConfigGroupListeners] Configurando listeners, container:', container);
    console.log('[setupConfigGroupListeners] Container HTML:', container.innerHTML.substring(0, 300));
    
    if (!container) {
        console.error('[setupConfigGroupListeners] Container no encontrado');
        return;
    }
    
    // Verificar que los grupos existan
    const groups = container.querySelectorAll('.config-popover__group');
    console.log('[setupConfigGroupListeners] Grupos encontrados:', groups.length);
    groups.forEach((group, index) => {
        console.log(`[setupConfigGroupListeners] Grupo ${index}:`, group.getAttribute('data-group-id'), group);
    });
    
    // Event delegation para los headers de grupos
    container.addEventListener('click', function(e) {
        console.log('[setupConfigGroupListeners] Click capturado en container, target:', e.target);
        
        // Buscar primero el grupo, luego verificar si el click fue en el header
        const groupElement = e.target.closest('.config-popover__group');
        if (!groupElement) {
            // No es un grupo, ignorar
            return;
        }
        
        // Verificar si el click fue en el header o en sus hijos
        const groupHeader = groupElement.querySelector('.config-popover__group-header');
        if (!groupHeader) {
            console.log('[setupConfigGroupListeners] Grupo encontrado pero sin header');
            return;
        }
        
        // Verificar si el click fue dentro del header (incluyendo sus hijos)
        if (!groupHeader.contains(e.target)) {
            // El click fue en el grupo pero no en el header (probablemente en el contenido expandido)
            return;
        }
        
        console.log('[setupConfigGroupListeners] Click en group-header detectado');
        
        e.preventDefault();
        e.stopPropagation();
        
        const groupId = groupElement.getAttribute('data-group-id');
        if (!groupId) {
            console.error('[setupConfigGroupListeners] No se encontró data-group-id');
            return;
        }
        
        console.log('[setupConfigGroupListeners] Click detectado en grupo:', groupId);
        
        if (window.handleConfigGroupClick) {
            console.log('[setupConfigGroupListeners] Llamando handleConfigGroupClick');
            window.handleConfigGroupClick(groupId);
        } else {
            console.error('[setupConfigGroupListeners] handleConfigGroupClick no está disponible');
        }
    });
    
    console.log('[setupConfigGroupListeners] Listener agregado correctamente');
}

/**
 * Render system menu items with nested structure
 */
async function renderSystemMenuItems(items) {
    let html = '<div class="config-popover__items">';
    
    for (const item of items) {
        const label = item.index_label || item.label;
        const route = item.route || null;
        const icon = item.icon || 'settings-outline';
        const escapedId = escapeHtml(item.id);
        const escapedLabel = escapeHtml(label);
        const escapedIcon = escapeHtml(icon);
        const hasChildren = item.hasChildren || item.has_children || false;
        
        if (hasChildren) {
            // Item with children - render as expandable group, pero también clicable para abrir el primer hijo
            html += `
                <div class="config-popover__group" data-group-id="${escapedId}">
                    <div class="config-popover__group-header" style="cursor: pointer;">
                        <ion-icon name="${escapedIcon}"></ion-icon>
                        <span>${escapedLabel}</span>
                        <ion-icon name="chevron-forward-outline" class="config-popover__chevron"></ion-icon>
                    </div>
                    <div class="config-popover__group-content" id="config-group-${escapedId}">
                        <div class="config-popover__loading">Cargando...</div>
                    </div>
                </div>
            `;
        } else {
            // Simple item - render as clickable option
            const routeAttr = route ? `onclick="openSystemMenuItem('${escapedId}', '${escapeHtml(route)}')"` : '';
            html += `
                <div class="config-popover__item" data-menu-item-id="${escapedId}" ${routeAttr}>
                    <ion-icon name="${escapedIcon}"></ion-icon>
                    <span>${escapedLabel}</span>
                </div>
            `;
        }
    }
    
    html += '</div>';
    return html;
}


/**
 * Handle click on configuration group - expand/collapse or open first child
 */
async function handleConfigGroupClick(groupId) {
    console.log('[handleConfigGroupClick] ========== FUNCIÓN LLAMADA ==========');
    console.log('[handleConfigGroupClick] Grupo ID:', groupId);
    console.log('[handleConfigGroupClick] Stack trace:', new Error().stack);
    
    if (!groupId) {
        console.error('[handleConfigGroupClick] groupId no proporcionado');
        return;
    }
    
    // Primero, intentar abrir directamente el primer hijo usando openSystemMenuItem
    // Esto obtendrá la jerarquía y abrirá automáticamente el primer hijo
    try {
        // Obtener la jerarquía del grupo para encontrar el primer hijo
        const hierarchyResponse = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(groupId)}`);
        const hierarchyResult = await hierarchyResponse.json();
        
        if (hierarchyResult.success && hierarchyResult.data) {
            const hierarchy = hierarchyResult.data;
            const item = hierarchy.item || {};
            const children = hierarchy.children || [];
            
            if (children.length > 0) {
                let childToOpen = null;
                
                // Si tiene default_child_id configurado, buscar ese hijo específico
                if (item.default_child_id) {
                    childToOpen = children.find(child => child.id === item.default_child_id);
                    console.log('[handleConfigGroupClick] default_child_id configurado:', item.default_child_id, 'hijo encontrado:', childToOpen?.id);
                }
                
                // Si no se encontró el hijo por defecto o no está configurado, usar el primer hijo con ruta válida
                if (!childToOpen) {
                    childToOpen = children.find(child => child.route && child.route !== '#') || children[0];
                    console.log('[handleConfigGroupClick] Usando primer hijo con ruta válida:', childToOpen?.id);
                }
                
                if (childToOpen && childToOpen.route && childToOpen.route !== '#') {
                    console.log('[handleConfigGroupClick] Abriendo hijo:', childToOpen.id, 'route:', childToOpen.route);
                    // Abrir el hijo directamente
                    await openSystemMenuItem(childToOpen.id, childToOpen.route);
                    return; // Salir, no expandir/colapsar
                }
            }
        }
    } catch (error) {
        console.error('[handleConfigGroupClick] Error obteniendo jerarquía:', error);
    }
    
    // Si no se pudo abrir el primer hijo, expandir/colapsar como antes
    const groupContent = document.getElementById(`config-group-${groupId}`);
    const groupHeader = document.querySelector(`[data-group-id="${groupId}"] .config-popover__group-header`);
    const chevron = groupHeader?.querySelector('.config-popover__chevron');
    
    if (!groupContent) return;
    
    const isExpanded = groupContent.classList.contains('expanded');
    
    if (isExpanded) {
        // Collapse
        groupContent.classList.remove('expanded');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    } else {
        // Expand - load children if not loaded
        if (groupContent.innerHTML.includes('Cargando')) {
            await loadGroupChildren(groupId, groupContent);
        }
        groupContent.classList.add('expanded');
        if (chevron) chevron.style.transform = 'rotate(90deg)';
        
        // Si hay hijos, abrir el primero automáticamente
        const firstChild = groupContent.querySelector('.config-popover__item');
        if (firstChild) {
            const childId = firstChild.getAttribute('data-menu-item-id');
            const childRoute = firstChild.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
            if (childId && childRoute) {
                // Pequeño delay para que se vea la expansión
                setTimeout(() => {
                    openSystemMenuItem(childId, childRoute);
                }, 100);
            }
        }
    }
}

/**
 * Toggle a configuration group (expand/collapse) - kept for compatibility
 */
async function toggleConfigGroup(groupId) {
    await handleConfigGroupClick(groupId);
}

/**
 * Load children of a menu group
 */
async function loadGroupChildren(groupId, container) {
    try {
        const response = await fetch(`/api/menu-config/list`);
        const result = await response.json();
        
        if (!result.success || !result.data) {
            container.innerHTML = '<div class="config-popover__error">Error al cargar subopciones</div>';
            return;
        }
        
        // Filtrar hijos del grupo y ordenarlos
        const children = result.data
            .filter(item => item.parent_id === groupId)
            .filter(item => !item.is_dynamic) // Excluir items dinámicos
            .sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        
        if (children.length === 0) {
            container.innerHTML = '<div class="config-popover__empty">No hay subopciones</div>';
            return;
        }
        
        container.innerHTML = children.map(child => {
            const label = child.index_label || child.label;
            const route = child.route || null;
            const icon = child.icon || 'ellipse-outline';
            const escapedId = escapeHtml(child.id);
            const escapedLabel = escapeHtml(label);
            const escapedIcon = escapeHtml(icon);
            const routeAttr = route ? `onclick="openSystemMenuItem('${escapedId}', '${escapeHtml(route)}')"` : '';
            
            return `
                <div class="config-popover__item config-popover__item--nested" data-menu-item-id="${escapedId}" ${routeAttr}>
                    <ion-icon name="${escapedIcon}"></ion-icon>
                    <span>${escapedLabel}</span>
                </div>
            `;
        }).join('');
        
    } catch (error) {
        console.error('[Header] Error cargando hijos del grupo:', error);
        container.innerHTML = '<div class="config-popover__error">Error al cargar subopciones</div>';
    }
}

/**
 * Open a system menu item as a window (not navigation)
 */
async function openSystemMenuItem(itemId, route) {
    console.log('[openSystemMenuItem] Iniciando, itemId:', itemId, 'route:', route);
    
    closeConfigPopover();

    // Obtener la jerarquía completa del item (padres e hijos) PRIMERO
    // para determinar si es un grupo y debemos abrir el primer hijo
    console.log('[openSystemMenuItem] Obteniendo jerarquía del item');
    let hierarchy = null;
    try {
        const hierarchyResponse = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(itemId)}`);
        const hierarchyResult = await hierarchyResponse.json();
        if (hierarchyResult.success && hierarchyResult.data) {
            hierarchy = hierarchyResult.data;
            console.log('[openSystemMenuItem] Jerarquía obtenida:', hierarchy);
            
            // Los grupos no se abren directamente, siempre abren su primer hijo (o el configurado)
            const children = hierarchy.children || [];
            const item = hierarchy.item || {};
            const hasChildren = children.length > 0;
            const hasChildrenFlag = item.has_children === true;
            
            if (hasChildren || hasChildrenFlag) {
                console.log('[openSystemMenuItem] Item tiene hijos, buscando hijo a abrir. Total hijos:', children.length);
                
                let childToOpen = null;
                
                // Si tiene default_child_id configurado, buscar ese hijo específico
                if (item.default_child_id) {
                    childToOpen = children.find(child => child.id === item.default_child_id);
                    console.log('[openSystemMenuItem] default_child_id configurado:', item.default_child_id, 'hijo encontrado:', childToOpen?.id);
                }
                
                // Si no se encontró el hijo por defecto o no está configurado, usar el primer hijo con ruta válida
                if (!childToOpen) {
                    const firstChild = children[0];
                    if (firstChild) {
                        // Buscar el primer hijo que tenga ruta válida
                        childToOpen = firstChild.route && firstChild.route !== '#' 
                            ? firstChild 
                            : children.find(child => child.route && child.route !== '#') || firstChild;
                        console.log('[openSystemMenuItem] Usando primer hijo con ruta válida:', childToOpen?.id);
                    }
                }
                
                if (childToOpen && childToOpen.route && childToOpen.route !== '#') {
                    console.log('[openSystemMenuItem] Item es grupo con hijos, abriendo hijo:', childToOpen.id, 'route:', childToOpen.route);
                    // Recursivamente abrir el hijo
                    return await openSystemMenuItem(childToOpen.id, childToOpen.route);
                } else {
                    console.warn('[openSystemMenuItem] Grupo no tiene hijos con ruta válida. childToOpen:', childToOpen);
                }
            } else {
                console.log('[openSystemMenuItem] Item no tiene hijos, continuando con apertura normal');
            }
        }
    } catch (error) {
        console.error('[openSystemMenuItem] Error obteniendo jerarquía:', error);
    }

    if (!route || route === '#') {
        console.warn('[openSystemMenuItem] Ruta no válida:', route);
        if (window.AlertService) {
            window.AlertService.warning('Esta opción no tiene ruta configurada');
        }
        return;
    }

    // Obtener el label del item para mostrar en el menú
    // Intentar obtenerlo del popover primero, luego del DOM general
    let itemLabel = document.querySelector(`[data-menu-item-id="${itemId}"]`)?.textContent?.trim();
    console.log('[openSystemMenuItem] Label desde DOM general:', itemLabel);
    
    if (!itemLabel) {
        // Buscar en el popover de configuración
        const configItem = document.querySelector(`#config-content [data-menu-item-id="${itemId}"]`);
        itemLabel = configItem?.textContent?.trim() || itemId;
        console.log('[openSystemMenuItem] Label desde popover:', itemLabel);
    }
    
    // Si no tenemos jerarquía aún, obtenerla ahora
    if (!hierarchy) {
        try {
            const hierarchyResponse = await fetch(`/api/menu/item-hierarchy?id=${encodeURIComponent(itemId)}`);
            const hierarchyResult = await hierarchyResponse.json();
            if (hierarchyResult.success && hierarchyResult.data) {
                hierarchy = hierarchyResult.data;
            }
        } catch (error) {
            console.error('[openSystemMenuItem] Error obteniendo jerarquía:', error);
        }
    }

    // Agregar directamente al menú como item dinámico (sin carpeta padre)
    const menuContainer = document.querySelector('.custom-menu');
    console.log('[openSystemMenuItem] Menu container encontrado:', !!menuContainer);
    
    if (menuContainer) {
        // Verificar si el item ya existe EN EL MENÚ LATERAL (no en el popover)
        const existingItem = menuContainer.querySelector(`[data-menu-item-id="${itemId}"]:not([data-temp-item="true"])`);
        console.log('[openSystemMenuItem] Item existente encontrado en menú:', !!existingItem);
        
        if (existingItem) {
            // Si ya existe, verificar si tiene jerarquía completa o necesita agregarla
            console.log('[openSystemMenuItem] Item ya existe, verificando jerarquía completa');
            console.log('[openSystemMenuItem] Item encontrado:', existingItem);
            console.log('[openSystemMenuItem] Item display:', window.getComputedStyle(existingItem).display);
            console.log('[openSystemMenuItem] Item visibility:', window.getComputedStyle(existingItem).visibility);
            console.log('[openSystemMenuItem] Item offsetParent:', existingItem.offsetParent);
            console.log('[openSystemMenuItem] Item parent:', existingItem.parentElement);
            console.log('[openSystemMenuItem] Item parent classes:', existingItem.parentElement?.className);
            console.log('[openSystemMenuItem] Item parent display:', existingItem.parentElement ? window.getComputedStyle(existingItem.parentElement).display : 'N/A');
            
            // Si tenemos jerarquía, verificar que todos los elementos estén presentes
            if (hierarchy) {
                const ancestors = hierarchy.ancestors || [];
                const children = hierarchy.children || [];
                
                console.log('[openSystemMenuItem] Ancestros en jerarquía:', ancestors.length);
                console.log('[openSystemMenuItem] Hijos en jerarquía:', children.length);
                
                // Verificar que todos los ancestros existan EN EL MENÚ
                let missingAncestors = [];
                for (const ancestor of ancestors) {
                    const ancestorExists = menuContainer.querySelector(`[data-menu-item-id="${ancestor.id}"]:not([data-temp-item="true"])`);
                    if (!ancestorExists) {
                        missingAncestors.push(ancestor);
                        console.log('[openSystemMenuItem] Ancestro faltante:', ancestor.id);
                    }
                }
                
                // Verificar que todos los hijos existan EN EL MENÚ
                let missingChildren = [];
                const checkChildren = (childList) => {
                    for (const child of childList) {
                        const childExists = menuContainer.querySelector(`[data-menu-item-id="${child.id}"]:not([data-temp-item="true"])`);
                        if (!childExists) {
                            missingChildren.push(child);
                            console.log('[openSystemMenuItem] Hijo faltante:', child.id);
                        }
                        if (child.children && child.children.length > 0) {
                            checkChildren(child.children);
                        }
                    }
                };
                checkChildren(children);
                
                // Si faltan elementos, agregarlos usando MenuHiddenItemsManager
                if (missingAncestors.length > 0 || missingChildren.length > 0) {
                    console.log('[openSystemMenuItem] Faltan elementos en la jerarquía, agregándolos');
                    console.log('[openSystemMenuItem] Ancestros faltantes:', missingAncestors.length);
                    console.log('[openSystemMenuItem] Hijos faltantes:', missingChildren.length);
                    if (window.menuHiddenItemsManager) {
                        await window.menuHiddenItemsManager.addHiddenItemAsDynamic(itemId, hierarchy);
                    }
                } else {
                    console.log('[openSystemMenuItem] Jerarquía completa ya está presente');
                }
            }
            
            // Asegurarse de que esté visible y no oculto por filtros
            console.log('[openSystemMenuItem] Forzando visibilidad del item');
            existingItem.style.display = '';
            existingItem.style.visibility = 'visible';
            existingItem.style.opacity = '1';
            existingItem.classList.remove('menu-filter-hidden');
            existingItem.classList.add('menu-filter-visible');
            
            // Asegurar que los padres estén expandidos y visibles
            let parent = existingItem.parentElement;
            let level = 0;
            console.log('[openSystemMenuItem] Recorriendo padres, nivel inicial:', level);
            
            while (parent && level < 10) { // Limitar a 10 niveles para evitar loops infinitos
                console.log('[openSystemMenuItem] Padre nivel', level, ':', parent.tagName, parent.className);
                
                if (parent.classList.contains('custom-menu-section')) {
                    console.log('[openSystemMenuItem] Es custom-menu-section, forzando visibilidad');
                    parent.style.display = '';
                    parent.style.visibility = 'visible';
                    parent.style.opacity = '1';
                    parent.classList.remove('menu-filter-hidden');
                    parent.classList.add('menu-filter-visible');
                    
                    const submenu = parent.querySelector('.custom-submenu');
                    if (submenu) {
                        console.log('[openSystemMenuItem] Submenu encontrado, expandiendo');
                        submenu.style.display = 'block';
                        submenu.style.visibility = 'visible';
                        submenu.style.opacity = '1';
                        parent.classList.add('expanded');
                    } else {
                        console.log('[openSystemMenuItem] No se encontró submenu en este padre');
                    }
                } else if (parent.classList.contains('custom-submenu')) {
                    console.log('[openSystemMenuItem] Es custom-submenu, forzando visibilidad');
                    parent.style.display = 'block';
                    parent.style.visibility = 'visible';
                    parent.style.opacity = '1';
                } else if (parent.classList.contains('custom-menu')) {
                    console.log('[openSystemMenuItem] Es contenedor principal del menú');
                    break; // Llegamos al contenedor principal
                }
                
                parent = parent.parentElement;
                level++;
            }
            
            // También asegurar que todos los hijos estén visibles
            if (hierarchy && hierarchy.children) {
                console.log('[openSystemMenuItem] Forzando visibilidad de hijos');
                const makeChildrenVisible = (children) => {
                    children.forEach(child => {
                        const childElement = menuContainer.querySelector(`[data-menu-item-id="${child.id}"]:not([data-temp-item="true"])`);
                        if (childElement) {
                            console.log('[openSystemMenuItem] Haciendo visible hijo:', child.id);
                            childElement.style.display = '';
                            childElement.style.visibility = 'visible';
                            childElement.style.opacity = '1';
                            childElement.classList.remove('menu-filter-hidden');
                            childElement.classList.add('menu-filter-visible');
                            
                            // Si tiene hijos, hacerlos visibles también
                            if (child.children && child.children.length > 0) {
                                makeChildrenVisible(child.children);
                            }
                        } else {
                            console.log('[openSystemMenuItem] Hijo no encontrado en menú:', child.id);
                        }
                    });
                };
                makeChildrenVisible(hierarchy.children);
            }
            
            // Verificación final - buscar en el menú, no en todo el documento
            const finalCheck = menuContainer.querySelector(`[data-menu-item-id="${itemId}"]:not([data-temp-item="true"])`);
            console.log('[openSystemMenuItem] Verificación final del item:');
            console.log('[openSystemMenuItem] - Existe:', !!finalCheck);
            console.log('[openSystemMenuItem] - Display:', finalCheck ? window.getComputedStyle(finalCheck).display : 'N/A');
            console.log('[openSystemMenuItem] - Visibility:', finalCheck ? window.getComputedStyle(finalCheck).visibility : 'N/A');
            console.log('[openSystemMenuItem] - Opacity:', finalCheck ? window.getComputedStyle(finalCheck).opacity : 'N/A');
            console.log('[openSystemMenuItem] - OffsetParent:', finalCheck ? !!finalCheck.offsetParent : 'N/A');
            console.log('[openSystemMenuItem] - Parent display:', finalCheck?.parentElement ? window.getComputedStyle(finalCheck.parentElement).display : 'N/A');
            console.log('[openSystemMenuItem] - Parent visibility:', finalCheck?.parentElement ? window.getComputedStyle(finalCheck.parentElement).visibility : 'N/A');
            
            console.log('[openSystemMenuItem] Visibilidad forzada para item y jerarquía');
            
            // Abrir el módulo
            if (typeof window.openModule === 'function') {
                window.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
                console.log('[openSystemMenuItem] Módulo abierto con window.openModule');
                
                // Asegurar que el estado visual se actualice después de abrir
                setTimeout(() => {
                    if (window.menuStateManager) {
                        window.menuStateManager.syncWithModuleStore();
                    }
                    // También actualizar el menú usando updateMenu si está disponible
                    if (typeof updateMenu === 'function') {
                        updateMenu();
                    }
                }, 150);
            } else if (window.legoWindowManager && window.legoWindowManager.openModule) {
                window.legoWindowManager.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
                console.log('[openSystemMenuItem] Módulo abierto con legoWindowManager.openModule');
                
                // Asegurar que el estado visual se actualice después de abrir
                setTimeout(() => {
                    if (window.menuStateManager) {
                        window.menuStateManager.syncWithModuleStore();
                    }
                }, 150);
            } else {
                console.error('[openSystemMenuItem] Ninguna función openModule disponible');
            }
            return;
        }
        
        // Si tenemos jerarquía, usar MenuHiddenItemsManager para agregar todo (padres e hijos)
        if (hierarchy && window.menuHiddenItemsManager) {
            console.log('[openSystemMenuItem] Usando MenuHiddenItemsManager para agregar jerarquía completa');
            await window.menuHiddenItemsManager.addHiddenItemAsDynamic(itemId, hierarchy);
            console.log('[openSystemMenuItem] Jerarquía agregada al menú');
        } else {
            // Fallback: crear el item directamente en el menú (sin jerarquía)
            console.log('[openSystemMenuItem] Fallback: creando item sin jerarquía');
            const itemIcon = 'settings-outline';
            
            const menuItemHTML = `
                <div class="custom-menu-section menu_item_openable dynamic-menu-item"
                    moduleId="${itemId}"
                    moduleUrl="${route}"
                    data-menu-item-id="${itemId}"
                    data-module-id="${itemId}"
                    data-module-url="${route}"
                    data-dynamic-item="true">
                    <button class="menu-close-button" title="Cerrar">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                    <button class="custom-button level-0">
                        <ion-icon name="${itemIcon}" class="icon_menu"></ion-icon>
                        <p class="text_menu_option">${itemLabel}</p>
                        <div class="menu-state-indicator"></div>
                    </button>
                </div>
            `;
            
            menuContainer.insertAdjacentHTML('beforeend', menuItemHTML);
            
            // Agregar listener de click
            const newMenuItem = document.querySelector(`[data-menu-item-id="${itemId}"]`);
            if (newMenuItem) {
                newMenuItem.addEventListener('click', function(e) {
                    if (e.target.closest('.menu-close-button')) return;
                    
                    const id = this.getAttribute('moduleId') || this.getAttribute('data-menu-item-id');
                    const url = this.getAttribute('moduleUrl') || '#';
                    const name = this.querySelector('.text_menu_option')?.textContent || id;
                    
                    if (window.moduleStore && window.moduleStore.getActiveModule() !== id) {
                        if (typeof window.openModule === 'function') {
                            window.openModule(id, url, name, { url, name });
                        }
                    }
                });
            }
            
            // Agregar a window.lego.menu para breadcrumb
            if (window.lego && window.lego.menu && Array.isArray(window.lego.menu)) {
                window.lego.menu.push({
                    id: itemId,
                    name: itemLabel,
                    url: route,
                    iconName: itemIcon,
                    level: 0,
                    childs: [],
                    isDynamic: true
                });
            }
        }
        
        // Abrir el módulo después de agregar al menú
        console.log('[openSystemMenuItem] Intentando abrir módulo');
        
        // Pequeño delay para asegurar que el DOM esté actualizado
        setTimeout(() => {
            if (typeof window.openModule === 'function') {
                window.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
                console.log('[openSystemMenuItem] Módulo abierto exitosamente con window.openModule');
                
                // Asegurar que el estado visual se actualice después de abrir
                // El evento lego:module:activated también disparará syncWithModuleStore
                // pero agregamos un timeout adicional para asegurar que el item esté en el DOM
                setTimeout(() => {
                    if (window.menuStateManager) {
                        window.menuStateManager.syncWithModuleStore();
                    }
                    // También actualizar el menú usando updateMenu si está disponible
                    if (typeof updateMenu === 'function') {
                        updateMenu();
                    }
                }, 200);
            } else if (window.legoWindowManager && window.legoWindowManager.openModule) {
                window.legoWindowManager.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
                console.log('[openSystemMenuItem] Módulo abierto exitosamente con legoWindowManager.openModule');
                
                // Asegurar que el estado visual se actualice después de abrir
                setTimeout(() => {
                    if (window.menuStateManager) {
                        window.menuStateManager.syncWithModuleStore();
                    }
                }, 200);
            } else {
                console.error('[openSystemMenuItem] No se puede abrir el módulo: ninguna función openModule disponible');
            }
        }, 100);
    } else {
        console.log('[openSystemMenuItem] Menu container no encontrado, usando fallback');
        // Fallback: usar window.openModule o navegación directa
        if (typeof window.openModule === 'function') {
            window.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
            console.log('[openSystemMenuItem] Fallback: módulo abierto con window.openModule');
        } else if (window.legoWindowManager && window.legoWindowManager.openModule) {
            window.legoWindowManager.openModule(itemId, route, itemLabel, { url: route, name: itemLabel });
            console.log('[openSystemMenuItem] Fallback: módulo abierto con legoWindowManager.openModule');
        } else {
            console.error('[openSystemMenuItem] Último fallback: navegación directa');
            window.location.href = route;
        }
    }
}

/**
 * Open configuration popover
 */
function openConfigPopover() {
    const popover = document.getElementById('config-popover');
    const container = document.querySelector('.config-popover-container');
    const settingsBtn = document.getElementById('user-settings-btn');
    
    if (!popover || !container || !settingsBtn) return;

    // Position popover near the settings button
    const btnRect = settingsBtn.getBoundingClientRect();
    const headerRect = document.getElementById('top-header')?.getBoundingClientRect();
    
    if (headerRect) {
        container.style.top = `${headerRect.bottom + 4}px`;
        container.style.right = `${window.innerWidth - btnRect.right}px`;
    }

    // Load items if not loaded yet
    const content = document.getElementById('config-content');
    if (content && (content.innerHTML.includes('Cargando') || content.innerHTML.trim() === '')) {
        loadSystemMenuItems();
    }

    popover.classList.add('active');

    // Close on click outside
    document.removeEventListener('click', handleConfigClickOutside);
    setTimeout(() => {
        document.addEventListener('click', handleConfigClickOutside);
    }, 10);
}

/**
 * Close configuration popover
 */
function closeConfigPopover() {
    const popover = document.getElementById('config-popover');
    if (popover) {
        popover.classList.remove('active');
    }
    document.removeEventListener('click', handleConfigClickOutside);
}

/**
 * Handle click outside configuration popover
 */
function handleConfigClickOutside(e) {
    const popover = document.getElementById('config-popover');
    const container = document.querySelector('.config-popover-container');
    const settingsBtn = document.getElementById('user-settings-btn');
    const userInfo = document.getElementById('user-info');
    
    // Si el click es en un group-header, NO cerrar el popover
    if (e.target.closest('.config-popover__group-header')) {
        console.log('[handleConfigClickOutside] Click en group-header, ignorando cierre');
        return;
    }
    
    if (container && !container.contains(e.target) && 
        settingsBtn && !settingsBtn.contains(e.target) &&
        userInfo && !userInfo.contains(e.target)) {
        closeConfigPopover();
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

    // Populate route params section
    const routeParams = window.legoWindowManager?.getRouteParams() || {};
    const routeSection = document.getElementById('params-route-section');
    if (routeSection) {
        renderRouteParams(routeSection, routeParams);
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

// Estado del toggle de route params
let routeParamsVisible = false;

/**
 * Render route params (read-only) in the route section
 */
function renderRouteParams(container, routeParams) {
    const keys = Object.keys(routeParams);

    if (keys.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    const eyeIcon = routeParamsVisible ? 'eye-outline' : 'eye-off-outline';
    const contentDisplay = routeParamsVisible ? 'block' : 'none';

    container.innerHTML = `
        <div class="params-popover__route-header">
            <span class="params-popover__route-title">
                <ion-icon name="link-outline"></ion-icon>
                Parámetros de Ruta
            </span>
            <button class="params-popover__route-toggle" onclick="toggleRouteParams()" title="Mostrar/ocultar parámetros de ruta">
                <ion-icon name="${eyeIcon}"></ion-icon>
            </button>
        </div>
        <div class="params-popover__route-content" id="params-route-content" style="display: ${contentDisplay};">
            ${keys.map(key => `
                <div class="param-item param-item--readonly">
                    <div class="param-item__header">
                        <span class="param-item__key">${escapeHtml(key)}</span>
                        <span class="param-item__readonly-badge">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                        </span>
                    </div>
                    <div class="param-item__value">${escapeHtml(String(routeParams[key]))}</div>
                </div>
            `).join('')}
        </div>
    `;
}

/**
 * Toggle route params visibility
 */
function toggleRouteParams() {
    routeParamsVisible = !routeParamsVisible;
    const content = document.getElementById('params-route-content');
    const toggle = document.querySelector('.params-popover__route-toggle ion-icon');

    if (content) {
        content.style.display = routeParamsVisible ? 'block' : 'none';
    }
    if (toggle) {
        toggle.setAttribute('name', routeParamsVisible ? 'eye-outline' : 'eye-off-outline');
    }
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
window.toggleRouteParams = toggleRouteParams;
window.openSystemMenuItem = openSystemMenuItem;
window.handleConfigGroupClick = handleConfigGroupClick;
window.openConfigPopover = openConfigPopover;
window.closeConfigPopover = closeConfigPopover;
window.toggleConfigGroup = toggleConfigGroup;