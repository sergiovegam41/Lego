/**
 * ProductsTableDemo JavaScript
 *
 * Define los callbacks para las acciones de la tabla.
 * Los callbacks se ejecutan cuando el usuario hace clic en los botones de acciones.
 */

console.log('[ProductsTableDemo] Component loaded');

/**
 * Callback para editar producto
 * Se ejecuta cuando el usuario hace clic en el botón "Editar"
 */
window.handleEdit = function(rowData, tableId) {
    console.log('[ProductsTableDemo] Editar producto:', rowData);

    // EJEMPLO: Redirigir a página de edición
    window.location.href = `/component/products-crud-v3/edit?id=${rowData.id}`;

    // ALTERNATIVA: Abrir modal de edición
    // window.lego.alert.info({
    //     title: 'Editar Producto',
    //     text: `Editando: ${rowData.name}`
    // });
};

/**
 * Callback para eliminar producto
 * Se ejecuta cuando el usuario hace clic en el botón "Eliminar" y confirma
 */
window.handleDelete = async function(rowData, tableId) {
    console.log('[ProductsTableDemo] Eliminar producto:', rowData);

    try {
        // Hacer fetch al endpoint de eliminación
        const response = await fetch('/api/products/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: rowData.id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.msj || 'Error al eliminar producto');
        }

        // Mostrar mensaje de éxito
        if (window.lego && window.lego.alert) {
            await window.lego.alert.success({
                title: 'Eliminado',
                text: result.msj || 'Producto eliminado correctamente'
            });
        }

        // Refrescar la tabla - emitir evento para recargar
        if (window.lego && window.lego.events) {
            window.lego.events.emit('table:refresh', tableId);
        } else {
            // Fallback: recargar la página
            window.location.reload();
        }

    } catch (error) {
        console.error('[ProductsTableDemo] Error eliminando producto:', error);

        if (window.lego && window.lego.alert) {
            await window.lego.alert.error({
                title: 'Error',
                text: error.message || 'Error al eliminar producto'
            });
        } else {
            alert('Error al eliminar producto: ' + error.message);
        }
    }
};
