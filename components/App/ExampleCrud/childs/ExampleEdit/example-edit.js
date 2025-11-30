/**
 * Example Edit - Lógica de edición (con ComponentContext)
 *
 * FILOSOFÍA LEGO:
 * ✅ CERO hardcoding - usa ComponentContext
 * ✅ Carga datos del registro al iniciar
 * ✅ Usa LegoSelect.setValue() sin .click() hack
 * ✅ Validación antes de actualizar
 */

console.log('[ExampleEdit] Script cargado');

// ═══════════════════════════════════════════════════════════════════
// SCREEN CONFIG - Debe coincidir con ExampleEditComponent::SCREEN_*
// ═══════════════════════════════════════════════════════════════════

const SCREEN_CONFIG = {
    screenId: 'example-crud-edit',       // ExampleEditComponent::SCREEN_ID
    parentScreenId: 'example-crud-list', // ExampleCrudComponent::SCREEN_ID
    menuGroupId: 'example-crud',         // ExampleCrudComponent::MENU_GROUP_ID
    apiRoute: '/api/example-crud',
    isDynamic: true                      // ExampleEditComponent::SCREEN_DYNAMIC
};

function apiUrl(action, params = null) {
    let url = `${SCREEN_CONFIG.apiRoute}/${action}`;
    if (params && Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    return url;
}

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN (copiada de example-create.js)
// ═══════════════════════════════════════════════════════════════════

function validateForm(formData) {
    const errors = {};

    // Nombre requerido
    if (!formData.name || formData.name.trim() === '') {
        errors.name = 'El nombre es requerido';
    }

    // Precio requerido y válido
    if (!formData.price || formData.price <= 0) {
        errors.price = 'El precio debe ser mayor a 0';
    }

    // Stock requerido y válido
    if (formData.stock === undefined || formData.stock < 0) {
        errors.stock = 'El stock debe ser mayor o igual a 0';
    }

    // Categoría requerida
    if (!formData.category || formData.category === '') {
        errors.category = 'La categoría es requerida';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

function showValidationErrors(errors) {
    // Limpiar errores previos
    document.querySelectorAll('.lego-input__error, .lego-select__error, .lego-textarea__error').forEach(el => {
        el.remove();
    });

    document.querySelectorAll('.lego-input--error, .lego-select--error, .lego-textarea--error').forEach(el => {
        el.classList.remove('lego-input--error', 'lego-select--error', 'lego-textarea--error');
    });

    // Mostrar nuevos errores
    Object.entries(errors).forEach(([field, message]) => {
        const input = document.getElementById(`example-${field}`);
        if (input) {
            const container = input.closest('.lego-input, .lego-select, .lego-textarea');
            if (container) {
                const errorDiv = document.createElement('div');

                // Determinar tipo de campo
                let errorClass = 'lego-input__error';
                let containerErrorClass = 'lego-input--error';
                if (container.classList.contains('lego-select')) {
                    errorClass = 'lego-select__error';
                    containerErrorClass = 'lego-select--error';
                } else if (container.classList.contains('lego-textarea')) {
                    errorClass = 'lego-textarea__error';
                    containerErrorClass = 'lego-textarea--error';
                }

                errorDiv.className = errorClass;
                errorDiv.textContent = Array.isArray(message) ? message[0] : message;
                container.appendChild(errorDiv);
                container.classList.add(containerErrorClass);
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// CARGAR DATOS DEL REGISTRO
// ═══════════════════════════════════════════════════════════════════

async function loadRecordData(recordId) {
    try {
        console.log('[ExampleEdit] Cargando registro:', recordId);

        // Usar apiUrl()
        const response = await fetch(apiUrl('get', { id: recordId }));
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar registro');
        }

        console.log('[ExampleEdit] Registro cargado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ExampleEdit] Error cargando registro:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al cargar registro');
        } else {
            alert('Error cargando registro: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// POBLAR FORMULARIO CON DATOS
// ═══════════════════════════════════════════════════════════════════

function populateForm(record, activeModuleContainer) {
    console.log('[ExampleEdit] Poblando formulario con:', record);

    // Poblar inputs DENTRO del módulo activo
    const nameInput = activeModuleContainer.querySelector('#example-name');
    const descriptionTextarea = activeModuleContainer.querySelector('#example-description');
    const priceInput = activeModuleContainer.querySelector('#example-price');
    const stockInput = activeModuleContainer.querySelector('#example-stock');

    if (nameInput) nameInput.value = record.name || '';
    if (descriptionTextarea) descriptionTextarea.value = record.description || '';
    if (priceInput) priceInput.value = record.price || '';
    if (stockInput) stockInput.value = record.stock || '';

    // Poblar select usando LegoSelect API (con retry para asegurar que esté listo)
    if (record.category) {
        const setCategory = () => {
            if (window.LegoSelect) {
                console.log('[ExampleEdit] Seteando categoría:', record.category);
                window.LegoSelect.setValue('example-category', record.category);
            } else {
                console.warn('[ExampleEdit] LegoSelect no disponible, reintentando...');
                setTimeout(setCategory, 100);
            }
        };
        setCategory();
    }

    console.log('[ExampleEdit] Formulario poblado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR REGISTRO
// ═══════════════════════════════════════════════════════════════════

async function updateRecord(recordId, formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[ExampleEdit] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        console.log('[ExampleEdit] Actualizando registro:', recordId, formData);

        // Usar apiUrl()
        const response = await fetch(apiUrl('update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: recordId,
                ...formData
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar registro');
        }

        console.log('[ExampleEdit] Registro actualizado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ExampleEdit] Error actualizando registro:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al actualizar registro');
        } else {
            alert('Error actualizando registro: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO (volver a tabla)
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (window.legoWindowManager) {
        // Usar legoWindowManager que se encarga de:
        // 1. Cerrar el módulo
        // 2. Eliminar el ítem dinámico del menú
        // 3. Volver al módulo origen
        window.legoWindowManager.closeCurrentWindow();
        console.log('[ExampleEdit] Módulo cerrado via legoWindowManager');
    } else {
        console.error('[ExampleEdit] legoWindowManager no disponible');
    }
}

// ═══════════════════════════════════════════════════════════════════
// RECARGAR TABLA DE REGISTROS
// ═══════════════════════════════════════════════════════════════════

function reloadExampleCrudTable() {
    // Recargar la tabla usando la función global de refresh
    const refreshFn = window.legoTable_example_crud_table_refresh;

    if (refreshFn) {
        console.log('[ExampleEdit] Recargando tabla de registros...');
        refreshFn();
    } else {
        console.warn('[ExampleEdit] Función de recarga de tabla no encontrada');
    }
}

// ═══════════════════════════════════════════════════════════════════
// CARGAR IMÁGENES EN FILEPOND
// ═══════════════════════════════════════════════════════════════════

function loadRecordImages(images) {
    console.log('[ExampleEdit] Cargando imágenes en FilePond:', images);

    // Esperar a que FilePond esté listo
    const waitForFilePond = setInterval(() => {
        const pond = window.FilePondComponent?.getInstance('example-images');

        if (pond) {
            clearInterval(waitForFilePond);

            // Agregar cada imagen a FilePond
            images.forEach(image => {
                // FilePond espera objetos con esta estructura para archivos existentes
                // IMPORTANTE: Usar el ID como source para que delete() funcione correctamente
                pond.addFile(image.id.toString(), {
                    type: 'local',
                    file: {
                        name: image.original_name || 'image.jpg',
                        size: image.size || 0,
                        type: image.mime_type || 'image/jpeg'
                    },
                    metadata: {
                        poster: image.url, // URL para mostrar la imagen
                        imageId: image.id   // ID para referencia adicional
                    }
                }).then(file => {
                    console.log('[ExampleEdit] Imagen agregada a FilePond con ID:', image.id);
                }).catch(error => {
                    console.error('[ExampleEdit] Error agregando imagen a FilePond:', error);
                });
            });
        }
    }, 100);

    // Timeout de seguridad
    setTimeout(() => {
        clearInterval(waitForFilePond);
    }, 5000);
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN CON RETRY LOGIC
// ═══════════════════════════════════════════════════════════════════

async function initializeForm() {
    console.log('[ExampleEdit] Inicializando formulario...');

    // IMPORTANTE: Buscar el container dentro del módulo activo SOLAMENTE
    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[ExampleEdit] No hay módulo activo');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[ExampleEdit] No se encontró container del módulo activo:', activeModuleId);
        return;
    }

    // Verificar si estamos en estado "sin contexto" (empty state)
    const emptyState = activeModuleContainer.querySelector('.example-form--no-context');
    if (emptyState) {
        console.log('[ExampleEdit] Modo sin contexto detectado, no hay registro para cargar');
        return; // No hay nada que hacer - el PHP ya muestra el mensaje adecuado
    }

    // Buscar el formulario DENTRO del módulo activo
    const container = activeModuleContainer.querySelector('.example-form[data-example-id]');
    console.log('[ExampleEdit] Container encontrado:', container);
    console.log('[ExampleEdit] Atributos del container:', container ? Array.from(container.attributes).map(a => `${a.name}="${a.value}"`).join(', ') : 'N/A');

    const recordId = container?.getAttribute('data-example-id');
    console.log('[ExampleEdit] Record ID extraído:', recordId);

    if (!recordId) {
        console.log('[ExampleEdit] No hay ID de registro - estado sin contexto');
        return;
    }

    // Buscar elementos del form DENTRO del módulo activo
    const form = activeModuleContainer.querySelector('#example-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#example-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#example-form-cancel-btn');
    const loading = activeModuleContainer.querySelector('#example-form-loading');

    if (!form || !loading) {
        console.warn('[ExampleEdit] Elementos no encontrados aún, esperando...');
        return;
    }

    // Cargar datos del registro
    try {
        const record = await loadRecordData(recordId);

        // Ocultar loading, mostrar form
        loading.style.display = 'none';
        form.style.display = 'flex';

        // Poblar formulario (pasando el container del módulo activo)
        populateForm(record, activeModuleContainer);

        // Cargar imágenes en FilePond si existen
        if (record.images && record.images.length > 0) {
            loadRecordImages(record.images);
        }

    } catch (error) {
        console.error('[ExampleEdit] Error en inicialización:', error);
        if (loading) {
            loading.textContent = 'Error cargando registro';
            loading.style.color = 'red';
        }
        return;
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[ExampleEdit] Enviando formulario...');

        // Deshabilitar botón mientras se envía
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        try {
            // Recoger datos del formulario desde el módulo activo
            const formData = {
                name: activeModuleContainer.querySelector('#example-name')?.value || '',
                description: activeModuleContainer.querySelector('#example-description')?.value || '',
                price: parseFloat(activeModuleContainer.querySelector('#example-price')?.value || 0),
                stock: parseInt(activeModuleContainer.querySelector('#example-stock')?.value || 0),
                category: window.LegoSelect?.getValue('example-category') || ''
            };

            // Obtener IDs de imágenes de FilePond
            const imageIds = window.FilePondComponent?.getImageIds('example-images') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            console.log('[ExampleEdit] Datos del formulario:', formData);

            // Actualizar registro
            const updatedRecord = await updateRecord(recordId, formData);

            if (updatedRecord) {
                // Éxito
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Registro actualizado correctamente');
                } else {
                    alert('Registro actualizado correctamente');
                }

                // Cerrar y refrescar el módulo origen automáticamente
                setTimeout(() => {
                    if (window.legoWindowManager) {
                        window.legoWindowManager.closeCurrentWindow({ refresh: true });
                    }
                }, 500); // Delay para que el usuario vea el mensaje de éxito
            }

        } catch (error) {
            console.error('[ExampleEdit] Error:', error);
        } finally {
            // Re-habilitar botón
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar Cambios';
        }
    });

    // Botón cancelar
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            closeModule();
        });
    }

    console.log('[ExampleEdit] Formulario inicializado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN INMEDIATA CON RETRY
// ═══════════════════════════════════════════════════════════════════

let attempts = 0;
const maxAttempts = 40; // 40 * 50ms = 2 segundos

function tryInitialize() {
    // IMPORTANTE: Buscar el módulo activo PRIMERO para evitar conflictos con otros módulos
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            console.log(`[ExampleEdit] ModuleStore no disponible, reintentando... (${attempts}/${maxAttempts})`);
            setTimeout(tryInitialize, 50);
        } else {
            console.error('[ExampleEdit] ModuleStore no disponible después de 2 segundos');
        }
        return;
    }

    // Buscar elementos SOLO dentro del módulo activo
    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            console.log(`[ExampleEdit] Container del módulo activo no encontrado, reintentando... (${attempts}/${maxAttempts})`);
            setTimeout(tryInitialize, 50);
        } else {
            console.error('[ExampleEdit] Container del módulo activo no encontrado después de 2 segundos');
        }
        return;
    }

    // Verificar si es estado sin contexto (empty state) - no hay nada que inicializar
    const emptyState = activeModuleContainer.querySelector('.example-form--no-context');
    if (emptyState) {
        console.log('[ExampleEdit] Modo sin contexto detectado, inicialización no necesaria');
        return; // Nada que hacer
    }

    // Buscar el contenedor específico del edit form DENTRO del módulo activo
    const container = activeModuleContainer.querySelector('.example-form[data-example-id]');
    const form = activeModuleContainer.querySelector('#example-edit-form');

    if (container && form) {
        console.log('[ExampleEdit] Elementos encontrados en módulo activo, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        console.log(`[ExampleEdit] Elementos no encontrados en módulo activo, reintentando... (${attempts}/${maxAttempts})`);
        setTimeout(tryInitialize, 50);
    } else {
        console.error('[ExampleEdit] No se pudieron encontrar los elementos después de 2 segundos');
    }
}

// Iniciar
tryInitialize();
