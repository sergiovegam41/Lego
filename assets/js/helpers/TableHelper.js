/**
 * TableHelper - Utilidades para trabajar con TableComponent
 *
 * FILOSOFÍA LEGO:
 * Proporciona métodos para interactuar con tablas de manera robusta,
 * eliminando race conditions y timing issues.
 *
 * PROBLEMA QUE RESUELVE:
 * - TableComponent se inicializa asíncronamente (carga AG Grid desde CDN)
 * - El código de implementación no sabe cuándo está lista la tabla
 * - Usar setTimeout() es frágil y arbitrario
 *
 * SOLUCIÓN:
 * - Sistema de eventos y Promises
 * - waitForTable() retorna Promise que se resuelve cuando la tabla está lista
 * - Funciona incluso si la tabla ya estaba inicializada
 *
 * EJEMPLO DE USO:
 * ```javascript
 * // Esperar a que la tabla esté lista
 * const table = await TableHelper.waitForTable('my-table-id');
 * console.log('Tabla lista!', table.api);
 *
 * // Cargar datos
 * table.api.setGridOption('rowData', myData);
 *
 * // Obtener filas seleccionadas
 * const selected = table.api.getSelectedRows();
 * ```
 */

class TableHelper {
    /**
     * Esperar a que una tabla esté completamente inicializada
     * @param {string} tableId - ID de la tabla
     * @param {number} timeout - Timeout en ms (default: 10000)
     * @returns {Promise<Object>} Objeto con api y columnApi
     */
    static async waitForTable(tableId, timeout = 10000) {
        console.log(`[TableHelper] Esperando tabla: ${tableId}`);

        // Inicializar registry si no existe
        if (!window.LEGO_TABLES) {
            window.LEGO_TABLES = {};
        }

        // Si la tabla ya existe, devolver inmediatamente
        if (window.LEGO_TABLES[tableId]?.api) {
            console.log(`[TableHelper] Tabla ${tableId} ya disponible`);
            return window.LEGO_TABLES[tableId];
        }

        // Si ya hay una Promise esperando, unirse a ella
        if (window.LEGO_TABLES[tableId]?.promise) {
            console.log(`[TableHelper] Uniéndose a Promise existente para ${tableId}`);
            return window.LEGO_TABLES[tableId].promise;
        }

        // Crear nueva Promise
        const promise = new Promise((resolve, reject) => {
            let resolved = false;

            // Timeout
            const timeoutId = setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    console.error(`[TableHelper] Timeout esperando tabla: ${tableId}`);
                    reject(new Error(`Timeout waiting for table: ${tableId}`));
                }
            }, timeout);

            // Event listener
            const handler = (event) => {
                if (event.detail.tableId === tableId && !resolved) {
                    resolved = true;
                    clearTimeout(timeoutId);
                    window.removeEventListener('lego:table:ready', handler);

                    console.log(`[TableHelper] Tabla ${tableId} lista!`);
                    resolve(event.detail);
                }
            };

            window.addEventListener('lego:table:ready', handler);

            // Verificar de nuevo por si acaso se inicializó mientras configurábamos el listener
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
     * Verificar si una tabla está inicializada
     * @param {string} tableId - ID de la tabla
     * @returns {boolean}
     */
    static isTableReady(tableId) {
        return !!(window.LEGO_TABLES && window.LEGO_TABLES[tableId]?.api);
    }

    /**
     * Obtener API de tabla si está disponible (síncrono)
     * @param {string} tableId - ID de la tabla
     * @returns {Object|null} API o null si no está lista
     */
    static getTableApi(tableId) {
        return window.LEGO_TABLES?.[tableId]?.api || null;
    }

    /**
     * Actualizar datos de una tabla
     * @param {string} tableId - ID de la tabla
     * @param {Array} data - Datos a cargar
     */
    static async setTableData(tableId, data) {
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('rowData', data);
        console.log(`[TableHelper] Datos actualizados en ${tableId}:`, data.length, 'filas');
    }

    /**
     * Obtener filas seleccionadas de una tabla
     * @param {string} tableId - ID de la tabla
     * @returns {Array} Filas seleccionadas
     */
    static async getSelectedRows(tableId) {
        const table = await this.waitForTable(tableId);
        return table.api.getSelectedRows();
    }

    /**
     * Refrescar tabla (disparar evento para que el componente recargue datos)
     * @param {string} tableId - ID de la tabla
     */
    static refresh(tableId) {
        console.log(`[TableHelper] Disparando evento de refresh para ${tableId}`);
        window.dispatchEvent(new CustomEvent('lego:table:refresh', {
            detail: { tableId }
        }));
    }

    /**
     * Limpiar tabla
     * @param {string} tableId - ID de la tabla
     */
    static async clearTable(tableId) {
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('rowData', []);
        console.log(`[TableHelper] Tabla ${tableId} limpiada`);
    }

    /**
     * Aplicar filtro rápido a tabla
     * @param {string} tableId - ID de la tabla
     * @param {string} filterText - Texto a filtrar
     */
    static async quickFilter(tableId, filterText) {
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('quickFilterText', filterText);
    }
}

// Exportar
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TableHelper;
}

window.TableHelper = TableHelper;
