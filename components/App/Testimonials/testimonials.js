/**
 * Testimonials Component JavaScript
 * Handles testimonial list interactions
 */

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

/**
 * Open create testimonial window
 */
function openCreateTestimonialModule() {
    console.log('[Testimonials] Opening create testimonial window');

    if (window.legoWindowManager) {
        window.legoWindowManager.openModuleWithMenu({
            moduleId: 'testimonials-create',
            parentMenuId: '4', // ID del menú "Testimonios"
            label: 'Crear Testimonio',
            icon: 'add-circle-outline',
            url: `${HOST_NAME}/component/testimonials/create`
        });
    } else {
        console.error('[Testimonials] legoWindowManager not available');
    }
}

/**
 * Handle edit testimonial
 */
function handleEditTestimonial(testimonialData) {
    console.log('[Testimonials] Edit testimonial:', testimonialData);

    if (window.legoWindowManager) {
        window.legoWindowManager.openModuleWithMenu({
            moduleId: `testimonials-edit-${testimonialData.id}`,
            parentMenuId: '4', // ID del menú "Testimonios"
            label: `Editar: ${testimonialData.author}`,
            icon: 'create-outline',
            url: `${HOST_NAME}/component/testimonials/edit?id=${testimonialData.id}`
        });
    } else {
        console.error('[Testimonials] legoWindowManager not available');
    }
}

/**
 * Handle delete testimonial
 */
async function handleDeleteTestimonial(testimonialData) {
    console.log('[Testimonials] Delete testimonial:', testimonialData);

    // Show confirmation dialog using ConfirmationService
    if (!window.ConfirmationService) {
        console.error('[Testimonials] ConfirmationService not available');
        return;
    }

    const confirmed = await window.ConfirmationService.delete(`el testimonio de "${testimonialData.author}"`, {
        title: '¿Eliminar testimonio?',
        description: 'Esta acción no se puede deshacer.'
    });

    if (!confirmed) {
        return;
    }

    try {
        // Show loading
        if (window.legoLoading) {
            window.legoLoading.show();
        }

        // Delete via API
        const response = await fetch(`${HOST_NAME}/api/testimonials/${testimonialData.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Error al eliminar el testimonio');
        }

        console.log('[Testimonials] Testimonial deleted successfully');

        // Refresh table
        if (window.legoTable_testimonials_table_refresh) {
            window.legoTable_testimonials_table_refresh();
        }

        // Show success message
        if (window.AlertService) {
            window.AlertService.success('Éxito', 'Testimonio eliminado exitosamente');
        } else {
            alert('Testimonio eliminado exitosamente');
        }

    } catch (error) {
        console.error('[Testimonials] Error deleting testimonial:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', 'Error al eliminar el testimonio. Por favor intenta nuevamente.');
        } else {
            alert('Error al eliminar el testimonio. Por favor intenta nuevamente.');
        }
    } finally {
        // Hide loading
        if (window.legoLoading) {
            window.legoLoading.hide();
        }
    }
}

// Export functions to window for global access
window.openCreateTestimonialModule = openCreateTestimonialModule;
window.handleEditTestimonial = handleEditTestimonial;
window.handleDeleteTestimonial = handleDeleteTestimonial;

console.log('[Testimonials] Module loaded');
