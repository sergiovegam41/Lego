/**
 * @deprecated DEPRECATED - Usar TableManager en su lugar
 *
 * TableHelper ha sido consolidado en TableManager.
 * Este archivo se mantiene por compatibilidad pero será eliminado en futuras versiones.
 *
 * MIGRACIÓN:
 * - TableHelper.waitForTable(id) → TableManager.waitForTable(id)
 * - TableHelper.setTableData(id, data) → TableManager.setTableData(id, data)
 * - TableHelper.refresh(id) → TableManager.refresh(id)
 *
 * O usar modo instancia:
 * const table = new TableManager('my-table');
 * table.onReady(() => table.setData(data));
 */

class TableHelper {
    /**
     * @deprecated Usar TableManager.waitForTable()
     */
    static async waitForTable(tableId, timeout = 10000) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.waitForTable() en su lugar');
        if (window.TableManager) {
            return TableManager.waitForTable(tableId, timeout);
        }
        // Fallback si TableManager no está cargado
        return this._legacyWaitForTable(tableId, timeout);
    }

    /**
     * @deprecated Usar TableManager.isTableReady()
     */
    static isTableReady(tableId) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.isTableReady() en su lugar');
        if (window.TableManager) {
            return TableManager.isTableReady(tableId);
        }
        return !!(window.LEGO_TABLES && window.LEGO_TABLES[tableId]?.api);
    }

    /**
     * @deprecated Usar TableManager.getTableApi()
     */
    static getTableApi(tableId) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.getTableApi() en su lugar');
        if (window.TableManager) {
            return TableManager.getTableApi(tableId);
        }
        return window.LEGO_TABLES?.[tableId]?.api || null;
    }

    /**
     * @deprecated Usar TableManager.setTableData()
     */
    static async setTableData(tableId, data) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.setTableData() en su lugar');
        if (window.TableManager) {
            return TableManager.setTableData(tableId, data);
        }
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('rowData', data);
    }

    /**
     * @deprecated Usar TableManager.getSelectedRowsStatic()
     */
    static async getSelectedRows(tableId) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.getSelectedRowsStatic() en su lugar');
        if (window.TableManager) {
            return TableManager.getSelectedRowsStatic(tableId);
        }
        const table = await this.waitForTable(tableId);
        return table.api.getSelectedRows();
    }

    /**
     * @deprecated Usar TableManager.refresh()
     */
    static refresh(tableId) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.refresh() en su lugar');
        if (window.TableManager) {
            return TableManager.refresh(tableId);
        }
        window.dispatchEvent(new CustomEvent('lego:table:refresh', {
            detail: { tableId }
        }));
    }

    /**
     * @deprecated Usar TableManager.clearTable()
     */
    static async clearTable(tableId) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.clearTable() en su lugar');
        if (window.TableManager) {
            return TableManager.clearTable(tableId);
        }
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('rowData', []);
    }

    /**
     * @deprecated Usar TableManager.quickFilter()
     */
    static async quickFilter(tableId, filterText) {
        console.warn('[TableHelper] DEPRECATED: Usar TableManager.quickFilter() en su lugar');
        if (window.TableManager) {
            return TableManager.quickFilter(tableId, filterText);
        }
        const table = await this.waitForTable(tableId);
        table.api.setGridOption('quickFilterText', filterText);
    }

    /**
     * Implementación legacy para fallback
     * @private
     */
    static async _legacyWaitForTable(tableId, timeout = 10000) {
        if (!window.LEGO_TABLES) {
            window.LEGO_TABLES = {};
        }

        if (window.LEGO_TABLES[tableId]?.api) {
            return window.LEGO_TABLES[tableId];
        }

        return new Promise((resolve, reject) => {
            let resolved = false;

            const timeoutId = setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    reject(new Error(`Timeout waiting for table: ${tableId}`));
                }
            }, timeout);

            const handler = (event) => {
                if (event.detail.tableId === tableId && !resolved) {
                    resolved = true;
                    clearTimeout(timeoutId);
                    window.removeEventListener('lego:table:ready', handler);
                    resolve(event.detail);
                }
            };

            window.addEventListener('lego:table:ready', handler);

            if (window.LEGO_TABLES[tableId]?.api && !resolved) {
                resolved = true;
                clearTimeout(timeoutId);
                window.removeEventListener('lego:table:ready', handler);
                resolve(window.LEGO_TABLES[tableId]);
            }
        });
    }
}

// Exportar
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TableHelper;
}

window.TableHelper = TableHelper;
