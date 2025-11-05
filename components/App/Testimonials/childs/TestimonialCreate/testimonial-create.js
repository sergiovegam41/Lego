/**
 * Testimonial Create Component JavaScript
 * Handles testimonial creation form logic
 */

console.log('[TestimonialCreate] Script loaded');

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

function initializeForm() {
    console.log('[TestimonialCreate] Initializing form...');

    const activeModuleId = window.moduleStore?.getActiveModule();
    if (!activeModuleId) {
        console.error('[TestimonialCreate] No active module');
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
    if (!activeModuleContainer) {
        console.error('[TestimonialCreate] Module container not found');
        return;
    }

    const form = activeModuleContainer.querySelector('#testimonial-create-form');
    const submitBtn = activeModuleContainer.querySelector('#testimonial-form-submit-btn');
    const cancelBtn = activeModuleContainer.querySelector('#testimonial-form-cancel-btn');

    if (!form) {
        console.warn('[TestimonialCreate] Form not found');
        return;
    }

    console.log('[TestimonialCreate] Form found, attaching listeners');

    // Cancel button - close window
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            console.log('[TestimonialCreate] Cancel clicked');
            if (window.legoWindowManager) {
                window.legoWindowManager.closeCurrentWindow();
            }
        });
    }

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('[TestimonialCreate] Form submitted');

        // Get form data using activeModuleContainer
        const authorInput = activeModuleContainer.querySelector('#testimonial-author');
        const messageTextarea = activeModuleContainer.querySelector('#testimonial-message');

        if (!authorInput || !messageTextarea) {
            console.error('[TestimonialCreate] Form fields not found');
            return;
        }

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
        const testimonialData = {
            author,
            message,
            is_active: true
        };

        console.log('[TestimonialCreate] Testimonial data:', testimonialData);

        try {
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creando...';

            // Show loading
            if (window.legoLoading) {
                window.legoLoading.show();
            }

            // Submit to API
            const response = await fetch(`${HOST_NAME}/api/testimonials`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(testimonialData)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Error al crear el testimonio');
            }

            const result = await response.json();
            console.log('[TestimonialCreate] Testimonial created:', result);

            // Success - show message
            if (window.AlertService) {
                window.AlertService.success('Éxito', 'Testimonio creado exitosamente');
            } else {
                alert('Testimonio creado exitosamente');
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

                            // Wait for table to be ready and refresh it
                            let attempts = 0;
                            const maxAttempts = 20;
                            const checkAndRefresh = () => {
                                const refreshFn = window.legoTable_testimonials_table_refresh;
                                if (refreshFn && typeof refreshFn === 'function') {
                                    console.log('[TestimonialCreate] Refreshing table...');
                                    refreshFn();
                                } else if (attempts < maxAttempts) {
                                    attempts++;
                                    setTimeout(checkAndRefresh, 100);
                                } else {
                                    console.warn('[TestimonialCreate] Could not refresh table (timeout)');
                                }
                            };
                            setTimeout(checkAndRefresh, 200);
                        }
                    }
                }, 100);
            }

        } catch (error) {
            console.error('[TestimonialCreate] Error creating testimonial:', error);

            if (window.AlertService) {
                window.AlertService.error('Error', error.message);
            } else {
                alert(`Error al crear el testimonio: ${error.message}`);
            }

            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Crear Testimonio';
        } finally {
            // Hide loading
            if (window.legoLoading) {
                window.legoLoading.hide();
            }
        }
    });

    console.log('[TestimonialCreate] Form initialized successfully');
}

let attempts = 0;
const maxAttempts = 50;

function tryInitialize() {
    const activeModuleId = window.moduleStore?.getActiveModule();

    if (!activeModuleId) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);

    if (!activeModuleContainer) {
        if (attempts < maxAttempts) {
            attempts++;
            setTimeout(tryInitialize, 50);
        }
        return;
    }

    const form = activeModuleContainer.querySelector('#testimonial-create-form');

    if (form) {
        console.log('[TestimonialCreate] Form found, initializing...');
        initializeForm();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    }
}

tryInitialize();
