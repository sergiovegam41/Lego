/**
 * ProductsCrud v2 - CRUD refactorizado con bloques modulares
 *
 * FILOSOFÍA LEGO:
 * Usa ApiClient, StateManager, ValidationEngine, TableManager, FormBuilder.
 * Bloques agnósticos sin hardcoding de rutas o IDs.
 * Mucho más simple, mantenible y reutilizable.
 *
 * COMPOSICIÓN DE BLOQUES:
 * - ApiClient: comunicación con REST API
 * - StateManager: estado y eventos
 * - ValidationEngine: validación de datos
 * - TableManager: gestión de tabla AG Grid
 * - FormBuilder: gestión de formularios
 */

console.log('[ProductsCrud v2] Inicializando...');

// Crear bloques modulares reutilizables
const api = new ApiClient('/api/products');
const state = new StateManager();
const tableManager = new TableManager('products-crud-table');

const validator = new ValidationEngine({
    name: { required: true, minLength: 3 },
    sku: { required: true, minLength: 2 },
    price: { required: true, type: 'number', min: 0 },
    stock: { type: 'number', min: 0 },
    min_stock: { type: 'number', min: 0 },
    category: { required: true },
    description: { minLength: 10 }
});

// Cuando la tabla esté lista, configurar y cargar datos
tableManager.onReady(async () => {
    console.log('[ProductsCrud v2] Tabla lista, configurando...');
    configureTable();
    await loadProducts();
});

/**
 * Configurar columnas de la tabla
 */
function configureTable() {
    const columnDefs = [
        { field: 'id', headerName: 'ID', width: 80, sortable: true, filter: true, pinned: 'left' },
        { field: 'name', headerName: 'Nombre', width: 200, sortable: true, filter: true },
        { field: 'sku', headerName: 'SKU', width: 120, sortable: true, filter: true },
        { field: 'category', headerName: 'Categoría', width: 150, sortable: true, filter: true },
        {
            field: 'price',
            headerName: 'Precio',
            width: 120,
            sortable: true,
            filter: true,
            cellRenderer: params => params.value ? `$${parseFloat(params.value).toFixed(2)}` : '-'
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
            sortable: false,
            filter: false,
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

    tableManager.setColumnDefs(columnDefs);
}

/**
 * Cargar productos desde API y actualizar tabla
 */
async function loadProducts() {
    try {
        console.log('[ProductsCrud v2] Cargando productos...');
        tableManager.setLoading(true);
        const result = await api.list();

        if (result.success) {
            const products = result.data || [];

            // Guardar en estado para otros componentes
            state.setState('products', products);
            state.emit('products:loaded', { count: products.length });

            // Actualizar tabla con TableManager
            tableManager.setData(products);
            tableManager.updateRowCount();

            console.log(`[ProductsCrud v2] ${products.length} productos cargados`);
        } else {
            AlertService.error('Error al cargar productos');
        }
    } catch (error) {
        console.error('[ProductsCrud v2] Error:', error);
        AlertService.error('Error de conexión');
    } finally {
        tableManager.setLoading(false);
    }
}

/**
 * Crear producto
 */
window.createProduct = async function() {
    const result = await AlertService.componentModal('/component/products-crud/product-form', {
        title: '➕ Nuevo Producto',
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        width: '700px'
    });

    if (result.isConfirmed && result.value) {
        const closeLoading = AlertService.loading('Creando producto...');

        try {
            // Validar
            const errors = validator.validate(result.value);
            if (validator.hasErrors(errors)) {
                closeLoading();
                const message = Object.entries(errors)
                    .map(([field, msgs]) => `${field}: ${msgs[0]}`)
                    .join('\n');
                AlertService.error('Errores de validación:\n' + message);
                return;
            }

            // Crear
            const response = await api.create(result.value);
            closeLoading();

            if (response.success) {
                await loadProducts();
                AlertService.success('Producto creado correctamente');
            } else {
                AlertService.error(response.msj || 'Error al crear');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrud v2] Error:', error);
            AlertService.error('Error de conexión');
        }
    }
};

/**
 * Editar producto
 */
window.editProduct = async function(id) {
    const result = await AlertService.componentModal('/component/products-crud/product-form', {
        title: `✏️ Editar Producto #${id}`,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        width: '700px',
        params: { id }
    });

    if (result.isConfirmed && result.value) {
        const closeLoading = AlertService.loading('Actualizando...');

        try {
            // Validar
            const errors = validator.validate(result.value);
            if (validator.hasErrors(errors)) {
                closeLoading();
                const message = Object.entries(errors)
                    .map(([field, msgs]) => `${field}: ${msgs[0]}`)
                    .join('\n');
                AlertService.error('Errores de validación:\n' + message);
                return;
            }

            // Actualizar
            const response = await api.update({ id, ...result.value });
            closeLoading();

            if (response.success) {
                await loadProducts();
                AlertService.success('Producto actualizado');
            } else {
                AlertService.error(response.msj || 'Error al actualizar');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrud v2] Error:', error);
            AlertService.error('Error de conexión');
        }
    }
};

/**
 * Eliminar producto
 */
window.deleteProduct = async function(id) {
    const confirmed = await AlertService.confirmDelete(`el producto #${id}`);

    if (confirmed) {
        const closeLoading = AlertService.loading('Eliminando...');

        try {
            const response = await api.delete(id);
            closeLoading();

            if (response.success) {
                await loadProducts();
                AlertService.success('Producto eliminado');
            } else {
                AlertService.error(response.msj || 'Error al eliminar');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrud v2] Error:', error);
            AlertService.error('Error de conexión');
        }
    }
};

console.log('[ProductsCrud v2] ✓ Listo para usar');
