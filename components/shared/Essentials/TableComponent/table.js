/**
 * TableComponent JavaScript
 *
 * FILOSOFÍA LEGO:
 * Inicializa y gestiona instancias de AG Grid de forma declarativa.
 * Carga AG Grid desde CDN y configura todas las opciones recibidas desde PHP.
 */

// Recibir configuración desde PHP
let context = {CONTEXT};

(function() {
    if (!context || !context.arg) {
        console.error('[LEGO Table] No se recibió configuración desde PHP');
        return;
    }

    const config = context.arg;
    const {
        id,
        jsId,
        columnDefs,
        rowData,
        gridOptions,
        callbacks
    } = config;

    console.log('[LEGO Table] Inicializando tabla:', id);

    // Cargar AG Grid desde CDN si no está cargado
    function loadAGGrid() {
        return new Promise((resolve, reject) => {
            // Verificar si AG Grid ya está cargado
            if (window.agGrid) {
                console.log('[LEGO Table] AG Grid ya está cargado');
                resolve();
                return;
            }

            console.log('[LEGO Table] Cargando AG Grid desde CDN...');

            // Cargar CSS de AG Grid
            const cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = 'https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-grid.css';
            document.head.appendChild(cssLink);

            // Cargar tema Quartz (default)
            const themeLink = document.createElement('link');
            themeLink.rel = 'stylesheet';
            themeLink.href = 'https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-theme-quartz.css';
            document.head.appendChild(themeLink);

            // Cargar JavaScript de AG Grid
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/dist/ag-grid-community.min.js';
            script.onload = () => {
                console.log('[LEGO Table] AG Grid cargado exitosamente');
                resolve();
            };
            script.onerror = (error) => {
                console.error('[LEGO Table] Error al cargar AG Grid:', error);
                reject(error);
            };
            document.head.appendChild(script);
        });
    }

    // Inicializar la tabla
    function initTable() {
        const gridDiv = document.getElementById(id);
        if (!gridDiv) {
            console.error(`[LEGO Table] No se encontró el elemento con ID: ${id}`);
            return;
        }

        // Configuración completa de AG Grid
        const fullGridOptions = {
            columnDefs: columnDefs,
            rowData: rowData,
            ...gridOptions,

            // Localización en español
            localeText: {
                // Paginación
                page: 'Página',
                of: 'de',
                to: 'a',
                more: 'Más',
                next: 'Siguiente',
                last: 'Último',
                first: 'Primero',
                previous: 'Anterior',

                // Filtros
                filterOoo: 'Filtrar...',
                equals: 'Igual a',
                notEqual: 'No igual a',
                lessThan: 'Menor que',
                greaterThan: 'Mayor que',
                lessThanOrEqual: 'Menor o igual que',
                greaterThanOrEqual: 'Mayor o igual que',
                inRange: 'En rango',
                contains: 'Contiene',
                notContains: 'No contiene',
                startsWith: 'Comienza con',
                endsWith: 'Termina con',
                blank: 'En blanco',
                notBlank: 'No en blanco',
                andCondition: 'Y',
                orCondition: 'O',
                applyFilter: 'Aplicar',
                resetFilter: 'Limpiar',
                clearFilter: 'Limpiar',

                // General
                loadingOoo: 'Cargando...',
                noRowsToShow: 'No hay datos disponibles',
                enabled: 'Habilitado',
                disabled: 'Deshabilitado',

                // Menú de columnas
                pinColumn: 'Fijar columna',
                pinLeft: 'Fijar izquierda',
                pinRight: 'Fijar derecha',
                noPin: 'No fijar',
                autosizeThiscolumn: 'Autoajustar columna',
                autosizeAllColumns: 'Autoajustar todas',
                resetColumns: 'Restablecer columnas',

                // Exportación
                export: 'Exportar',
                csvExport: 'Exportar CSV',
                excelExport: 'Exportar Excel'
            },

            // Callbacks personalizados
            onSelectionChanged: callbacks.onSelectionChanged ?
                (event) => window[callbacks.onSelectionChanged]?.(event) : undefined,

            onCellValueChanged: callbacks.onCellValueChanged ?
                (event) => window[callbacks.onCellValueChanged]?.(event) : undefined,

            onRowClicked: callbacks.onRowClicked ?
                (event) => window[callbacks.onRowClicked]?.(event) : undefined,

            onRowDoubleClicked: callbacks.onRowDoubleClicked ?
                (event) => window[callbacks.onRowDoubleClicked]?.(event) : undefined,

            onCellClicked: callbacks.onCellClicked ?
                (event) => window[callbacks.onCellClicked]?.(event) : undefined,

            onGridReady: (event) => {
                console.log('[LEGO Table] Grid listo:', id);

                // Guardar referencia del grid API globalmente usando jsId sanitizado
                window[`legoTable_${jsId}_api`] = event.api;
                window[`legoTable_${jsId}_columnApi`] = event.columnApi;

                // Callback personalizado onGridReady
                if (callbacks.onGridReady) {
                    window[callbacks.onGridReady]?.(event);
                }

                // Autoajustar columnas si es necesario
                event.api.sizeColumnsToFit();
            }
        };

        // Crear la grid
        const gridApi = agGrid.createGrid(gridDiv, fullGridOptions);

        console.log('[LEGO Table] Grid inicializada exitosamente:', id);

        // Funciones de exportación globales usando jsId sanitizado
        window[`legoTable_${jsId}_exportCSV`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            if (api) {
                api.exportDataAsCsv({
                    fileName: config.exportFileName || 'export'
                });
                console.log('[LEGO Table] Exportando a CSV...');
            }
        };

        window[`legoTable_${jsId}_exportExcel`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            if (api) {
                // Nota: Excel export requiere AG Grid Enterprise
                if (api.exportDataAsExcel) {
                    api.exportDataAsExcel({
                        fileName: config.exportFileName || 'export'
                    });
                    console.log('[LEGO Table] Exportando a Excel...');
                } else {
                    console.warn('[LEGO Table] Excel export requiere AG Grid Enterprise. Exportando como CSV...');
                    api.exportDataAsCsv({
                        fileName: config.exportFileName || 'export'
                    });
                }
            }
        };

        // Función para actualizar datos dinámicamente
        window[`legoTable_${jsId}_updateData`] = function(newRowData) {
            const api = window[`legoTable_${jsId}_api`];
            if (api) {
                api.setGridOption('rowData', newRowData);
                console.log('[LEGO Table] Datos actualizados:', id);
            }
        };

        // Función para obtener filas seleccionadas
        window[`legoTable_${jsId}_getSelectedRows`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            return api ? api.getSelectedRows() : [];
        };

        // Función para limpiar selección
        window[`legoTable_${jsId}_deselectAll`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            if (api) {
                api.deselectAll();
            }
        };

        // Función para mostrar/ocultar loader
        window[`legoTable_${jsId}_showLoader`] = function(show = true) {
            const loader = document.getElementById(`${id}-loader`);
            if (loader) {
                loader.style.display = show ? 'flex' : 'none';
            }
        };

        // Función para actualizar contador de filas
        window[`legoTable_${jsId}_updateRowCount`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            const countElement = document.getElementById(`${id}-row-count`);
            if (api && countElement) {
                const count = api.getDisplayedRowCount();
                countElement.textContent = count === 1 ? '1 registro' : `${count} registros`;
            }
        };
    }

    // Ejecutar cuando AG Grid esté cargado
    loadAGGrid()
        .then(() => {
            // Inicializar inmediatamente si el DOM está listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initTable);
            } else {
                initTable();
            }
        })
        .catch((error) => {
            console.error('[LEGO Table] Error al inicializar:', error);
        });

})();
