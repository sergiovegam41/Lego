/**
 * Featured Products Component JavaScript
 * Handles featured product list interactions
 */

// Get HOST_NAME from window global
const HOST_NAME = window.HOST_NAME || '';

/**
 * Open create featured product window
 */
function openCreateFeaturedProductModule() {
    console.log('[FeaturedProducts] Opening create window');

    if (window.legoWindowManager) {
        window.legoWindowManager.openModuleWithMenu({
            moduleId: 'featured-products-create',
            parentMenuId: '5', // ID del menú "Productos Destacados"
            label: 'Agregar Producto Destacado',
            icon: 'add-circle-outline',
            url: `${HOST_NAME}/component/featured-products/create`
        });
    } else {
        console.error('[FeaturedProducts] legoWindowManager not available');
    }
}

/**
 * Handle edit featured product
 */
function handleEditFeaturedProduct(featuredProductData) {
    console.log('[FeaturedProducts] Edit featured product:', featuredProductData);

    if (window.legoWindowManager) {
        window.legoWindowManager.openModuleWithMenu({
            moduleId: `featured-products-edit-${featuredProductData.id}`,
            parentMenuId: '5',
            label: `Editar: ${featuredProductData.product_name || 'Producto'}`,
            icon: 'create-outline',
            url: `${HOST_NAME}/component/featured-products/edit?id=${featuredProductData.id}`
        });
    } else {
        console.error('[FeaturedProducts] legoWindowManager not available');
    }
}

/**
 * Handle delete featured product
 */
async function handleDeleteFeaturedProduct(featuredProductData) {
    console.log('[FeaturedProducts] Delete featured product:', featuredProductData);

    // Show confirmation dialog using ConfirmationService
    const productName = featuredProductData.product_name || 'este producto';

    if (!window.ConfirmationService) {
        console.error('[FeaturedProducts] ConfirmationService not available');
        return;
    }

    const confirmed = await window.ConfirmationService.delete(`el producto destacado "${productName}"`, {
        title: '¿Eliminar producto destacado?',
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
        const response = await fetch(`${HOST_NAME}/api/featured-products/${featuredProductData.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Error al eliminar el producto destacado');
        }

        console.log('[FeaturedProducts] Featured product deleted successfully');

        // Refresh table
        if (window.legoTable_featured_products_table_refresh) {
            window.legoTable_featured_products_table_refresh();
        }

        // Show success message
        if (window.AlertService) {
            window.AlertService.success('Éxito', 'Producto destacado eliminado exitosamente');
        } else {
            alert('Producto destacado eliminado exitosamente');
        }

    } catch (error) {
        console.error('[FeaturedProducts] Error deleting featured product:', error);

        if (window.AlertService) {
            window.AlertService.error('Error', 'Error al eliminar el producto destacado. Por favor intenta nuevamente.');
        } else {
            alert('Error al eliminar el producto destacado. Por favor intenta nuevamente.');
        }
    } finally {
        // Hide loading
        if (window.legoLoading) {
            window.legoLoading.hide();
        }
    }
}

// Export functions to window for global access
window.openCreateFeaturedProductModule = openCreateFeaturedProductModule;
window.handleEditFeaturedProduct = handleEditFeaturedProduct;
window.handleDeleteFeaturedProduct = handleDeleteFeaturedProduct;

console.log('[FeaturedProducts] Module loaded');
