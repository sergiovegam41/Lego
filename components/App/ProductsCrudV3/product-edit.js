/**
 * Product Edit - Lógica de edición
 *
 * FILOSOFÍA LEGO:
 * Formulario de edición con carga de datos y validación.
 * Mantiene "las mismas distancias" que ProductCreate.
 *
 * MEJORAS vs V1/V2:
 * ✅ Usa fetch para GET y PUT (sin ApiClient para evitar imports)
 * ✅ Carga datos del producto al iniciar
 * ✅ Usa LegoSelect.setValue() sin .click() hack
 * ✅ Validación antes de actualizar
 */

console.log('[ProductEdit] Script cargado');

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN (copiada de product-create.js)
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
        const input = document.getElementById(`product-${field}`);
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
// CARGAR DATOS DEL PRODUCTO
// ═══════════════════════════════════════════════════════════════════

async function loadProductData(productId) {
    try {
        console.log('[ProductEdit] Cargando producto:', productId);

        // Usar endpoint legacy con query param
        const response = await fetch(`/api/products/get?id=${productId}`);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al cargar producto');
        }

        console.log('[ProductEdit] Producto cargado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ProductEdit] Error cargando producto:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al cargar producto');
        } else {
            alert('Error cargando producto: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// POBLAR FORMULARIO CON DATOS
// ═══════════════════════════════════════════════════════════════════

function populateForm(product) {
    console.log('[ProductEdit] Poblando formulario con:', product);

    // Poblar inputs
    const nameInput = document.getElementById('product-name');
    const descriptionTextarea = document.getElementById('product-description');
    const priceInput = document.getElementById('product-price');
    const stockInput = document.getElementById('product-stock');

    if (nameInput) nameInput.value = product.name || '';
    if (descriptionTextarea) descriptionTextarea.value = product.description || '';
    if (priceInput) priceInput.value = product.price || '';
    if (stockInput) stockInput.value = product.stock || '';

    // Poblar select usando LegoSelect API (con retry para asegurar que esté listo)
    if (product.category) {
        const setCategory = () => {
            if (window.LegoSelect) {
                console.log('[ProductEdit] Seteando categoría:', product.category);
                window.LegoSelect.setValue('product-category', product.category);
            } else {
                console.warn('[ProductEdit] LegoSelect no disponible, reintentando...');
                setTimeout(setCategory, 100);
            }
        };
        setCategory();
    }

    console.log('[ProductEdit] Formulario poblado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// ACTUALIZAR PRODUCTO
// ═══════════════════════════════════════════════════════════════════

async function updateProduct(productId, formData) {
    try {
        // Validar antes de enviar
        const validation = validateForm(formData);
        if (!validation.isValid) {
            console.error('[ProductEdit] Validación fallida:', validation.errors);
            showValidationErrors(validation.errors);
            return null;
        }

        console.log('[ProductEdit] Actualizando producto:', productId, formData);

        const response = await fetch('/api/products/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: productId,
                ...formData
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al actualizar producto');
        }

        console.log('[ProductEdit] Producto actualizado:', result.data);
        return result.data;

    } catch (error) {
        console.error('[ProductEdit] Error actualizando producto:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', error.message || 'Error al actualizar producto');
        } else {
            alert('Error actualizando producto: ' + error.message);
        }

        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR MÓDULO (volver a tabla)
// ═══════════════════════════════════════════════════════════════════

function closeModule() {
    if (!window.moduleStore) {
        console.error('[ProductEdit] ModuleStore no disponible');
        return;
    }

    const currentModule = window.moduleStore.getActiveModule();
    if (currentModule && window.lego && window.lego.closeModule) {
        window.lego.closeModule(currentModule);
        console.log('[ProductEdit] Módulo cerrado');
    }
}

// ═══════════════════════════════════════════════════════════════════
// RECARGAR TABLA DE PRODUCTOS
// ═══════════════════════════════════════════════════════════════════

function reloadProductsTable() {
    // Recargar la tabla usando la función global de refresh
    const refreshFn = window.legoTable_products_table_v3_refresh;

    if (refreshFn) {
        console.log('[ProductEdit] Recargando tabla de productos...');
        refreshFn();
    } else {
        console.warn('[ProductEdit] Función de recarga de tabla no encontrada');
    }
}

// ═══════════════════════════════════════════════════════════════════
// CARGAR IMÁGENES EN FILEPOND
// ═══════════════════════════════════════════════════════════════════

function loadProductImages(images) {
    console.log('[ProductEdit] Cargando imágenes en FilePond:', images);

    // Esperar a que FilePond esté listo
    const waitForFilePond = setInterval(() => {
        const pond = window.FilePondComponent?.getInstance('product-images');

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
                    console.log('[ProductEdit] Imagen agregada a FilePond con ID:', image.id);
                }).catch(error => {
                    console.error('[ProductEdit] Error agregando imagen a FilePond:', error);
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
    console.log('[ProductEdit] Inicializando formulario...');

    const container = document.querySelector('.product-form');
    console.log('[ProductEdit] Container encontrado:', container);
    console.log('[ProductEdit] Atributos del container:', container ? Array.from(container.attributes).map(a => `${a.name}="${a.value}"`).join(', ') : 'N/A');

    const productId = container?.getAttribute('data-product-id');
    console.log('[ProductEdit] Product ID extraído:', productId);

    if (!productId) {
        console.error('[ProductEdit] No se encontró ID de producto en el container');
        return;
    }

    const form = document.getElementById('product-edit-form');
    const submitBtn = document.getElementById('product-form-submit-btn');
    const cancelBtn = document.getElementById('product-form-cancel-btn');
    const loading = document.getElementById('product-form-loading');

    if (!form || !loading) {
        console.warn('[ProductEdit] Elementos no encontrados aún, esperando...');
        return;
    }

    // Cargar datos del producto
    try {
        const product = await loadProductData(productId);

        // Ocultar loading, mostrar form
        loading.style.display = 'none';
        form.style.display = 'flex';

        // Poblar formulario
        populateForm(product);

        // Cargar imágenes en FilePond si existen
        if (product.images && product.images.length > 0) {
            loadProductImages(product.images);
        }

    } catch (error) {
        console.error('[ProductEdit] Error en inicialización:', error);
        if (loading) {
            loading.textContent = 'Error cargando producto';
            loading.style.color = 'red';
        }
        return;
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        console.log('[ProductEdit] Enviando formulario...');

        // Deshabilitar botón mientras se envía
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        try {
            // Recoger datos del formulario
            const formData = {
                name: document.getElementById('product-name')?.value || '',
                description: document.getElementById('product-description')?.value || '',
                price: parseFloat(document.getElementById('product-price')?.value || 0),
                stock: parseInt(document.getElementById('product-stock')?.value || 0),
                category: window.LegoSelect?.getValue('product-category') || ''
            };

            // Obtener IDs de imágenes de FilePond
            const imageIds = window.FilePondComponent?.getImageIds('product-images') || [];
            if (imageIds.length > 0) {
                formData.image_ids = imageIds;
            }

            console.log('[ProductEdit] Datos del formulario:', formData);

            // Actualizar producto
            const updatedProduct = await updateProduct(productId, formData);

            if (updatedProduct) {
                // Éxito
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Producto actualizado correctamente');
                } else {
                    alert('Producto actualizado correctamente');
                }

                // Recargar tabla
                reloadProductsTable();

                // Volver al módulo de la tabla (similar a product-create)
                const tableModule = Object.keys(window.moduleStore.modules).find(id =>
                    id.includes('products-crud-v3') && !id.includes('create') && !id.includes('edit')
                );

                if (tableModule && window.moduleStore) {
                    // Activar el módulo de la tabla
                    window.moduleStore._openModule(tableModule, window.moduleStore.modules[tableModule].component);

                    // Mostrar visualmente el módulo de la tabla
                    document.querySelectorAll('.module-container').forEach(module => module.classList.remove('active'));
                    const tableContainer = document.getElementById(`module-${tableModule}`);
                    if (tableContainer) {
                        tableContainer.classList.add('active');
                    }

                    // Cerrar el módulo de edición
                    const currentModule = window.moduleStore.getActiveModule();
                    if (currentModule && currentModule.includes('edit')) {
                        setTimeout(() => {
                            window.moduleStore.closeModule(currentModule);
                        }, 300);
                    }
                }
            }

        } catch (error) {
            console.error('[ProductEdit] Error:', error);
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

    console.log('[ProductEdit] Formulario inicializado correctamente');
}

// ═══════════════════════════════════════════════════════════════════
// INICIALIZACIÓN INMEDIATA CON RETRY
// ═══════════════════════════════════════════════════════════════════

let attempts = 0;
const maxAttempts = 40; // 40 * 50ms = 2 segundos

function tryInitialize() {
    const container = document.querySelector('.product-form');
    const form = document.getElementById('product-edit-form');

    if (container && form) {
        console.log('[ProductEdit] Elementos encontrados, inicializando...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        console.log(`[ProductEdit] Elementos no encontrados, reintentando... (${attempts}/${maxAttempts})`);
        setTimeout(tryInitialize, 50);
    } else {
        console.error('[ProductEdit] No se pudieron encontrar los elementos después de 2 segundos');
    }
}

// Iniciar
tryInitialize();
