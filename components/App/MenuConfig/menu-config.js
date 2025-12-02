/**
 * MenuConfig - Lógica de configuración del menú
 *
 * FILOSOFÍA LEGO:
 * ✅ Drag & drop para reordenar
 * ✅ Modal para editar nombre e icono
 * ✅ Guardado batch de cambios
 */

console.log('[MenuConfig] Inicializando...');

// ═══════════════════════════════════════════════════════════════════
// CONFIG
// ═══════════════════════════════════════════════════════════════════

const MENU_CONFIG = {
    screenId: 'menu-config',
    apiRoute: '/api/menu-config'
};

// Cache de cambios pendientes
let pendingChanges = {};
let currentEditItem = null;
let currentNewItemIcon = 'ellipse-outline';

// ═══════════════════════════════════════════════════════════════════
// DRAG & DROP
// ═══════════════════════════════════════════════════════════════════

// Variable para rastrear si ya se agregaron los listeners de event delegation
let dragAndDropInitialized = false;

function initDragAndDrop() {
    // Usar event delegation en el contenedor principal para capturar eventos en todos los niveles
    const listContainer = document.getElementById('menu-config-list');
    if (!listContainer) {
        console.warn('[MenuConfig] No se encontró el contenedor menu-config-list');
        return;
    }
    
    // Solo agregar listeners una vez usando event delegation
    // Esto captura eventos en todos los niveles, incluso items creados dinámicamente
    if (!dragAndDropInitialized) {
        // Event delegation para dragstart y dragend en items
        listContainer.addEventListener('dragstart', function(e) {
            const item = e.target.closest('.menu-config__item');
            if (item) {
                handleDragStart.call(item, e);
            }
        }, true);
        
        listContainer.addEventListener('dragend', function(e) {
            const item = e.target.closest('.menu-config__item');
            if (item) {
                handleDragEnd.call(item, e);
            }
        }, true);
        
        // Event delegation para dragover, drop y dragleave en itemContent
        listContainer.addEventListener('dragover', function(e) {
            const itemContent = e.target.closest('.menu-config__item-content');
            if (!itemContent || !draggedItem) return;
            
            const item = itemContent.closest('.menu-config__item');
            if (!item) return;
            
            // IMPORTANTE: Solo bloquear si el target ES el padre directo del item arrastrado
            const draggedParent = draggedItem.closest('.menu-config__items')?.closest('.menu-config__item');
            if (draggedParent && draggedParent === item) {
                // El target es el padre directo, no procesar
                return;
            }
            
            // Procesar normalmente (mover a grupo)
            handleItemContentDragOver.call(itemContent, e);
        }, true);
        
        listContainer.addEventListener('drop', function(e) {
            const itemContent = e.target.closest('.menu-config__item-content');
            if (!itemContent || !draggedItem) return;
            
            const item = itemContent.closest('.menu-config__item');
            if (!item) return;
            
            // IMPORTANTE: Solo bloquear si el target ES el padre directo del item arrastrado
            const draggedParent = draggedItem.closest('.menu-config__items')?.closest('.menu-config__item');
            if (draggedParent && draggedParent === item) {
                // El target es el padre directo, no procesar
                return;
            }
            
            handleItemContentDrop.call(itemContent, e);
        }, true);
        
        listContainer.addEventListener('dragleave', function(e) {
            const itemContent = e.target.closest('.menu-config__item-content');
            if (itemContent) {
                handleItemContentDragLeave.call(itemContent, e);
            }
        }, true);
        
        dragAndDropInitialized = true;
        console.log('[MenuConfig] Event delegation inicializado para drag & drop');
    }
}

let draggedItem = null;

function handleDragStart(e) {
    draggedItem = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', this.dataset.id);
    
    // REMOVIDO: Ya no marcamos el padre como "dragging-child" porque no deshabilitamos pointer-events
    // La lógica de JavaScript ya previene mover un item dentro de su propio padre
    // Esto permite que TODOS los itemContent en TODOS los niveles puedan recibir eventos de drag-and-drop
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.menu-config__item').forEach(item => {
        item.classList.remove('drag-over');
        // REMOVIDO: Ya no usamos dragging-child
    });
    draggedItem = null;
}

// Funciones de reordenar con drag & drop ELIMINADAS
// Solo se mantiene la funcionalidad de mover items dentro de grupos/carpetas

// ═══════════════════════════════════════════════════════════════════
// DRAG & DROP - ZONA DE DROP DENTRO DE ITEM (CREAR GRUPO)
// ═══════════════════════════════════════════════════════════════════

function handleItemContentDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const targetItem = this.closest('.menu-config__item');
    if (!targetItem || targetItem === draggedItem) {
        e.dataTransfer.dropEffect = 'none';
        this.classList.remove('menu-config__drop-zone');
        return;
    }
    
    // Validar que no se mueva un item dentro de sí mismo o dentro de sus hijos
    const targetId = targetItem.dataset.id;
    const sourceId = draggedItem.dataset.id;
    const targetLevel = parseInt(targetItem.dataset.level) || 0;
    const sourceLevel = parseInt(draggedItem.dataset.level) || 0;
    
    // Si el target es el mismo que el source, bloquear
    if (targetId === sourceId) {
        e.dataTransfer.dropEffect = 'none';
        this.classList.remove('menu-config__drop-zone');
        return;
    }
    
    // IMPORTANTE: Solo bloquear si el target ES el padre directo del item arrastrado
    // Esto permite mover items a otros grupos (incluso carpetas dentro de carpetas), pero evita moverlos dentro de su propio padre
    const draggedParent = draggedItem.closest('.menu-config__items')?.closest('.menu-config__item');
    if (draggedParent && draggedParent === targetItem) {
        // El target es el padre directo, bloquear (no tiene sentido mover dentro de su propio padre)
        e.dataTransfer.dropEffect = 'none';
        this.classList.remove('menu-config__drop-zone');
        return;
    }
    
    // Verificar si el target es un hijo del item arrastrado (evita ciclos - mover dentro de sus propios hijos)
    // IMPORTANTE: Esto es diferente a isDescendantOf - aquí verificamos si el target es hijo del source
    if (isDescendantOf(targetItem, sourceId)) {
        e.dataTransfer.dropEffect = 'none';
        this.classList.remove('menu-config__drop-zone');
        return;
    }
    
    // IMPORTANTE: Permitir mover desde cualquier nivel a cualquier otro nivel más profundo
    // Esto incluye mover de nivel 0 a nivel 1, de nivel 1 a nivel 2, de nivel 2 a nivel 3, etc.
    // Solo bloqueamos si es el padre directo o si crearía un ciclo
    
    // Permitir drop en cualquier otro caso (hermanos, otros grupos, niveles más profundos, etc.)
    console.log('[MenuConfig] DragOver permitido:', {
        source: sourceId,
        sourceLevel: sourceLevel,
        target: targetId,
        targetLevel: targetLevel,
        newLevel: targetLevel + 1
    });
    this.classList.add('menu-config__drop-zone');
    e.dataTransfer.dropEffect = 'move';
}

function handleItemContentDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('menu-config__drop-zone');
}

function handleItemContentDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove('menu-config__drop-zone');
    
    const targetItem = this.closest('.menu-config__item');
    if (!targetItem || targetItem === draggedItem) return;
    
    const targetId = targetItem.dataset.id;
    const sourceId = draggedItem.dataset.id;
    
    // Validar que no se mueva un item dentro de sí mismo o dentro de sus hijos
    // Si el target es el mismo que el source, bloquear
    if (targetId === sourceId) {
        console.warn('[MenuConfig] No se puede mover un item dentro de sí mismo');
        if (window.AlertService) {
            window.AlertService.warning('Operación inválida', 'No se puede mover un item dentro de sí mismo.');
        }
        return;
    }
    
    // IMPORTANTE: Solo bloquear si el target ES el padre directo del item arrastrado
    // Esto permite mover items a otros grupos (incluso carpetas dentro de carpetas), pero evita moverlos dentro de su propio padre
    const draggedParent = draggedItem.closest('.menu-config__items')?.closest('.menu-config__item');
    if (draggedParent && draggedParent === targetItem) {
        // El target es el padre directo, bloquear (no tiene sentido mover dentro de su propio padre)
        console.log('[MenuConfig] No se puede mover un item dentro de su propio padre');
        return;
    }
    
    // Verificar si el target es un hijo del item arrastrado (evita ciclos - mover dentro de sus propios hijos)
    // IMPORTANTE: Esto es diferente a isDescendantOf - aquí verificamos si el target es hijo del source
    if (isDescendantOf(targetItem, sourceId)) {
        console.warn('[MenuConfig] No se puede mover un item dentro de sus propios hijos');
        if (window.AlertService) {
            window.AlertService.warning('Operación inválida', 'No se puede mover un item dentro de sus propios hijos.');
        }
        return;
    }
    
    const targetLevel = parseInt(targetItem.dataset.level) || 0;
    const newLevel = targetLevel + 1;
    
    // Verificar si el item objetivo ya tiene hijos
    let targetList = targetItem.querySelector('.menu-config__items');
    
    if (!targetList) {
        // El item objetivo no tiene hijos, crear estructura de grupo
        const itemContent = targetItem.querySelector('.menu-config__item-content');
        const childrenList = document.createElement('ul');
        childrenList.className = 'menu-config__items';
        childrenList.setAttribute('data-level', newLevel);
        
        // Insertar después del contenido del item
        if (itemContent && itemContent.nextSibling) {
            targetItem.insertBefore(childrenList, itemContent.nextSibling);
        } else {
            targetItem.appendChild(childrenList);
        }
        
        targetList = childrenList;
        
        // Convertir el item objetivo en grupo (marcar para que el backend lo maneje)
        if (!pendingChanges[targetId]) {
            pendingChanges[targetId] = {};
        }
        console.log('[MenuConfig] Item convertido en grupo:', targetId);
    }
    
    // Mover el item arrastrado dentro del grupo
    targetList.appendChild(draggedItem);
    
    // Actualizar parent_id y level del item movido
    updateItemParentAndLevel(sourceId, targetId, newLevel);
    
    // Recalcular orden dentro del nuevo nivel
    recalculateOrder(targetList);
    
    console.log('[MenuConfig] Item movido dentro de grupo:', sourceId, '→ dentro de', targetId, '(nivel', newLevel + ')');
}

/**
 * Verificar si el target contiene al item arrastrado como hijo directo o indirecto
 * Retorna true si el target es un ancestro del item arrastrado
 * IMPORTANTE: Esto previene mover un item dentro de sus propios hijos (evita ciclos)
 * PERO permite mover items a otros grupos, incluso si el target es un ancestro lejano
 */
function isDescendantOf(item, ancestorId) {
    if (!item || !ancestorId) return false;
    
    // Si el item mismo es el ancestor, no es descendiente (pero no se puede mover dentro de sí mismo)
    if (item.dataset && item.dataset.id === ancestorId) {
        return false; // Un item no puede moverse dentro de sí mismo
    }
    
    // Buscar en todos los ancestros del item para ver si el target es uno de ellos
    // Esto previene mover un item dentro de sus propios hijos (evita crear ciclos)
    let current = item;
    while (current) {
        // Buscar el padre en la jerarquía
        const parentList = current.closest('.menu-config__items');
        if (!parentList) break;
        
        const parentItem = parentList.closest('.menu-config__item');
        if (!parentItem) break;
        
        // Si encontramos el ancestor en la jerarquía, el item es descendiente
        // Esto previene mover un item dentro de sus propios hijos
        if (parentItem.dataset && parentItem.dataset.id === ancestorId) {
            return true;
        }
        
        current = parentItem;
    }
    
    return false;
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZACIÓN DE PARENT_ID Y LEVEL
// ═══════════════════════════════════════════════════════════════════

function updateItemParentAndLevel(itemId, newParentId, newLevel) {
    const item = document.querySelector(`.menu-config__item[data-id="${itemId}"]`);
    if (!item) return;
    
    // Normalizar parent_id: convertir string vacío a null
    const oldParentId = item.dataset.parent === '' ? null : (item.dataset.parent || null);
    const oldLevel = parseInt(item.dataset.level) || 0;
    
    // Normalizar newParentId: convertir string vacío a null
    const normalizedNewParentId = (newParentId === '' || newParentId === null) ? null : newParentId;
    
    // Asegurar que el nivel no sea negativo
    const normalizedNewLevel = Math.max(0, newLevel);
    
    // Actualizar atributos del DOM
    item.dataset.parent = normalizedNewParentId || '';
    item.dataset.level = normalizedNewLevel;
    
    // Actualizar cambios pendientes
    if (!pendingChanges[itemId]) {
        pendingChanges[itemId] = {};
    }
    
    // Comparar parent_id normalizado
    if (oldParentId !== normalizedNewParentId) {
        // Guardar null explícitamente, no string vacío
        pendingChanges[itemId].parent_id = normalizedNewParentId;
        console.log('[MenuConfig] Cambio de parent:', itemId, oldParentId, '→', normalizedNewParentId);
    }
    
    if (oldLevel !== normalizedNewLevel) {
        pendingChanges[itemId].level = normalizedNewLevel;
        console.log('[MenuConfig] Cambio de level:', itemId, oldLevel, '→', normalizedNewLevel);
        
        // Actualizar recursivamente los niveles de los hijos
        updateChildrenLevels(item, normalizedNewLevel);
    }
    
    // Si el parent_id cambió a null, asegurarse de que el level también sea 0
    if (normalizedNewParentId === null && normalizedNewLevel !== 0) {
        pendingChanges[itemId].level = 0;
        item.dataset.level = 0;
        console.log('[MenuConfig] Ajustando level a 0 para item sin padre:', itemId);
    }
    
    updateSaveButtonState();
}

function updateChildrenLevels(parentItem, parentLevel) {
    const childrenList = parentItem.querySelector('.menu-config__items');
    if (!childrenList) return;
    
    const children = childrenList.querySelectorAll(':scope > .menu-config__item');
    const childLevel = parentLevel + 1;
    
    children.forEach(child => {
        const childId = child.dataset.id;
        child.dataset.level = childLevel;
        
        if (!pendingChanges[childId]) {
            pendingChanges[childId] = {};
        }
        pendingChanges[childId].level = childLevel;
        
        // Recursivo para nietos
        updateChildrenLevels(child, childLevel);
    });
}

function recalculateOrder(list) {
    const items = list.querySelectorAll(':scope > .menu-config__item');
    
    items.forEach((item, index) => {
        const id = item.dataset.id;
        const oldOrder = parseInt(item.dataset.order) || 0;
        
        if (oldOrder !== index) {
            item.dataset.order = index;
            
            if (!pendingChanges[id]) {
                pendingChanges[id] = {};
            }
            pendingChanges[id].display_order = index;
            
            console.log('[MenuConfig] Cambio de orden:', id, oldOrder, '→', index);
        }
    });
    
    updateSaveButtonState();
}

// ═══════════════════════════════════════════════════════════════════
// MODAL DE EDICIÓN
// ═══════════════════════════════════════════════════════════════════

window.openEditModal = function(id, label, icon, route) {
    currentEditItem = { id, label, icon, route: route || '' };
    
    document.getElementById('edit-item-id').value = id;
    document.getElementById('edit-label').value = label;
    document.getElementById('edit-route').value = route || '';
    document.getElementById('current-icon').setAttribute('name', icon);
    document.getElementById('current-icon-name').textContent = icon;
    
    // Renderizar grid de iconos
    renderIconGrid(icon);
    
    const modal = document.getElementById('menu-edit-modal');
    // Mover modal al body si no está ya ahí
    if (modal.parentElement !== document.body) {
        modal.dataset.originalParent = modal.parentElement.id || '.' + modal.parentElement.className.split(' ')[0];
        document.body.appendChild(modal);
    }
    modal.style.display = 'flex';
    modal.style.zIndex = '9999999';
};

window.closeEditModal = function() {
    const modal = document.getElementById('menu-edit-modal');
    modal.style.display = 'none';
    // Restaurar modal a su posición original si fue movido
    if (modal.dataset.originalParent) {
        const originalParent = document.querySelector(modal.dataset.originalParent);
        if (originalParent) {
            originalParent.appendChild(modal);
        }
        delete modal.dataset.originalParent;
    }
    currentEditItem = null;
};

function renderIconGrid(selectedIcon) {
    const grid = document.getElementById('icon-grid');
    const icons = window.MENU_CONFIG_ICONS || [];
    
    grid.innerHTML = icons.map(icon => `
        <button 
            type="button" 
            class="menu-config__icon-option ${icon === selectedIcon ? 'selected' : ''}"
            data-icon="${icon}"
            onclick="selectIcon('${icon}')"
            title="${icon}"
        >
            <ion-icon name="${icon}"></ion-icon>
        </button>
    `).join('');
}

window.selectIcon = function(icon) {
    // Actualizar preview
    document.getElementById('current-icon').setAttribute('name', icon);
    document.getElementById('current-icon-name').textContent = icon;
    
    // Actualizar selección visual
    document.querySelectorAll('.menu-config__icon-option').forEach(btn => {
        btn.classList.toggle('selected', btn.dataset.icon === icon);
    });
    
    currentEditItem.icon = icon;
};

window.applyEditChanges = function() {
    if (!currentEditItem) return;
    
    const id = currentEditItem.id;
    const newLabel = document.getElementById('edit-label').value.trim();
    const newRoute = document.getElementById('edit-route').value.trim();
    const newIcon = currentEditItem.icon;
    
    // Actualizar en DOM
    const item = document.querySelector(`.menu-config__item[data-id="${id}"]`);
    if (item) {
        item.querySelector('.menu-config__item-label').textContent = newLabel;
        item.querySelector('.menu-config__item-icon ion-icon').setAttribute('name', newIcon);
    }
    
    // Registrar cambios
    if (!pendingChanges[id]) {
        pendingChanges[id] = {};
    }
    pendingChanges[id].label = newLabel;
    pendingChanges[id].route = newRoute || null; // null si está vacío (grupo)
    pendingChanges[id].icon = newIcon;
    
    console.log('[MenuConfig] Cambios aplicados:', id, { label: newLabel, route: newRoute, icon: newIcon });
    
    updateSaveButtonState();
    closeEditModal();
};

// ═══════════════════════════════════════════════════════════════════
// MODAL DE NUEVO ITEM
// ═══════════════════════════════════════════════════════════════════

window.openNewItemModal = function() {
    currentNewItemIcon = 'ellipse-outline';
    
    document.getElementById('new-label').value = '';
    document.getElementById('new-route').value = '';
    document.getElementById('new-current-icon').setAttribute('name', currentNewItemIcon);
    document.getElementById('new-current-icon-name').textContent = currentNewItemIcon;
    
    // Renderizar grid de iconos
    renderNewIconGrid(currentNewItemIcon);
    
    const modal = document.getElementById('menu-new-modal');
    // Mover modal al body si no está ya ahí
    if (modal.parentElement !== document.body) {
        modal.dataset.originalParent = modal.parentElement.id || '.' + modal.parentElement.className.split(' ')[0];
        document.body.appendChild(modal);
    }
    modal.style.display = 'flex';
    modal.style.zIndex = '9999999';
};

window.closeNewItemModal = function() {
    const modal = document.getElementById('menu-new-modal');
    modal.style.display = 'none';
    // Restaurar modal a su posición original si fue movido
    if (modal.dataset.originalParent) {
        const originalParent = document.querySelector(modal.dataset.originalParent);
        if (originalParent) {
            originalParent.appendChild(modal);
        }
        delete modal.dataset.originalParent;
    }
    document.getElementById('menu-new-form').reset();
};

function renderNewIconGrid(selectedIcon) {
    const grid = document.getElementById('new-icon-grid');
    const icons = window.MENU_CONFIG_ICONS || [];
    
    grid.innerHTML = icons.map(icon => `
        <button 
            type="button" 
            class="menu-config__icon-option ${icon === selectedIcon ? 'selected' : ''}"
            data-icon="${icon}"
            onclick="selectNewIcon('${icon}')"
            title="${icon}"
        >
            <ion-icon name="${icon}"></ion-icon>
        </button>
    `).join('');
}

window.selectNewIcon = function(icon) {
    currentNewItemIcon = icon;
    
    // Actualizar preview
    document.getElementById('new-current-icon').setAttribute('name', icon);
    document.getElementById('new-current-icon-name').textContent = icon;
    
    // Actualizar selección visual
    document.querySelectorAll('#new-icon-grid .menu-config__icon-option').forEach(btn => {
        btn.classList.toggle('selected', btn.dataset.icon === icon);
    });
};

window.createNewItem = async function() {
    const label = document.getElementById('new-label').value.trim();
    const route = document.getElementById('new-route').value.trim();
    const icon = currentNewItemIcon;
    
    if (!label) {
        if (window.AlertService) {
            window.AlertService.warning('Campo requerido', 'Por favor completa el nombre.');
        }
        return;
    }
    
    // La ruta es opcional - si está vacía, será un grupo
    try {
        const response = await fetch(MENU_CONFIG.apiRoute + '/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                label: label,
                route: route || null, // null si está vacío (será un grupo)
                icon: icon
            })
        });
        
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al crear item');
        }
        
        if (window.AlertService) {
            window.AlertService.success('Item creado', 'El nuevo item se ha creado correctamente');
        }
        
        closeNewItemModal();
        
        // Recargar la vista
        reloadMenuConfigView();
        
    } catch (error) {
        console.error('[MenuConfig] Error creando item:', error);
        
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al crear el item');
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// GUARDAR
// ═══════════════════════════════════════════════════════════════════

function updateSaveButtonState() {
    const btn = document.getElementById('menu-config-save-btn');
    const hasChanges = Object.keys(pendingChanges).length > 0;
    
    if (hasChanges) {
        btn.style.background = '#22c55e'; // Verde para indicar cambios
        btn.innerHTML = '<ion-icon name="save-outline"></ion-icon> Guardar (' + Object.keys(pendingChanges).length + ')';
    } else {
        btn.style.background = '';
        btn.innerHTML = '<ion-icon name="save-outline"></ion-icon> Guardar';
    }
}

window.saveMenuConfig = async function() {
    if (Object.keys(pendingChanges).length === 0) {
        if (window.AlertService) {
            window.AlertService.info('Sin cambios', 'No hay cambios pendientes para guardar.');
        }
        return;
    }
    
    const btn = document.getElementById('menu-config-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<ion-icon name="sync-outline"></ion-icon> Guardando...';
    
    try {
        // Normalizar los cambios: convertir parent_id vacío a null
        const normalizedChanges = {};
        for (const [itemId, changes] of Object.entries(pendingChanges)) {
            normalizedChanges[itemId] = { ...changes };
            if ('parent_id' in normalizedChanges[itemId]) {
                if (normalizedChanges[itemId].parent_id === '' || normalizedChanges[itemId].parent_id === 'null' || normalizedChanges[itemId].parent_id === null) {
                    normalizedChanges[itemId].parent_id = null;
                }
            }
            // Asegurar que si parent_id es null, level sea 0
            if (normalizedChanges[itemId].parent_id === null && (!('level' in normalizedChanges[itemId]) || normalizedChanges[itemId].level !== 0)) {
                normalizedChanges[itemId].level = 0;
            }
        }
        
        console.log('[MenuConfig] Guardando cambios:', JSON.stringify(normalizedChanges, null, 2));
        
        const response = await fetch(MENU_CONFIG.apiRoute + '/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items: normalizedChanges })
        });
        
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al guardar');
        }
        
        if (window.AlertService) {
            window.AlertService.success('Guardado', 'Configuración del menú actualizada correctamente');
        }
        
        // Limpiar cambios pendientes
        pendingChanges = {};
        updateSaveButtonState();
        
        // Recargar la vista de configuración para mostrar la nueva estructura
        reloadMenuConfigView();
        
        // Emitir evento para recargar el menú lateral
        if (window.lego && window.lego.events) {
            window.lego.events.emit('menu:updated', {});
        }
        
        // También emitir evento nativo del navegador
        window.dispatchEvent(new CustomEvent('lego:menu:updated', {}));
        
    } catch (error) {
        console.error('[MenuConfig] Error guardando:', error);
        
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al guardar configuración');
        }
    } finally {
        btn.disabled = false;
        updateSaveButtonState();
    }
};

// ═══════════════════════════════════════════════════════════════════
// RECARGAR VISTA DE CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════════

async function reloadMenuConfigView() {
    try {
        // Obtener nueva estructura del menú
        const response = await fetch(MENU_CONFIG.apiRoute + '/list');
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al obtener items del menú');
        }
        
        const items = result.data || [];
        
        // Reconstruir árbol desde lista plana
        const menuTree = buildMenuTreeFromFlatList(items);
        const menuHtml = renderMenuTreeToHtml(menuTree);
        
        // Actualizar DOM
        const listContainer = document.getElementById('menu-config-list');
        if (listContainer) {
            listContainer.innerHTML = menuHtml;
            
            // Reinicializar drag & drop (event delegation ya está configurado, solo asegurar que el contenedor existe)
            // No necesitamos re-agregar listeners porque usamos event delegation
            if (!dragAndDropInitialized) {
                initDragAndDrop();
            }
            
            console.log('[MenuConfig] Vista recargada');
        }
    } catch (error) {
        console.error('[MenuConfig] Error al recargar vista:', error);
    }
}

function buildMenuTreeFromFlatList(items) {
    const tree = [];
    const itemsById = {};
    
    // Indexar por ID
    items.forEach(item => {
        itemsById[item.id] = {
            ...item,
            children: []
        };
    });
    
    // Construir árbol
    Object.values(itemsById).forEach(item => {
        if (item.parent_id === null) {
            tree.push(item);
        } else if (itemsById[item.parent_id]) {
            itemsById[item.parent_id].children.push(item);
        }
    });
    
    // Ordenar por display_order
    const sortTree = (items) => {
        items.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        items.forEach(item => {
            if (item.children.length > 0) {
                sortTree(item.children);
            }
        });
    };
    
    sortTree(tree);
    return tree;
}

function renderMenuTreeToHtml(items, level = 0) {
    if (!items || items.length === 0) return '';
    
    let html = `<ul class="menu-config__items" data-level="${level}">`;
    
    // Contar items en este nivel para ocultar botones arriba/abajo si solo hay uno
    const itemsCount = items.length;
    
    items.forEach((item, index) => {
        const icon = escapeHtml(item.icon || 'ellipse-outline');
        const label = escapeHtml(item.label);
        const id = escapeHtml(item.id);
        const parentId = item.parent_id || '';
        const isVisible = item.is_visible ? 'visible' : 'hidden';
        const isDynamic = item.is_dynamic ? 'dynamic' : 'static';
        const hasChildren = item.children && Array.isArray(item.children) && item.children.length > 0;
        
        let badges = '';
        if (!item.is_visible) {
            badges += '<span class="menu-config__badge menu-config__badge--hidden" title="Oculto">Oculto</span>';
        }
        if (item.is_dynamic) {
            badges += '<span class="menu-config__badge menu-config__badge--dynamic" title="Dinámico">Dinámico</span>';
        }
        // Indicador de grupo: si tiene hijos o no tiene ruta
        const isGroup = hasChildren || !item.route || item.route === null;
        if (isGroup) {
            badges += '<span class="menu-config__badge menu-config__badge--group" title="Grupo">Grupo</span>';
        }
        
        // Botón de izquierda solo si tiene parent_id Y está en nivel > 0
        const itemLevel = parseInt(item.level || 0);
        const leftButton = (parentId && itemLevel > 0) ? `<button class="menu-config__arrow-btn" onclick="moveItemLeft('${id}')" title="Subir de nivel (más superficial)">
                                <ion-icon name="arrow-back-outline"></ion-icon>
                            </button>` : '';
        
        // Botones arriba/abajo solo si hay más de un item en este nivel
        const upButton = itemsCount > 1 ? `<button class="menu-config__arrow-btn" onclick="moveItemUp('${id}')" title="Mover arriba (mismo nivel)">
                                <ion-icon name="arrow-up-outline"></ion-icon>
                            </button>` : '';
        const downButton = itemsCount > 1 ? `<button class="menu-config__arrow-btn" onclick="moveItemDown('${id}')" title="Mover abajo (mismo nivel)">
                                <ion-icon name="arrow-down-outline"></ion-icon>
                            </button>` : '';
        
        html += `
            <li class="menu-config__item" 
                data-id="${id}" 
                data-parent="${parentId}"
                data-order="${item.display_order || 0}"
                data-level="${item.level || 0}"
                data-visible="${isVisible}"
                data-dynamic="${isDynamic}"
                draggable="true"
            >
                <div class="menu-config__item-content">
                    <span class="menu-config__drag-handle">
                        <ion-icon name="reorder-three-outline"></ion-icon>
                    </span>
                    <span class="menu-config__item-icon">
                        <ion-icon name="${icon}"></ion-icon>
                    </span>
                    <span class="menu-config__item-label">${label}</span>
                    ${badges}
                    <div class="menu-config__item-actions">
                        <div class="menu-config__arrow-controls">
                            ${upButton}
                            ${downButton}
                            ${leftButton}
                        </div>
                        <button class="menu-config__edit-btn" onclick="openEditModal('${id}', '${escapeHtml(label)}', '${icon}', '${escapeHtml(item.route || '')}')">
                            <ion-icon name="create-outline"></ion-icon>
                        </button>
                    </div>
                </div>
        `;
        
        if (hasChildren) {
            html += renderMenuTreeToHtml(item.children, level + 1);
        }
        
        html += '</li>';
    });
    
    html += '</ul>';
    return html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ═══════════════════════════════════════════════════════════════════
// CONTROLES DE REORDENAMIENTO Y NIVELES (4 FLECHAS)
// ═══════════════════════════════════════════════════════════════════

/**
 * ↑ (Arriba): Reordenar hacia arriba en el mismo nivel
 */
window.moveItemUp = function(itemId) {
    const item = document.querySelector(`.menu-config__item[data-id="${itemId}"]`);
    if (!item) return;
    
    const list = item.parentElement;
    if (!list || !list.classList.contains('menu-config__items')) return;
    
    const items = Array.from(list.querySelectorAll(':scope > .menu-config__item'));
    const currentIndex = items.indexOf(item);
    
    if (currentIndex <= 0) {
        // Ya está en la primera posición
        return;
    }
    
    // Intercambiar con el item anterior
    const previousItem = items[currentIndex - 1];
    list.insertBefore(item, previousItem);
    
    // Recalcular orden
    recalculateOrder(list);
    
    console.log('[MenuConfig] Item movido hacia arriba:', itemId);
};

/**
 * ↓ (Abajo): Reordenar hacia abajo en el mismo nivel
 */
window.moveItemDown = function(itemId) {
    const item = document.querySelector(`.menu-config__item[data-id="${itemId}"]`);
    if (!item) return;
    
    const list = item.parentElement;
    if (!list || !list.classList.contains('menu-config__items')) return;
    
    const items = Array.from(list.querySelectorAll(':scope > .menu-config__item'));
    const currentIndex = items.indexOf(item);
    
    if (currentIndex >= items.length - 1) {
        // Ya está en la última posición
        return;
    }
    
    // Intercambiar con el item siguiente
    const nextItem = items[currentIndex + 1];
    if (nextItem.nextSibling) {
        list.insertBefore(item, nextItem.nextSibling);
    } else {
        list.appendChild(item);
    }
    
    // Recalcular orden
    recalculateOrder(list);
    
    console.log('[MenuConfig] Item movido hacia abajo:', itemId);
};

// Botón de derecha eliminado - se usa drag & drop para mover hacia adentro

/**
 * ← (Izquierda): Subir de nivel (nivel más superficial, número menor)
 */
window.moveItemLeft = function(itemId) {
    const item = document.querySelector(`.menu-config__item[data-id="${itemId}"]`);
    if (!item) {
        console.error('[MenuConfig] Item no encontrado:', itemId);
        return;
    }
    
    // Normalizar parent_id del item actual
    const currentParentId = item.dataset.parent === '' ? null : (item.dataset.parent || null);
    const currentLevel = parseInt(item.dataset.level) || 0;
    
    if (!currentParentId) {
        // Ya está en el nivel raíz
        if (window.AlertService) {
            window.AlertService.info('Ya está en el nivel raíz', 'Este item ya está en el nivel superior.');
        }
        return;
    }
    
    // Encontrar el padre
    const parentItem = document.querySelector(`.menu-config__item[data-id="${currentParentId}"]`);
    if (!parentItem) {
        console.error('[MenuConfig] Padre no encontrado:', currentParentId);
        return;
    }
    
    // Encontrar la lista que contiene al padre
    const parentList = parentItem.closest('.menu-config__items');
    if (!parentList) {
        console.error('[MenuConfig] Lista del padre no encontrada');
        return;
    }
    
    // Calcular el nuevo parent_id (el padre del padre actual)
    const newParentId = parentItem.dataset.parent === '' ? null : (parentItem.dataset.parent || null);
    
    // Calcular el nuevo nivel
    let newLevel = 0;
    if (newParentId) {
        // Si tiene nuevo padre, calcular nivel desde ese padre
        const newParentItem = document.querySelector(`.menu-config__item[data-id="${newParentId}"]`);
        if (newParentItem) {
            const newParentLevel = parseInt(newParentItem.dataset.level) || 0;
            newLevel = newParentLevel + 1;
        } else {
            console.warn('[MenuConfig] Nuevo padre no encontrado, usando nivel 0');
            newLevel = 0;
        }
    } else {
        // Si no tiene padre, está en nivel raíz (0)
        newLevel = 0;
    }
    
    console.log('[MenuConfig] Moviendo item:', {
        itemId,
        currentParentId,
        newParentId,
        currentLevel,
        newLevel
    });
    
    // Mover el item al nivel del padre (en la misma lista donde está el padre)
    // Insertar después del padre
    const nextSibling = parentItem.nextSibling;
    if (nextSibling) {
        parentList.insertBefore(item, nextSibling);
    } else {
        parentList.appendChild(item);
    }
    
    // Actualizar parent_id y level
    updateItemParentAndLevel(itemId, newParentId, newLevel);
    
    // Recalcular orden en el nuevo nivel (la lista donde está ahora)
    const newList = item.closest('.menu-config__items');
    if (newList) {
        recalculateOrder(newList);
    }
    
    console.log('[MenuConfig] Item movido a nivel más superficial:', itemId, '→ nivel', newLevel, 'parent:', newParentId);
};

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

function initialize() {
    console.log('[MenuConfig] Inicializando drag & drop...');
    initDragAndDrop();
    updateSaveButtonState();
    console.log('[MenuConfig] Listo');
}

// ═══════════════════════════════════════════════════════════════════
// EXPORTAR E IMPORTAR ESTRUCTURA DEL MENÚ
// ═══════════════════════════════════════════════════════════════════

/**
 * Exportar estructura del menú a archivo JSON
 */
window.exportMenuStructure = async function exportMenuStructure() {
    try {
        const response = await fetch(MENU_CONFIG.apiRoute + '/export');
        
        if (!response.ok) {
            const result = await response.json();
            throw new Error(result.msj || 'Error al exportar menú');
        }
        
        // Obtener el blob del archivo
        const blob = await response.blob();
        
        // Crear URL temporal y descargar
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `menu-structure-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        if (window.AlertService) {
            window.AlertService.success('Exportado', 'Estructura del menú exportada correctamente');
        }
    } catch (error) {
        console.error('[MenuConfig] Error exportando:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al exportar estructura del menú');
        }
    }
}

/**
 * Abrir modal de importación
 */
window.openImportModal = function openImportModal() {
    const importModal = document.getElementById('menu-import-modal');
    if (importModal) {
        // Mover modal al body si no está ya ahí
        if (importModal.parentElement !== document.body) {
            importModal.dataset.originalParent = importModal.parentElement.id || '.' + importModal.parentElement.className.split(' ')[0];
            document.body.appendChild(importModal);
        }
        importModal.style.display = 'flex';
        importModal.style.zIndex = '9999999';
        // Resetear formulario
        document.getElementById('import-file').value = '';
        document.getElementById('import-mode').value = 'replace';
        document.getElementById('import-preview').style.display = 'none';
        document.getElementById('import-submit-btn').disabled = true;
    }
}

/**
 * Cerrar modal de importación
 */
window.closeImportModal = function closeImportModal() {
    const importModal = document.getElementById('menu-import-modal');
    if (importModal) {
        importModal.style.display = 'none';
        // Restaurar modal a su posición original si fue movido
        if (importModal.dataset.originalParent) {
            const originalParent = document.querySelector(importModal.dataset.originalParent);
            if (originalParent) {
                originalParent.appendChild(importModal);
            }
            delete importModal.dataset.originalParent;
        }
    }
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Manejar selección de archivo para importación
 */
window.handleImportFileSelect = function handleImportFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const jsonData = JSON.parse(e.target.result);
            
            // Validar formato
            if (!jsonData.items || !Array.isArray(jsonData.items)) {
                throw new Error('Formato inválido: el archivo debe contener un objeto con campo "items" (array)');
            }
            
            // Mostrar vista previa
            const preview = document.getElementById('import-preview');
            const previewContent = document.getElementById('import-preview-content');
            preview.style.display = 'block';
            
            previewContent.innerHTML = `
                <strong>Total de items:</strong> ${jsonData.items.length}<br>
                <strong>Versión:</strong> ${jsonData.version || 'N/A'}<br>
                <strong>Exportado:</strong> ${jsonData.exported_at || 'N/A'}<br>
                <br>
                <strong>Primeros items:</strong><br>
                ${jsonData.items.slice(0, 5).map(item => `• ${item.label} (${item.id})`).join('<br>')}
                ${jsonData.items.length > 5 ? `<br>... y ${jsonData.items.length - 5} más` : ''}
            `;
            
            // Habilitar botón de importar
            document.getElementById('import-submit-btn').disabled = false;
            
            // Guardar datos en variable global temporal
            window._importData = jsonData;
        } catch (error) {
            console.error('[MenuConfig] Error leyendo archivo:', error);
            if (window.AlertService) {
                window.AlertService.error('Error', 'Error al leer archivo: ' + error.message);
            }
            document.getElementById('import-preview').style.display = 'none';
            document.getElementById('import-submit-btn').disabled = true;
        }
    };
    
    reader.readAsText(file);
}

/**
 * Importar estructura del menú
 */
window.importMenuStructure = async function importMenuStructure() {
    if (!window._importData) {
        if (window.AlertService) {
            window.AlertService.warning('Error', 'No hay datos para importar');
        }
        return;
    }
    
    const mode = document.getElementById('import-mode').value;
    const submitBtn = document.getElementById('import-submit-btn');
    
    // Confirmar si es modo replace
    if (mode === 'replace') {
        const confirmed = await window.ConfirmationService?.warning(
            '¿Reemplazar todo?',
            'Esta acción eliminará TODOS los items del menú actual y los reemplazará con los importados. ¿Estás seguro?'
        );
        
        if (!confirmed) {
            return;
        }
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<ion-icon name="sync-outline"></ion-icon> Importando...';
    
    try {
        const response = await fetch(MENU_CONFIG.apiRoute + '/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                data: window._importData,
                mode: mode
            })
        });
        
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al importar menú');
        }
        
        if (window.AlertService) {
            const message = `Importación completada: ${result.data.imported || 0} nuevos, ${result.data.updated || 0} actualizados`;
            window.AlertService.success('Importado', message);
        }
        
        // Cerrar modal
        closeImportModal();
        
        // Recargar vista
        reloadMenuConfigView();
        
        // Emitir evento para recargar el menú lateral
        if (window.lego && window.lego.events) {
            window.lego.events.emit('menu:updated', {});
        }
        
    } catch (error) {
        console.error('[MenuConfig] Error importando:', error);
        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al importar estructura del menú');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<ion-icon name="cloud-upload-outline"></ion-icon> Importar';
        window._importData = null;
    }
}

// ═══════════════════════════════════════════════════════════════════
// MENÚ MÓVIL
// ═══════════════════════════════════════════════════════════════════

/**
 * Abrir/cerrar menú móvil
 */
window.toggleMobileMenu = function toggleMobileMenu(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const dropdown = document.getElementById('menu-config-mobile-menu-dropdown');
    const button = document.getElementById('menu-config-mobile-menu-btn');
    
    if (!dropdown || !button) {
        console.warn('[MenuConfig] No se encontró el menú móvil o el botón');
        return;
    }
    
    const isShowing = dropdown.classList.contains('show');
    
    if (isShowing) {
        // Cerrar menú
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
        dropdown.style.visibility = 'hidden';
        dropdown.style.opacity = '0';
        dropdown.style.pointerEvents = 'none';
        
        // Restaurar dropdown a su posición original si fue movido
        if (dropdown.dataset.originalParent) {
            const originalParent = document.querySelector(dropdown.dataset.originalParent);
            if (originalParent) {
                originalParent.appendChild(dropdown);
            }
            delete dropdown.dataset.originalParent;
        }
    } else {
        // Cerrar otros menús abiertos
        document.querySelectorAll('.menu-config__mobile-menu-dropdown.show, .menu-config__item-mobile-menu-dropdown.show').forEach(menu => {
            menu.classList.remove('show');
            menu.style.display = 'none';
            menu.style.visibility = 'hidden';
            menu.style.opacity = '0';
            menu.style.pointerEvents = 'none';
            
            // Restaurar otros menús si fueron movidos
            if (menu.dataset.originalParent) {
                const originalParent = document.querySelector(menu.dataset.originalParent);
                if (originalParent) {
                    originalParent.appendChild(menu);
                }
                delete menu.dataset.originalParent;
            }
        });
        
        // Mover dropdown al body si no está ya ahí
        if (dropdown.parentElement !== document.body) {
            dropdown.dataset.originalParent = dropdown.parentElement.id || '.' + dropdown.parentElement.className.split(' ')[0];
            document.body.appendChild(dropdown);
        }
        
        // Calcular posición
        const rect = button.getBoundingClientRect();
        const dropdownWidth = dropdown.offsetWidth || 180;
        const dropdownHeight = dropdown.offsetHeight || 200;
        
        let left = rect.right - dropdownWidth;
        let top = rect.bottom + 4;
        
        // Ajustar si se sale de la pantalla
        if (left < 8) {
            left = 8;
        }
        if (left + dropdownWidth > window.innerWidth - 8) {
            left = window.innerWidth - dropdownWidth - 8;
        }
        if (top + dropdownHeight > window.innerHeight) {
            top = Math.max(8, rect.top - dropdownHeight);
        }
        if (top < 8) {
            top = 8;
        }
        
        // Aplicar estilos
        dropdown.style.position = 'fixed';
        dropdown.style.top = top + 'px';
        dropdown.style.left = left + 'px';
        dropdown.style.right = 'auto';
        dropdown.style.bottom = 'auto';
        dropdown.style.zIndex = '999999';
        dropdown.style.display = 'block';
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.style.pointerEvents = 'auto';
        
        dropdown.classList.add('show');
    }
};

/**
 * Cerrar menú móvil
 */
window.closeMobileMenu = function closeMobileMenu() {
    const dropdown = document.getElementById('menu-config-mobile-menu-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
        dropdown.style.visibility = 'hidden';
        dropdown.style.opacity = '0';
        dropdown.style.pointerEvents = 'none';
        
        // Restaurar dropdown a su posición original si fue movido
        if (dropdown.dataset.originalParent) {
            const originalParent = document.querySelector(dropdown.dataset.originalParent);
            if (originalParent) {
                originalParent.appendChild(dropdown);
            }
            delete dropdown.dataset.originalParent;
        }
    }
};

// Cerrar menú móvil al hacer clic fuera
document.addEventListener('click', function(e) {
    const mobileMenu = document.querySelector('.menu-config__mobile-menu');
    const dropdown = document.getElementById('menu-config-mobile-menu-dropdown');
    const button = document.getElementById('menu-config-mobile-menu-btn');
    
    if (dropdown && !mobileMenu?.contains(e.target) && !dropdown.contains(e.target) && !button?.contains(e.target)) {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
        dropdown.style.visibility = 'hidden';
        dropdown.style.opacity = '0';
        dropdown.style.pointerEvents = 'none';
        
        // Restaurar dropdown a su posición original si fue movido
        if (dropdown.dataset.originalParent) {
            const originalParent = document.querySelector(dropdown.dataset.originalParent);
            if (originalParent) {
                originalParent.appendChild(dropdown);
            }
            delete dropdown.dataset.originalParent;
        }
    }

    // Cerrar todos los menús móviles de items
    const itemMenus = document.querySelectorAll('.menu-config__item-mobile-menu-dropdown');
    itemMenus.forEach(menu => {
        const button = menu.previousElementSibling || document.querySelector(`[onclick*="item-menu-${menu.id.replace('item-menu-', '')}"]`);
        if (!menu.contains(e.target) && (!button || !button.contains(e.target))) {
            menu.classList.remove('show');
            menu.style.display = 'none';
            // Restaurar al contenedor original si fue movido
            if (menu.dataset.originalParent) {
                const originalParent = document.querySelector(menu.dataset.originalParent);
                if (originalParent) {
                    originalParent.appendChild(menu);
                }
                delete menu.dataset.originalParent;
            }
        }
    });
});

/**
 * Abrir/cerrar menú móvil de un item específico
 */
window.toggleItemMobileMenu = function toggleItemMobileMenu(event, itemId) {
    event.stopPropagation();
    event.preventDefault();
    
    const dropdown = document.getElementById('item-menu-' + itemId);
    const button = event.target.closest('.menu-config__item-mobile-menu-btn');
    
    if (!dropdown || !button) {
        console.warn('[MenuConfig] No se encontró dropdown o botón para item:', itemId);
        return;
    }
    
    const isShowing = dropdown.classList.contains('show');
    
    if (isShowing) {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
        // Restaurar al contenedor original si fue movido
        if (dropdown.dataset.originalParent) {
            const originalParent = document.querySelector(dropdown.dataset.originalParent);
            if (originalParent) {
                originalParent.appendChild(dropdown);
            }
            delete dropdown.dataset.originalParent;
        }
    } else {
        // Cerrar otros menús abiertos
        document.querySelectorAll('.menu-config__mobile-menu-dropdown.show, .menu-config__item-mobile-menu-dropdown.show').forEach(menu => {
            menu.classList.remove('show');
            menu.style.display = 'none';
            // Restaurar al contenedor original si fue movido
            if (menu.dataset.originalParent) {
                const originalParent = document.querySelector(menu.dataset.originalParent);
                if (originalParent) {
                    originalParent.appendChild(menu);
                }
                delete menu.dataset.originalParent;
            }
        });
        
        // Mover el dropdown al body para evitar problemas de overflow
        if (dropdown.parentElement !== document.body) {
            dropdown.dataset.originalParent = '#' + dropdown.parentElement.id || '.' + dropdown.parentElement.className.split(' ')[0];
            document.body.appendChild(dropdown);
        }
        
        // Calcular posición usando getBoundingClientRect
        const rect = button.getBoundingClientRect();
        const dropdownWidth = 180; // min-width del dropdown
        const dropdownHeight = dropdown.offsetHeight || 200; // Estimación si no está renderizado
        
        // Calcular posición - alinear completamente a la derecha del botón
        let top = rect.bottom + 4;
        let right = window.innerWidth - rect.right; // Distancia desde el borde derecho de la pantalla al borde derecho del botón
        
        // Ajustar si se sale por abajo
        if (top + dropdownHeight > window.innerHeight) {
            top = Math.max(8, rect.top - dropdownHeight); // Mostrar arriba en lugar de abajo
        }
        
        // Asegurar que no se salga por arriba
        if (top < 8) {
            top = 8;
        }
        
        // Asegurar que no se salga por la derecha
        if (right < 8) {
            right = 8;
        }
        
        // Aplicar estilos - usar right para alineación desde la derecha
        dropdown.style.position = 'fixed';
        dropdown.style.top = top + 'px';
        dropdown.style.right = right + 'px';
        dropdown.style.left = 'auto';
        dropdown.style.bottom = 'auto';
        dropdown.style.zIndex = '999999';
        dropdown.style.display = 'block';
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.style.pointerEvents = 'auto';
        
        dropdown.classList.add('show');
    }
};

/**
 * Cerrar menú móvil de un item específico
 */
window.closeItemMobileMenu = function closeItemMobileMenu(itemId) {
    const dropdown = document.getElementById('item-menu-' + itemId);
    if (dropdown) {
        dropdown.classList.remove('show');
    }
};

// Asegurar que las funciones estén siempre disponibles en window
// Esto es importante porque cuando se recarga el módulo, el script se ejecuta de nuevo
// y necesitamos que las funciones estén disponibles inmediatamente

// Inicializar cuando el DOM esté listo
function initMenuConfig() {
    // Verificar que el contenedor existe antes de inicializar
    const listContainer = document.getElementById('menu-config-list');
    if (listContainer) {
        initialize();
    } else {
        // Si el contenedor no existe, esperar un poco y reintentar
        setTimeout(initMenuConfig, 100);
    }
}

// Ejecutar inicialización
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMenuConfig);
} else {
    // Usar un pequeño delay para asegurar que el DOM esté completamente renderizado
    setTimeout(initMenuConfig, 100);
}

// También escuchar eventos de recarga del módulo
if (window.lego && window.lego.events) {
    window.lego.events.on('lego:module:reloaded', function(event) {
        if (event.detail && event.detail.moduleId === 'menu-config') {
            console.log('[MenuConfig] Módulo recargado, reinicializando...');
            setTimeout(initMenuConfig, 200);
        }
    });
}

