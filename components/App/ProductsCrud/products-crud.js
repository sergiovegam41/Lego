/**
 * ProductsCrud JavaScript
 *
 * FILOSOFÍA LEGO:
 * Maneja todas las operaciones CRUD para productos usando:
 * - AlertService para UX
 * - Fetch API para comunicación con ProductsController
 * - AG Grid API para actualizar tabla dinámicamente
 */

const API_BASE = '/api/products';

// Cargar productos al inicio
// No usar DOMContentLoaded porque el script se carga dinámicamente
console.log('[ProductsCrud] Script cargado, inicializando...');

// Esperar a que la tabla esté lista
setTimeout(() => {
    console.log('[ProductsCrud] Verificando API de tabla...');
    if (typeof legoTable_products_crud_table_api !== 'undefined') {
        console.log('[ProductsCrud] API de tabla encontrada');
        configureTableColumns();
        setTimeout(loadProducts, 100); // Dar tiempo a que se apliquen las columnas
    } else {
        console.error('[ProductsCrud] API de tabla no disponible, reintentando...');
        // Reintentar después de más tiempo
        setTimeout(() => {
            if (typeof legoTable_products_crud_table_api !== 'undefined') {
                console.log('[ProductsCrud] API de tabla encontrada en segundo intento');
                configureTableColumns();
                setTimeout(loadProducts, 100);
            } else {
                console.error('[ProductsCrud] API de tabla no disponible después de 2 intentos');
            }
        }, 1000);
    }
}, 500);

/**
 * Configurar columnas con cellRenderers personalizados
 */
function configureTableColumns() {
    const api = window.legoTable_products_crud_table_api;
    if (!api) {
        console.error('[ProductsCrud] API de tabla no disponible');
        return;
    }

    // Actualizar definiciones de columnas para renderizar HTML
    const columnDefs = [
        { field: 'id', headerName: 'ID', width: 80, sortable: true, filter: true, pinned: 'left' },
        { field: 'name', headerName: 'Nombre', width: 200, sortable: true, filter: true },
        { field: 'category', headerName: 'Categoría', width: 150, sortable: true, filter: true },
        {
            field: 'price',
            headerName: 'Precio',
            width: 120,
            sortable: true,
            filter: true,
            cellRenderer: params => params.value
        },
        {
            field: 'stock',
            headerName: 'Stock',
            width: 100,
            sortable: true,
            filter: true,
            cellRenderer: params => {
                if (params.data && params.data.stock !== undefined) {
                    const stock = params.data.stock;
                    let className = 'stock-empty';
                    if (stock > 10) className = 'stock-high';
                    else if (stock > 0) className = 'stock-low';
                    return `<span class="${className}">${stock}</span>`;
                }
                return params.value;
            }
        },
        {
            field: 'is_active',
            headerName: 'Estado',
            width: 120,
            sortable: true,
            filter: true,
            cellRenderer: params => {
                if (params.data && params.data.is_active !== undefined) {
                    return params.data.is_active
                        ? '<span style="color: #22c55e; font-weight: bold; display: flex; align-items: center; gap: 0.25rem;"><ion-icon name="checkmark-circle"></ion-icon> Activo</span>'
                        : '<span style="color: #9ca3af; display: flex; align-items: center; gap: 0.25rem;"><ion-icon name="close-circle"></ion-icon> Inactivo</span>';
                }
                return params.value;
            }
        },
        {
            field: 'actions',
            headerName: 'Acciones',
            width: 180,
            pinned: 'right',
            cellRenderer: params => {
                if (params.data && params.data.id) {
                    return `
                        <button class="lego-table-action-btn edit-btn" onclick="editProduct(${params.data.id})" title="Editar">
                            <ion-icon name="create-outline"></ion-icon>
                        </button>
                        <button class="lego-table-action-btn delete-btn" onclick="deleteProduct(${params.data.id})" title="Eliminar">
                            <ion-icon name="trash-outline"></ion-icon>
                        </button>
                    `;
                }
                return '';
            }
        }
    ];

    api.setGridOption('columnDefs', columnDefs);
    console.log('[ProductsCrud] Columnas configuradas con cellRenderers');
}

/**
 * Cargar productos desde el backend
 */
async function loadProducts() {
    try {
        console.log('[ProductsCrud] Iniciando carga de productos...');
        const response = await fetch(`${API_BASE}/list`);
        const result = await response.json();

        console.log('[ProductsCrud] Respuesta del API:', result);

        if (result.success) {
            const products = result.data;
            console.log('[ProductsCrud] Productos recibidos:', products.length);

            // Formatear solo el precio
            const formattedProducts = products.map(p => ({
                ...p,
                price: p.price ? `$${parseFloat(p.price).toFixed(2)}` : '-'
            }));

            console.log('[ProductsCrud] Productos formateados:', formattedProducts);

            // Actualizar tabla usando API directamente
            const api = window.legoTable_products_crud_table_api;
            if (api) {
                console.log('[ProductsCrud] Actualizando datos de tabla...');
                api.setGridOption('rowData', formattedProducts);
                console.log('[ProductsCrud] Datos actualizados en tabla');

                // Actualizar contador de registros
                if (typeof window.legoTable_products_crud_table_updateRowCount === 'function') {
                    window.legoTable_products_crud_table_updateRowCount();
                    console.log('[ProductsCrud] Contador actualizado');
                }
            } else {
                console.error('[ProductsCrud] API de tabla no disponible');
            }

            // Actualizar estadísticas
            updateStats(products);

            console.log('[ProductsCrud] Productos cargados exitosamente:', products.length);
        } else {
            AlertService.error(result.message || 'Error al cargar productos');
        }
    } catch (error) {
        console.error('[ProductsCrud] Error:', error);
        AlertService.error('Error de conexión al cargar productos');
    }
}

/**
 * Actualizar estadísticas
 */
function updateStats(products) {
    const total = products.length;
    const active = products.filter(p => p.is_active).length;
    const inStock = products.filter(p => p.stock > 0).length;
    const totalValue = products.reduce((sum, p) => sum + (parseFloat(p.price) * parseInt(p.stock)), 0);

    // Actualizar con verificación de null
    const totalEl = document.getElementById('total-products');
    const activeEl = document.getElementById('active-products');
    const inStockEl = document.getElementById('instock-products');
    const valueEl = document.getElementById('total-value');

    if (totalEl) totalEl.textContent = total;
    if (activeEl) activeEl.textContent = active;
    if (inStockEl) inStockEl.textContent = inStock;
    if (valueEl) valueEl.textContent = '$' + totalValue.toFixed(2);
}

/**
 * Crear nuevo producto usando LEGO ComponentModal
 */
window.createProduct = async function() {
    // Cargar formulario desde el child component
    const result = await AlertService.componentModal('/component/products-crud/product-form', {
     
        confirmButtonText: 'Crear Producto',
        cancelButtonText: 'Cancelar',
        width: '700px'
    });

    // Si el usuario confirmó el formulario
    if (result.isConfirmed && result.value) {
        const formValues = result.value;
        const closeLoading = AlertService.loading('Creando producto...');

        try {
            const response = await fetch(`${API_BASE}/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formValues)
            });

            const result = await response.json();
            closeLoading();

            if (result.success) {
                loadProducts(); // Recargar tabla inmediatamente
                AlertService.success('Producto creado correctamente');
            } else {
                AlertService.error(result.message || 'Error al crear producto');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrud] Error:', error);
            AlertService.error('Error de conexión al crear producto');
        }
    }
};

/**
 * Editar producto existente usando LEGO ComponentModal
 */
window.editProduct = async function(id) {
    // Cargar formulario desde el child component con el ID del producto
    const result = await AlertService.componentModal('/component/products-crud/product-form', {
        title: `✏️ Editar Producto #${id}`,
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        width: '700px',
        params: { id: id } // Pasar ID al componente para que cargue los datos
    });

    // Si el usuario confirmó el formulario
    if (result.isConfirmed && result.value) {
        const formValues = { ...result.value, id }; // Agregar ID a los datos
        const closeLoadingUpdate = AlertService.loading('Actualizando producto...');

        try {
            const updateResponse = await fetch(`${API_BASE}/update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formValues)
            });

            const updateResult = await updateResponse.json();
            closeLoadingUpdate();

            if (updateResult.success) {
                loadProducts(); // Recargar tabla inmediatamente
                AlertService.success('Producto actualizado correctamente');
            } else {
                AlertService.error(updateResult.message || 'Error al actualizar producto');
            }
        } catch (error) {
            closeLoadingUpdate();
            console.error('[ProductsCrud] Error:', error);
            AlertService.error('Error de conexión al actualizar producto');
        }
    }
};

/**
 * Eliminar producto
 */
window.deleteProduct = async function(id) {
    const confirmed = await AlertService.confirmDelete('el producto #' + id);

    if (confirmed) {
        const closeLoading = AlertService.loading('Eliminando producto...');

        try {
            const response = await fetch(`${API_BASE}/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });

            const result = await response.json();
            closeLoading();

            if (result.success) {
                loadProducts(); // Recargar tabla inmediatamente
                AlertService.success('Producto eliminado correctamente');
            } else {
                AlertService.error(result.message || 'Error al eliminar producto');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrud] Error:', error);
            AlertService.error('Error de conexión al eliminar producto');
        }
    }
};

console.log('[LEGO Framework] ProductsCrud JS cargado');
