/**
 * TableManager - Abstracción modular de AG Grid
 *
 * FILOSOFÍA LEGO:
 * Abstrae la complejidad de AG Grid en una interfaz simple y agnóstica.
 * Funciona con cualquier tabla en cualquier página sin conocer detalles internos.
 *
 * DOS MODOS DE USO:
 *
 * 1. MODO INSTANCIA (recomendado para componentes):
 *    const tableManager = new TableManager('products-table');
 *    tableManager.onReady(() => {
 *        tableManager.setData(products);
 *    });
 *
 * 2. MODO ESTÁTICO (para scripts rápidos):
 *    const table = await TableManager.waitForTable('products-table');
 *    TableManager.setTableData('products-table', products);
 */

class TableManager {
    constructor(tableId) {
        this.tableId = tableId;
        this.jsId = this._sanitizeId(tableId);
        this.api = null;
        this.columnApi = null;
        this.isReady = false;
        this.eventListeners = {};
        this.readyCallbacks = [];

        // Acceder a la API de la tabla global
        this._initializeTableApi();

        // Si la tabla ya está lista (solo verificar api, columnApi es opcional)
        if (this.api) {
            this.isReady = true;
            this._triggerReadyCallbacks();
        } else {
            // Esperar a que la tabla se inicialice
            this._waitForTableReady();
        }
    }

    /**
     * Sanitizar ID de tabla para generar jsId válido
     */
    _sanitizeId(id) {
        return id.replace(/[^a-zA-Z0-9_]/g, '_');
    }

    /**
     * Obtener referencias a la API global de la tabla
     */
    _initializeTableApi() {
        const jsId = this.jsId;
        const varName = `legoTable_${jsId}_api`;
        const globalApi = window[varName];
        const globalColumnApi = window[`legoTable_${jsId}_columnApi`];

        // Solo necesitamos api (columnApi está deprecated en AG Grid moderno)
        if (globalApi) {
            this.api = globalApi;
            this.columnApi = globalColumnApi || null;
        }
    }

    /**
     * Esperar a que la tabla esté lista via evento
     */
    _waitForTableReady() {
        const checkInterval = setInterval(() => {
            this._initializeTableApi();
            // Solo verificar api (columnApi es opcional en AG Grid moderno)
            if (this.api) {
                this.isReady = true;
                clearInterval(checkInterval);
                this._triggerReadyCallbacks();
            }
        }, 100);

        // También escuchar evento de tabla lista (método preferido)
        window.addEventListener('lego:table:ready', (event) => {
            if (event.detail.tableId === this.tableId) {
                this.api = event.detail.api;
                this.columnApi = event.detail.columnApi;
                this.isReady = true;
                clearInterval(checkInterval);
                this._triggerReadyCallbacks();
            }
        });
    }

    /**
     * Registrar callback para cuando la tabla esté lista
     */
    onReady(callback) {
        if (this.isReady) {
            callback();
        } else {
            this.readyCallbacks.push(callback);
        }
    }

    /**
     * Ejecutar callbacks de tabla lista
     */
    _triggerReadyCallbacks() {
        this.readyCallbacks.forEach(cb => {
            try {
                cb();
            } catch (error) {
                console.error(`[TableManager] Error en callback onReady:`, error);
            }
        });
        this.readyCallbacks = [];
    }

    /**
     * Actualizar datos de la tabla
     */
    setData(rowData) {
        if (!this.api) {
            console.error(`[TableManager] API no disponible para tabla ${this.tableId}`);
            return false;
        }

        try {
            this.api.setGridOption('rowData', rowData);
            console.log(`[TableManager] Datos actualizados (${rowData.length} registros)`);
            return true;
        } catch (error) {
            console.error(`[TableManager] Error al actualizar datos:`, error);
            return false;
        }
    }

    /**
     * Obtener datos actuales de la tabla
     */
    getData() {
        if (!this.api) return [];
        const data = [];
        this.api.forEachNode((node) => {
            data.push(node.data);
        });
        return data;
    }

    /**
     * Actualizar contador de registros
     */
    updateRowCount() {
        const updateFunction = window[`legoTable_${this.jsId}_updateRowCount`];
        if (updateFunction && typeof updateFunction === 'function') {
            updateFunction();
            return true;
        }
        return false;
    }

    /**
     * Obtener filas seleccionadas
     */
    getSelectedRows() {
        if (!this.api) return [];
        const getSelectedFunction = window[`legoTable_${this.jsId}_getSelectedRows`];
        if (getSelectedFunction && typeof getSelectedFunction === 'function') {
            return getSelectedFunction();
        }
        return [];
    }

    /**
     * Deseleccionar todas las filas
     */
    deselectAll() {
        const deselectFunction = window[`legoTable_${this.jsId}_deselectAll`];
        if (deselectFunction && typeof deselectFunction === 'function') {
            deselectFunction();
            return true;
        }
        return false;
    }

    /**
     * Actualizar columnas (cellRenderers, etc)
     */
    setColumnDefs(columnDefs) {
        if (!this.api) {
            console.error(`[TableManager] API no disponible para tabla ${this.tableId}`);
            return false;
        }

        try {
            this.api.setGridOption('columnDefs', columnDefs);
            console.log(`[TableManager] Columnas actualizadas`);
            return true;
        } catch (error) {
            console.error(`[TableManager] Error al actualizar columnas:`, error);
            return false;
        }
    }

    /**
     * Exportar datos como CSV
     */
    exportToCSV(fileName = 'export') {
        const exportFunction = window[`legoTable_${this.jsId}_exportCSV`];
        if (exportFunction && typeof exportFunction === 'function') {
            console.log(`[TableManager] Exportando CSV: ${fileName}`);
            exportFunction();
            return true;
        }
        return false;
    }

    /**
     * Mostrar/ocultar loader
     */
    setLoading(show = true) {
        const loaderFunction = window[`legoTable_${this.jsId}_showLoader`];
        if (loaderFunction && typeof loaderFunction === 'function') {
            loaderFunction(show);
            return true;
        }
        return false;
    }

    /**
     * Sistema de eventos personalizado
     */
    on(eventName, callback) {
        if (!this.eventListeners[eventName]) {
            this.eventListeners[eventName] = [];
        }
        this.eventListeners[eventName].push(callback);
    }

    /**
     * Desuscribirse de evento
     */
    off(eventName, callback) {
        if (!this.eventListeners[eventName]) return;
        const index = this.eventListeners[eventName].indexOf(callback);
        if (index > -1) {
            this.eventListeners[eventName].splice(index, 1);
        }
    }

    /**
     * Disparar evento
     */
    emit(eventName, data) {
        if (!this.eventListeners[eventName]) return;
        this.eventListeners[eventName].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`[TableManager] Error en listener ${eventName}:`, error);
            }
        });
    }

    /**
     * Esperar a que la tabla esté lista (Promise)
     */
    ready() {
        return new Promise((resolve) => {
            this.onReady(() => resolve(this));
        });
    }

    /**
     * Autoajustar columnas al contenido
     */
    autoSizeColumns() {
        if (!this.api) return false;
        try {
            this.api.sizeColumnsToFit();
            console.log(`[TableManager] Columnas autoajustadas`);
            return true;
        } catch (error) {
            console.error(`[TableManager] Error al autoajustar columnas:`, error);
            return false;
        }
    }

    /**
     * Obtener API directa si se necesita algo no soportado
     */
    getAPI() {
        return this.api;
    }

    /**
     * Obtener Column API directa si se necesita algo no soportado
     */
    getColumnAPI() {
        return this.columnApi;
    }

    // ═══════════════════════════════════════════════════════════════════
    // MÉTODOS ESTÁTICOS
    // Permiten uso sin instanciar: TableManager.waitForTable('id')
    // ═══════════════════════════════════════════════════════════════════

    /**
     * [ESTÁTICO] Esperar a que una tabla esté completamente inicializada
     * @param {string} tableId - ID de la tabla
     * @param {number} timeout - Timeout en ms (default: 10000)
     * @returns {Promise<Object>} Objeto con api y columnApi
     */
    static async waitForTable(tableId, timeout = 10000) {
        console.log(`[TableManager] Esperando tabla: ${tableId}`);

        // Inicializar registry si no existe
        if (!window.LEGO_TABLES) {
            window.LEGO_TABLES = {};
        }

        // Si la tabla ya existe, devolver inmediatamente
        if (window.LEGO_TABLES[tableId]?.api) {
            console.log(`[TableManager] Tabla ${tableId} ya disponible`);
            return window.LEGO_TABLES[tableId];
        }

        // Si ya hay una Promise esperando, unirse a ella
        if (window.LEGO_TABLES[tableId]?.promise) {
            console.log(`[TableManager] Uniéndose a Promise existente para ${tableId}`);
            return window.LEGO_TABLES[tableId].promise;
        }

        // Crear nueva Promise
        const promise = new Promise((resolve, reject) => {
            let resolved = false;

            // Timeout
            const timeoutId = setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    console.error(`[TableManager] Timeout esperando tabla: ${tableId}`);
                    reject(new Error(`Timeout waiting for table: ${tableId}`));
                }
            }, timeout);

            // Event listener
            const handler = (event) => {
                if (event.detail.tableId === tableId && !resolved) {
                    resolved = true;
                    clearTimeout(timeoutId);
                    window.removeEventListener('lego:table:ready', handler);
                    console.log(`[TableManager] Tabla ${tableId} lista!`);
                    resolve(event.detail);
                }
            };

            window.addEventListener('lego:table:ready', handler);

            // Verificar de nuevo por si acaso
            if (window.LEGO_TABLES[tableId]?.api && !resolved) {
                resolved = true;
                clearTimeout(timeoutId);
                window.removeEventListener('lego:table:ready', handler);
                resolve(window.LEGO_TABLES[tableId]);
            }
        });

        // Guardar la promise para que otros puedan unirse
        if (!window.LEGO_TABLES[tableId]) {
            window.LEGO_TABLES[tableId] = {};
        }
        window.LEGO_TABLES[tableId].promise = promise;

        return promise;
    }

    /**
     * [ESTÁTICO] Verificar si una tabla está inicializada
     * @param {string} tableId - ID de la tabla
     * @returns {boolean}
     */
    static isTableReady(tableId) {
        return !!(window.LEGO_TABLES && window.LEGO_TABLES[tableId]?.api);
    }

    /**
     * [ESTÁTICO] Obtener API de tabla si está disponible (síncrono)
     * @param {string} tableId - ID de la tabla
     * @returns {Object|null} API o null si no está lista
     */
    static getTableApi(tableId) {
        return window.LEGO_TABLES?.[tableId]?.api || null;
    }

    /**
     * [ESTÁTICO] Actualizar datos de una tabla
     * @param {string} tableId - ID de la tabla
     * @param {Array} data - Datos a cargar
     */
    static async setTableData(tableId, data) {
        const table = await TableManager.waitForTable(tableId);
        table.api.setGridOption('rowData', data);
        console.log(`[TableManager] Datos actualizados en ${tableId}:`, data.length, 'filas');
    }

    /**
     * [ESTÁTICO] Obtener filas seleccionadas de una tabla
     * @param {string} tableId - ID de la tabla
     * @returns {Array} Filas seleccionadas
     */
    static async getSelectedRowsStatic(tableId) {
        const table = await TableManager.waitForTable(tableId);
        return table.api.getSelectedRows();
    }

    /**
     * [ESTÁTICO] Refrescar tabla (disparar evento)
     * @param {string} tableId - ID de la tabla
     */
    static refresh(tableId) {
        console.log(`[TableManager] Disparando evento de refresh para ${tableId}`);
        window.dispatchEvent(new CustomEvent('lego:table:refresh', {
            detail: { tableId }
        }));
    }

    /**
     * [ESTÁTICO] Limpiar tabla
     * @param {string} tableId - ID de la tabla
     */
    static async clearTable(tableId) {
        const table = await TableManager.waitForTable(tableId);
        table.api.setGridOption('rowData', []);
        console.log(`[TableManager] Tabla ${tableId} limpiada`);
    }

    /**
     * [ESTÁTICO] Aplicar filtro rápido a tabla
     * @param {string} tableId - ID de la tabla
     * @param {string} filterText - Texto a filtrar
     */
    static async quickFilter(tableId, filterText) {
        const table = await TableManager.waitForTable(tableId);
        table.api.setGridOption('quickFilterText', filterText);
    }
}

// Exponer globalmente
window.TableManager = TableManager;
