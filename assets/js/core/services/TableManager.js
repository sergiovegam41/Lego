/**
 * TableManager - Abstração modular de AG Grid
 *
 * FILOSOFÍA LEGO:
 * Abstrae la complejidad de AG Grid en una interfaz simple y agnóstica.
 * Funciona con cualquier tabla en cualquier página sin conocer detalles internos.
 *
 * USO:
 * const tableManager = new TableManager('products-crud-table');
 * tableManager.onReady(() => {
 *     tableManager.setData(products);
 *     tableManager.updateRowCount();
 * });
 * tableManager.on('action:edit', (rowId) => { ... });
 * tableManager.on('action:delete', (rowId) => { ... });
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

        // Si la tabla ya está lista
        if (this.api && this.columnApi) {
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
        const globalApi = window[`legoTable_${jsId}_api`];
        const globalColumnApi = window[`legoTable_${jsId}_columnApi`];

        if (globalApi && globalColumnApi) {
            this.api = globalApi;
            this.columnApi = globalColumnApi;
            console.log(`[TableManager] API de tabla encontrada: ${this.tableId}`);
        }
    }

    /**
     * Esperar a que la tabla esté lista via evento
     */
    _waitForTableReady() {
        const checkInterval = setInterval(() => {
            this._initializeTableApi();
            if (this.api && this.columnApi) {
                this.isReady = true;
                clearInterval(checkInterval);
                console.log(`[TableManager] Tabla ${this.tableId} lista`);
                this._triggerReadyCallbacks();
            }
        }, 100);

        // También escuchar evento de tabla lista
        window.addEventListener('lego:table:ready', (event) => {
            if (event.detail.tableId === this.tableId) {
                this.api = event.detail.api;
                this.columnApi = event.detail.columnApi;
                this.isReady = true;
                clearInterval(checkInterval);
                console.log(`[TableManager] Tabla ${this.tableId} lista (evento)`);
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
}
