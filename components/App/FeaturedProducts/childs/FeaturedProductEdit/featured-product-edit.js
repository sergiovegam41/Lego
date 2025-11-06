/**
 * Featured Product Edit Component JavaScript
 * Handles featured product editing form logic
 */

console.log('[FeaturedProductEdit] Script loaded');

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

async function initializeForm() {
    console.log('[FeaturedProductEdit] Initializing edit form...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[FeaturedProductEdit] No active module');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[FeaturedProductEdit] Module container not found');
        return;
    }

    const form = activeModuleContainer.querySelector('.featured-product-form');
    if (!form) {
        console.warn('[FeaturedProductEdit] Form not found in active module');
        return;
    }

    const featuredProductId = parseInt(form.dataset.featuredProductId);
    if (!featuredProductId) {
        console.error('[FeaturedProductEdit] Featured product ID not valid');
        return;
    }

    const loadingDiv = activeModuleContainer.querySelector('#featured-product-form-loading');
    const formEl = activeModuleContainer.querySelector('#featured-product-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#featured-product-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#featured-product-form-cancel-btn');

    try {
        // Load featured product data
        const response = await fetch(`${HOST_NAME}/api/featured-products/${featuredProductId}`);

        if (!response.ok) {
            throw new Error('Error al cargar el producto destacado');
        }

        const result = await response.json();
        console.log('[FeaturedProductEdit] API response:', result);

        // Extract data from API response format {success: true, data: {...}}
        const featuredProductData = result.data || result;
        console.log('[FeaturedProductEdit] Featured product data:', featuredProductData);

        // Populate form using activeModuleContainer
        const descriptionInput = activeModuleContainer.querySelector('#featured-product-description');
        const sortOrderInput = activeModuleContainer.querySelector('#featured-product-sort-order');
        const isActiveInput = activeModuleContainer.querySelector('#featured-product-is-active');

        // Set select values using LegoSelect API
        if (window.LegoSelect) {
            if (featuredProductData.product_id) {
                window.LegoSelect.setValue('featured-product-product-id', featuredProductData.product_id.toString());
            }
            if (featuredProductData.tag) {
                window.LegoSelect.setValue('featured-product-tag', featuredProductData.tag);
            }
        }

        if (descriptionInput) descriptionInput.value = featuredProductData.description || '';
        if (sortOrderInput) sortOrderInput.value = featuredProductData.sort_order || '0';
        if (isActiveInput) isActiveInput.checked = featuredProductData.is_active ?? true;

        // Hide loading, show form
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (formEl) formEl.style.display = 'flex';

        // Form submission
        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('[FeaturedProductEdit] Form submitted');

            // Get form data using LegoSelect API and activeModuleContainer
            const descriptionInput = activeModuleContainer.querySelector('#featured-product-description');
            const sortOrderInput = activeModuleContainer.querySelector('#featured-product-sort-order');
            const isActiveInput = activeModuleContainer.querySelector('#featured-product-is-active');

            // Get select values using LegoSelect API
            const productId = window.LegoSelect?.getValue('featured-product-product-id') || '';
            const tag = window.LegoSelect?.getValue('featured-product-tag') || '';
            const description = descriptionInput?.value.trim() || '';
            const sortOrder = parseInt(sortOrderInput?.value || '0');
            const isActive = isActiveInput?.checked ?? true;

            // Validation - tag is now optional
            if (!productId) {
                if (window.AlertService) {
                    window.AlertService.error('Campo requerido', 'Por favor selecciona un producto');
                } else {
                    alert('Por favor selecciona un producto');
                }
                return;
            }

            // Prepare data
            const updateData = {
                product_id: parseInt(productId),
                tag,
                description,
                sort_order: sortOrder,
                is_active: isActive
            };

            console.log('[FeaturedProductEdit] Update data:', updateData);

            try {
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<ion-icon name="checkmark-outline"></ion-icon><span>Guardando...</span>';

                // Show loading
                if (window.legoLoading) {
                    window.legoLoading.show();
                }

                // Submit to API (PUT request)
                const response = await fetch(`${HOST_NAME}/api/featured-products/${featuredProductId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Error al actualizar el producto destacado');
                }

                const result = await response.json();
                console.log('[FeaturedProductEdit] Featured product updated:', result);

                // Success - show message
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Producto destacado actualizado exitosamente');
                }

                // Close current window
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();

                    // Navigate to featured products list and refresh table
                    setTimeout(() => {
                        const menuItem = document.querySelector('[data-menu-item-id="5-1"]');
                        if (menuItem) {
                            const button = menuItem.querySelector('.custom-button');
                            if (button) {
                                button.click();

                                // Refresh table after module opens
                                setTimeout(() => {
                                    const refreshFn = window.legoTable_featured_products_table_refresh;
                                    if (refreshFn) refreshFn();
                                }, 300);
                            }
                        }
                    }, 100);
                }

            } catch (error) {
                console.error('[FeaturedProductEdit] Error updating featured product:', error);

                if (window.AlertService) {
                    window.AlertService.error('Error', error.message);
                } else {
                    alert(`Error al actualizar el producto destacado: ${error.message}`);
                }

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<ion-icon name="checkmark-outline"></ion-icon><span>Guardar Cambios</span>';
            } finally {
                // Hide loading
                if (window.legoLoading) {
                    window.legoLoading.hide();
                }
            }
        });

        // Cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                console.log('[FeaturedProductEdit] Cancel clicked');
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();
                }
            });
        }

        console.log('[FeaturedProductEdit] Form initialized successfully');

    } catch (error) {
        console.error('[FeaturedProductEdit] Error loading featured product:', error);
        if (loadingDiv) {
            loadingDiv.innerHTML = `
                <div class="featured-product-form__error">
                    <h2>Error</h2>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }
}

let attempts = 0;
const maxAttempts = 50;

function tryInit() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[FeaturedProductEdit] Timeout: No active module found');
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[FeaturedProductEdit] Timeout: Module container not found');
        }
        return;
    }

    const form = activeModuleContainer.querySelector('.featured-product-form');

    if (form) {
        console.log('[FeaturedProductEdit] Form found, initializing...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInit, 50);
    } else {
        console.error('[FeaturedProductEdit] Timeout: Form not found after', maxAttempts, 'attempts');
    }
}

tryInit();
