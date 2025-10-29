/**
 * CrudManager - Sistema de gestión CRUD automático
 *
 * FILOSOFÍA LEGO:
 * Abstrae toda la lógica repetitiva de operaciones CRUD (Create, Read, Update, Delete)
 * para que los desarrolladores solo configuren endpoints y dejen que el framework maneje el resto.
 *
 * PROBLEMA QUE RESUELVE:
 * - Código repetitivo en cada CRUD (~150 líneas idénticas)
 * - Manejo inconsistente de errores
 * - Actualización manual de tablas
 * - Loading states manuales
 * - Validaciones dispersas
 *
 * SOLUCIÓN:
 * - Configuración declarativa
 * - Manejo automático de errores y loading
 * - Integración con AlertService y TableHelper
 * - Hooks para customización
 *
 * EJEMPLO DE USO:
 * ```javascript
 * const productsCrud = new CrudManager({
 *     endpoint: '/api/products',
 *     formPath: '/component/products-crud/product-form',
 *     tableId: 'products-crud-table',
 *     entityName: 'Producto'
 * }).expose();
 *
 * // Automáticamente crea:
 * // window.createProduct()
 * // window.editProduct(id)
 * // window.deleteProduct(id)
 * ```
 */

class CrudManager {
    /**
     * @param {Object} config - Configuración del CRUD
     * @param {string} config.endpoint - Endpoint base del API (ej: '/api/products')
     * @param {string} config.formPath - Ruta del componente formulario (ej: '/component/products-crud/product-form')
     * @param {string} config.tableId - ID de la tabla asociada
     * @param {string} config.entityName - Nombre de la entidad (ej: 'Producto')
     * @param {string} config.prefix - Prefijo para funciones globales (default: entityName en minúsculas)
     * @param {string} config.modalWidth - Ancho del modal (default: '700px')
     * @param {Function} config.onBeforeCreate - Hook antes de crear
     * @param {Function} config.onAfterCreate - Hook después de crear
     * @param {Function} config.onBeforeEdit - Hook antes de editar
     * @param {Function} config.onAfterEdit - Hook después de editar
     * @param {Function} config.onBeforeDelete - Hook antes de eliminar
     * @param {Function} config.onAfterDelete - Hook después de eliminar
     * @param {Function} config.onReload - Hook customizado para recargar tabla
     * @param {Function} config.transformData - Transformar datos antes de enviar
     * @param {Function} config.formatTableData - Formatear datos de la tabla
     */
    constructor(config) {
        this.validateConfig(config);

        this.config = {
            modalWidth: '700px',
            prefix: null,
            ...config
        };

        // Crear cliente REST
        this.api = new RestClient(this.config.endpoint);

        console.log('[CrudManager] Inicializado para:', this.config.entityName);
    }

    /**
     * Validar configuración requerida
     */
    validateConfig(config) {
        const required = ['endpoint', 'formPath', 'tableId', 'entityName'];
        const missing = required.filter(key => !config[key]);

        if (missing.length > 0) {
            throw new Error(`[CrudManager] Configuración incompleta. Faltan: ${missing.join(', ')}`);
        }
    }

    /**
     * Crear nuevo registro
     */
    async create() {
        console.log(`[CrudManager] Iniciando creación de ${this.config.entityName}`);

        // Hook antes de abrir formulario
        if (this.config.onBeforeCreate) {
            const shouldContinue = await this.config.onBeforeCreate();
            if (shouldContinue === false) {
                console.log('[CrudManager] Creación cancelada por onBeforeCreate');
                return;
            }
        }

        // Cargar formulario
        const result = await AlertService.componentModal(this.config.formPath, {
            title: `➕ Nuevo ${this.config.entityName}`,
            confirmButtonText: 'Crear',
            cancelButtonText: 'Cancelar',
            width: this.config.modalWidth
        });

        if (result.isConfirmed && result.value) {
            await this.save('create', result.value);
        }
    }

    /**
     * Editar registro existente
     * @param {number|string} id - ID del registro
     */
    async edit(id) {
        console.log(`[CrudManager] Iniciando edición de ${this.config.entityName} #${id}`);

        // Hook antes de abrir formulario
        if (this.config.onBeforeEdit) {
            const shouldContinue = await this.config.onBeforeEdit(id);
            if (shouldContinue === false) {
                console.log('[CrudManager] Edición cancelada por onBeforeEdit');
                return;
            }
        }

        // Cargar formulario con datos
        const result = await AlertService.componentModal(this.config.formPath, {
            title: `✏️ Editar ${this.config.entityName} #${id}`,
            confirmButtonText: 'Guardar Cambios',
            cancelButtonText: 'Cancelar',
            width: this.config.modalWidth,
            params: { id }
        });

        if (result.isConfirmed && result.value) {
            const data = { id, ...result.value };
            await this.save('update', data);
        }
    }

    /**
     * Eliminar registro
     * @param {number|string} id - ID del registro
     */
    async delete(id) {
        console.log(`[CrudManager] Iniciando eliminación de ${this.config.entityName} #${id}`);

        // Hook antes de confirmar
        if (this.config.onBeforeDelete) {
            const shouldContinue = await this.config.onBeforeDelete(id);
            if (shouldContinue === false) {
                console.log('[CrudManager] Eliminación cancelada por onBeforeDelete');
                return;
            }
        }

        // Confirmar eliminación
        const confirmed = await AlertService.confirmDelete(`${this.config.entityName} #${id}`);

        if (confirmed) {
            await this.save('delete', { id });
        }
    }

    /**
     * Guardar datos (create/update/delete)
     * @param {string} action - 'create', 'update', o 'delete'
     * @param {Object} data - Datos a enviar
     */
    async save(action, data) {
        const loading = AlertService.loading(this.getLoadingMessage(action));

        try {
            // Transformar datos si hay hook
            if (this.config.transformData) {
                data = this.config.transformData(data, action);
            }

            // Realizar petición según la acción
            let result;
            if (action === 'create') {
                result = await this.api.create(data);
            } else if (action === 'update') {
                result = await this.api.update(data);
            } else if (action === 'delete') {
                result = await this.api.delete(data.id);
            }

            loading();

            // Verificar éxito
            if (result.success) {
                // Recargar tabla PRIMERO (para feedback inmediato)
                await this.reloadTable();

                // Mostrar mensaje de éxito
                AlertService.success(result.message || this.getSuccessMessage(action));

                // Hook después de la operación
                const hookName = `onAfter${this.capitalize(action)}`;
                if (this.config[hookName]) {
                    await this.config[hookName](result.data, result);
                }

                console.log(`[CrudManager] ${action} completado exitosamente`);
            } else {
                AlertService.error(result.message || 'Error en la operación');
            }

        } catch (error) {
            loading();
            console.error(`[CrudManager] Error en ${action}:`, error);

            // Mensaje de error más descriptivo
            let errorMessage = 'Error de conexión';
            if (error.message) {
                errorMessage = error.message;
            }
            if (error.status === 401) {
                errorMessage = 'No autorizado. Por favor inicia sesión.';
            } else if (error.status === 403) {
                errorMessage = 'No tienes permisos para realizar esta acción.';
            } else if (error.status === 404) {
                errorMessage = 'Recurso no encontrado.';
            } else if (error.status === 422) {
                errorMessage = 'Error de validación: ' + (error.response?.message || 'Datos inválidos');
            } else if (error.status >= 500) {
                errorMessage = 'Error del servidor. Por favor intenta más tarde.';
            }

            AlertService.error(errorMessage);
        }
    }

    /**
     * Recargar datos de la tabla
     */
    async reloadTable() {
        console.log(`[CrudManager] Recargando tabla: ${this.config.tableId}`);

        // Si hay hook personalizado, usarlo
        if (this.config.onReload) {
            const table = await TableHelper.waitForTable(this.config.tableId);
            await this.config.onReload(table);
            return;
        }

        // Reload por defecto: obtener lista y actualizar tabla
        try {
            const response = await this.api.list();

            if (response.success && response.data) {
                let data = response.data;

                // Formatear datos si hay hook
                if (this.config.formatTableData) {
                    data = this.config.formatTableData(data);
                }

                await TableHelper.setTableData(this.config.tableId, data);
            }
        } catch (error) {
            console.error('[CrudManager] Error al recargar tabla:', error);
        }
    }

    /**
     * Obtener mensaje de loading según acción
     */
    getLoadingMessage(action) {
        const messages = {
            create: 'Creando...',
            update: 'Actualizando...',
            delete: 'Eliminando...'
        };
        return messages[action] || 'Procesando...';
    }

    /**
     * Obtener mensaje de éxito según acción
     */
    getSuccessMessage(action) {
        const messages = {
            create: `${this.config.entityName} creado correctamente`,
            update: `${this.config.entityName} actualizado correctamente`,
            delete: `${this.config.entityName} eliminado correctamente`
        };
        return messages[action];
    }

    /**
     * Capitalizar primera letra
     */
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Exponer métodos CRUD como funciones globales
     * Crea: window.createX(), window.editX(id), window.deleteX(id)
     * @returns {CrudManager} this para encadenar
     */
    expose() {
        const prefix = this.config.prefix || this.config.entityName.toLowerCase();
        const capitalizedPrefix = this.capitalize(prefix);

        // Crear funciones globales
        window[`create${capitalizedPrefix}`] = () => this.create();
        window[`edit${capitalizedPrefix}`] = (id) => this.edit(id);
        window[`delete${capitalizedPrefix}`] = (id) => this.delete(id);

        console.log(`[CrudManager] Funciones expuestas:`);
        console.log(`  - window.create${capitalizedPrefix}()`);
        console.log(`  - window.edit${capitalizedPrefix}(id)`);
        console.log(`  - window.delete${capitalizedPrefix}(id)`);

        return this;
    }

    /**
     * Cargar datos iniciales en la tabla
     */
    async loadInitialData() {
        console.log(`[CrudManager] Cargando datos iniciales...`);
        await this.reloadTable();
    }
}

// Exportar
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CrudManager;
}

window.CrudManager = CrudManager;
