/**
 * Testimonial Edit Component JavaScript
 * Handles testimonial edit form logic
 */

console.log('[TestimonialEdit] Script loaded');

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

async function initializeForm() {
    console.log('[TestimonialEdit] Initializing edit form...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[TestimonialEdit] No active module');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[TestimonialEdit] Module container not found');
        return;
    }

    const form = activeModuleContainer.querySelector('.testimonial-form');
    if (!form) {
        console.warn('[TestimonialEdit] Form not found in active module');
        return;
    }

    const testimonialId = parseInt(form.dataset.testimonialId);
    if (!testimonialId) {
        console.error('[TestimonialEdit] Testimonial ID not valid');
        return;
    }

    const loadingDiv = activeModuleContainer.querySelector('#testimonial-form-loading');
    const formEl = activeModuleContainer.querySelector('#testimonial-edit-form');
    const submitBtn = activeModuleContainer.querySelector('#testimonial-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#testimonial-form-cancel-btn');

    try {
        // Load testimonial data
        const response = await fetch(`${HOST_NAME}/api/testimonials/${testimonialId}`);

        if (!response.ok) {
            throw new Error('Error al cargar el testimonio');
        }

        const result = await response.json();
        console.log('[TestimonialEdit] API response:', result);

        // Extract data from API response format {success: true, data: {...}}
        const testimonialData = result.data || result;
        console.log('[TestimonialEdit] Testimonial data:', testimonialData);

        // Populate form using activeModuleContainer
        const authorInput = activeModuleContainer.querySelector('#testimonial-author');
        const messageTextarea = activeModuleContainer.querySelector('#testimonial-message');

        if (authorInput) authorInput.value = testimonialData.author || '';
        if (messageTextarea) messageTextarea.value = testimonialData.message || '';

        // Hide loading, show form
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (formEl) formEl.style.display = 'flex';

        // Form submission
        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('[TestimonialEdit] Form submitted');

            // Get form data using activeModuleContainer
            const authorInput = activeModuleContainer.querySelector('#testimonial-author');
            const messageTextarea = activeModuleContainer.querySelector('#testimonial-message');

            const author = authorInput.value.trim();
            const message = messageTextarea.value.trim();

            // Validation
            if (!author || !message) {
                if (window.AlertService) {
                    window.AlertService.error('Campos requeridos', 'Por favor completa todos los campos obligatorios');
                } else {
                    alert('Por favor completa todos los campos obligatorios');
                }
                return;
            }

            // Prepare data
            const updateData = {
                author,
                message
            };

            console.log('[TestimonialEdit] Update data:', updateData);

            try {
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Guardando...';

                // Show loading
                if (window.legoLoading) {
                    window.legoLoading.show();
                }

                // Submit to API
                const response = await fetch(`${HOST_NAME}/api/testimonials/${testimonialId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Error al actualizar el testimonio');
                }

                const result = await response.json();
                console.log('[TestimonialEdit] Testimonial updated:', result);

                // Success - show message
                if (window.AlertService) {
                    window.AlertService.success('Éxito', 'Testimonio actualizado exitosamente');
                }

                // Close current window
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();

                    // Navigate to testimonials list and refresh table
                    setTimeout(() => {
                        const menuItem = document.querySelector('[data-menu-item-id="4-1"]');
                        if (menuItem) {
                            const button = menuItem.querySelector('.custom-button');
                            if (button) {
                                button.click();

                                // Refresh table after module opens
                                setTimeout(() => {
                                    const refreshFn = window.legoTable_testimonials_table_refresh;
                                    if (refreshFn) refreshFn();
                                }, 300);
                            }
                        }
                    }, 100);
                }

            } catch (error) {
                console.error('[TestimonialEdit] Error updating testimonial:', error);

                if (window.AlertService) {
                    window.AlertService.error('Error', error.message);
                } else {
                    alert(`Error al actualizar el testimonio: ${error.message}`);
                }

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Cambios';
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
                console.log('[TestimonialEdit] Cancel clicked');
                if (window.legoWindowManager) {
                    window.legoWindowManager.closeCurrentWindow();
                }
            });
        }

        console.log('[TestimonialEdit] Form initialized successfully');

    } catch (error) {
        console.error('[TestimonialEdit] Error loading testimonial:', error);
        if (loadingDiv) {
            loadingDiv.innerHTML = `
                <div class="testimonial-form__error">
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
            console.error('[TestimonialEdit] Timeout: No active module found');
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInit, 50);
        } else {
            console.error('[TestimonialEdit] Timeout: Module container not found');
        }
        return;
    }

    const form = activeModuleContainer.querySelector('.testimonial-form');

    if (form) {
        console.log('[TestimonialEdit] Form found, initializing...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInit, 50);
    } else {
        console.error('[TestimonialEdit] Timeout: Form not found after', maxAttempts, 'attempts');
    }
}

tryInit();
