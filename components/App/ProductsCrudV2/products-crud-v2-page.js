/**
 * ProductsCrud V2 Page - CRUD con Bloques Modulares y Páginas Hijo
 *
 * FILOSOFÍA LEGO:
 * Usa bloques reutilizables (ApiClient, TableManager, ValidationEngine, StateManager)
 * sin hardcoding de rutas ni entidades específicas.
 *
 * CARACTERÍSTICAS:
 * ✓ Tabla AG Grid con datos en tiempo real
 * ✓ Página hijo para crear/editar (no modal)
 * ✓ Transición visual natural: slide in/out
 * ✓ Bloques modulares agnósticos
 * ✓ Validación integrada
 * ✓ Manejo de errores elegante
 */

console.log('[ProductsCrudV2] Inicializando CRUD con bloques modulares...');

// ═══════════════════════════════════════════════════════════════════
// CREAR BLOQUES MODULARES
// ═══════════════════════════════════════════════════════════════════

const api = new ApiClient('/api/products');
const state = new StateManager();
const tableManager = new TableManager('products-crud-v2-table');

const validator = new ValidationEngine({
    name: { required: true, minLength: 3 },
    sku: { required: true, minLength: 2 },
    price: { required: true, type: 'number', min: 0 },
    stock: { type: 'number', min: 0 },
    min_stock: { type: 'number', min: 0 },
    category: { required: true },
    description: { minLength: 10 }
});

// Variables de estado local
let currentFormPage = null;
let editingProductId = null;

// ═══════════════════════════════════════════════════════════════════
// CUANDO LA TABLA ESTÉ LISTA
// ═══════════════════════════════════════════════════════════════════

tableManager.onReady(async () => {
    console.log('[ProductsCrudV2] Tabla lista, configurando...');
    configureTable();
    await loadProducts();
});

// ═══════════════════════════════════════════════════════════════════
// CONFIGURAR TABLA CON RENDERIZADORES
// ═══════════════════════════════════════════════════════════════════

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
                        <button class="lego-table-action-btn edit-btn" onclick="openEditPage(${params.data.id})" title="Editar">
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

// ═══════════════════════════════════════════════════════════════════
// CARGAR PRODUCTOS DESDE API
// ═══════════════════════════════════════════════════════════════════

async function loadProducts() {
    try {
        console.log('[ProductsCrudV2] Cargando productos...');
        tableManager.setLoading(true);
        const result = await api.list();

        if (result.success) {
            const products = result.data || [];
            tableManager.setData(products);
            tableManager.updateRowCount();
            state.setState('products', products);
            state.emit('products:loaded', { count: products.length });

            console.log(`[ProductsCrudV2] ${products.length} productos cargados`);
        } else {
            AlertService.error('Error al cargar productos');
        }
    } catch (error) {
        console.error('[ProductsCrudV2] Error:', error);
        AlertService.error('Error de conexión');
    } finally {
        tableManager.setLoading(false);
    }
}

// ═══════════════════════════════════════════════════════════════════
// ABRIR PÁGINA FORMULARIO (CREAR)
// ═══════════════════════════════════════════════════════════════════

window.openCreatePage = async function() {
    console.log('[ProductsCrudV2] Abriendo página de crear...');
    editingProductId = null;

    try {
        const formPageHtml = await loadFormPageComponent(null, {});
        showFormPage(formPageHtml, 'create');
    } catch (error) {
        console.error('[ProductsCrudV2] Error al abrir formulario:', error);
        AlertService.error('Error al abrir formulario');
    }
};

// ═══════════════════════════════════════════════════════════════════
// ABRIR PÁGINA FORMULARIO (EDITAR)
// ═══════════════════════════════════════════════════════════════════

window.openEditPage = async function(productId) {
    console.log(`[ProductsCrudV2] Abriendo página de editar #${productId}...`);
    editingProductId = productId;

    try {
        tableManager.setLoading(true);

        // Obtener datos del producto
        const result = await api.get(productId);

        if (result.success && result.data) {
            const productData = result.data;
            const formPageHtml = await loadFormPageComponent(productId, productData);
            showFormPage(formPageHtml, 'edit');
        } else {
            AlertService.error('No se pudo cargar el producto');
        }
    } catch (error) {
        console.error('[ProductsCrudV2] Error:', error);
        AlertService.error('Error al cargar producto');
    } finally {
        tableManager.setLoading(false);
    }
};

// ═══════════════════════════════════════════════════════════════════
// CARGAR COMPONENTE FORMULARIO (DESDE PHP)
// ═══════════════════════════════════════════════════════════════════

async function loadFormPageComponent(productId, initialData) {
    try {
        const params = new URLSearchParams();

        if (productId) {
            params.append('product_id', productId);
            params.append('action', 'edit');
        } else {
            params.append('action', 'create');
        }

        const url = `/component/products-crud-v2/product-form-page?${params.toString()}`;
        console.log('[ProductsCrudV2] Cargando formulario desde:', url);

        const response = await fetch(url, { method: 'GET' });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const html = await response.text();
        console.log('[ProductsCrudV2] Componente cargado, tamaño:', html.length);
        return html;
    } catch (error) {
        console.error('[ProductsCrudV2] Error cargando componente:', error);
        throw error;
    }
}

// ═══════════════════════════════════════════════════════════════════
// MOSTRAR PÁGINA FORMULARIO (CON TRANSICIÓN)
// ═══════════════════════════════════════════════════════════════════

function showFormPage(htmlContent, mode = 'create') {
    console.log(`[ProductsCrudV2] Mostrando página de ${mode}...`);

    const container = document.getElementById('products-form-page-container');
    if (!container) {
        console.error('[ProductsCrudV2] Contenedor no encontrado');
        return;
    }

    // Limpiar contenedor anterior
    container.innerHTML = '';

    // Insertar nuevo contenido
    container.innerHTML = htmlContent;

    // Trigger reflow para que se aplique la transición CSS
    container.offsetHeight;

    // Agregar clase para animación
    const formPage = container.querySelector('.product-form-page');
    if (formPage) {
        formPage.classList.add('active');
    }

    // Conectar handler del formulario
    const form = container.querySelector('form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleFormSubmit(form, mode);
        });
    }

    // Animar overlay
    setTimeout(() => {
        const overlay = container.querySelector('.product-form-page-overlay');
        if (overlay) {
            overlay.style.opacity = '1';
        }
    }, 10);

    console.log(`[ProductsCrudV2] Página de ${mode} abierta`);
}

// ═══════════════════════════════════════════════════════════════════
// CERRAR PÁGINA FORMULARIO (CON TRANSICIÓN)
// ═══════════════════════════════════════════════════════════════════

window.closeFormPage = function() {
    console.log('[ProductsCrudV2] Cerrando página de formulario...');

    const container = document.getElementById('products-form-page-container');
    if (!container) return;

    const formPage = container.querySelector('.product-form-page');
    if (formPage) {
        formPage.classList.remove('active');
    }

    // Esperar a que termine la animación
    setTimeout(() => {
        container.innerHTML = '';
        editingProductId = null;
        console.log('[ProductsCrudV2] Página cerrada');
    }, 300);
};

// ═══════════════════════════════════════════════════════════════════
// MANEJAR SUBMIT DEL FORMULARIO
// ═══════════════════════════════════════════════════════════════════

async function handleFormSubmit(form, mode) {
    console.log(`[ProductsCrudV2] Guardando ${mode === 'create' ? 'nuevo' : ''}producto...`);

    // Obtener datos del formulario con FormBuilder
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Convertir booleanos
    data.is_active = data.is_active === '1';

    // Validar
    const errors = validator.validate(data);

    if (validator.hasErrors(errors)) {
        console.log('[ProductsCrudV2] Errores de validación:', errors);

        const errorMessages = Object.entries(errors)
            .map(([field, msgs]) => `${field}: ${msgs[0]}`)
            .join('\n');

        AlertService.error('Errores de validación:\n' + errorMessages);
        return;
    }

    const closeLoading = AlertService.loading(
        mode === 'create' ? 'Creando producto...' : 'Guardando cambios...'
    );

    try {
        let response;

        if (mode === 'create') {
            response = await api.create(data);
        } else {
            response = await api.update({ id: editingProductId, ...data });
        }

        closeLoading();

        if (response.success) {
            await loadProducts();
            closeFormPage();

            AlertService.success(
                mode === 'create'
                    ? 'Producto creado correctamente'
                    : 'Producto actualizado'
            );

            state.emit(
                mode === 'create' ? 'product:created' : 'product:updated',
                response.data || {}
            );
        } else {
            AlertService.error(response.msj || 'Error al guardar');
        }
    } catch (error) {
        closeLoading();
        console.error('[ProductsCrudV2] Error:', error);
        AlertService.error('Error de conexión');
    }
}

// ═══════════════════════════════════════════════════════════════════
// ELIMINAR PRODUCTO
// ═══════════════════════════════════════════════════════════════════

window.deleteProduct = async function(productId) {
    const confirmed = await AlertService.confirmDelete(`el producto #${productId}`);

    if (confirmed) {
        const closeLoading = AlertService.loading('Eliminando...');

        try {
            const response = await api.delete(productId);
            closeLoading();

            if (response.success) {
                await loadProducts();
                AlertService.success('Producto eliminado');
                state.emit('product:deleted', { id: productId });
            } else {
                AlertService.error(response.msj || 'Error al eliminar');
            }
        } catch (error) {
            closeLoading();
            console.error('[ProductsCrudV2] Error:', error);
            AlertService.error('Error de conexión');
        }
    }
};

// ═══════════════════════════════════════════════════════════════════
// ESCUCHAR EVENTOS DE ESTADO
// ═══════════════════════════════════════════════════════════════════

state.on('products:loaded', (data) => {
    console.log(`[ProductsCrudV2] ${data.count} productos en estado`);
});

state.on('product:created', (product) => {
    console.log('[ProductsCrudV2] Nuevo producto creado:', product);
});

state.on('product:updated', (product) => {
    console.log('[ProductsCrudV2] Producto actualizado:', product);
});

state.on('product:deleted', (data) => {
    console.log('[ProductsCrudV2] Producto eliminado:', data.id);
});

// ═══════════════════════════════════════════════════════════════════
// LISTO
// ═══════════════════════════════════════════════════════════════════

console.log('[ProductsCrudV2] ✓ Sistema listo');
