/**
 * TableComponent JavaScript
 *
 * FILOSOFÍA LEGO:
 * Inicializa y gestiona instancias de AG Grid de forma declarativa.
 * Carga AG Grid desde CDN y configura todas las opciones recibidas desde PHP.
 */

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

    // Procesar columnDefs para convertir código de formatters a funciones
    function processColumnDefs(columns) {
        return columns.map(col => {
            const processedCol = { ...col };

            // Convertir _valueFormatterCode a función real
            if (col._valueFormatterCode) {
                try {
                    // Evaluar el código como función
                    processedCol.valueFormatter = new Function('return ' + col._valueFormatterCode)();
                    delete processedCol._valueFormatterCode;
                } catch (error) {
                    console.error('[LEGO Table] Error creando valueFormatter para columna:', col.field, error);
                }
            }

            // Convertir _cellRendererCode a función real
            if (col._cellRendererCode) {
                try {
                    // Evaluar el código como función
                    processedCol.cellRenderer = new Function('return ' + col._cellRendererCode)();
                    delete processedCol._cellRendererCode;
                } catch (error) {
                    console.error('[LEGO Table] Error creando cellRenderer para columna:', col.field, error);
                }
            }

            // Auto-detectar tipo de filtro según el campo o tipo de columna
            // Solo si no tiene filtro personalizado ya definido
            if (!processedCol.filter && processedCol.field) {
                // Campos numéricos comunes
                const numericFields = ['price', 'stock', 'quantity', 'amount', 'total', 'count', 'id'];
                const isNumeric = numericFields.some(nf => processedCol.field.toLowerCase().includes(nf));

                if (isNumeric) {
                    processedCol.filter = 'agNumberColumnFilter';
                    processedCol.filterParams = {
                        buttons: ['reset', 'apply'],
                        closeOnApply: true
                    };
                }
            }

            return processedCol;
        });
    }

    // Inicializar la tabla
    /**
     * Crea la columna de acciones con botones personalizables
     * Los callbacks se buscan en el scope del módulo, NO en window global
     */
    function createActionsColumn(actions, tableId) {
        return {
            headerName: "Acciones",
            field: "_actions",
            width: 60 + (actions.length * 20), // Ancho reducido: menos de la mitad del anterior
            pinned: 'right',
            sortable: false,
            filter: false,
            resizable: false,
            cellRenderer: (params) => {
                const container = document.createElement('div');
                container.className = 'lego-table-actions';
                container.style.cssText = 'display: flex; gap: 0.25rem; align-items: center; height: 100%; justify-content: center;';

                actions.forEach(action => {
                    // Evaluar visibilidad condicional
                    if (action.visibleIf) {
                        try {
                            const visibleFn = new Function('params', `return ${action.visibleIf}`);
                            if (!visibleFn(params)) return;
                        } catch (e) {
                            console.error(`[LEGO Table] Error evaluando visibleIf para acción ${action.id}:`, e);
                        }
                    }

                    // Evaluar disabled condicional
                    let isDisabled = false;
                    if (action.disabledIf) {
                        try {
                            const disabledFn = new Function('params', `return ${action.disabledIf}`);
                            isDisabled = disabledFn(params);
                        } catch (e) {
                            console.error(`[LEGO Table] Error evaluando disabledIf para acción ${action.id}:`, e);
                        }
                    }

                    const button = document.createElement('button');
                    button.className = `lego-table-action-btn lego-table-action-${action.variant}`;
                    button.title = action.tooltip;
                    button.disabled = isDisabled;
                    button.style.cssText = `
                        padding: 0.25rem;
                        border: none;
                        background: transparent;
                        cursor: ${isDisabled ? 'not-allowed' : 'pointer'};
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.25rem;
                        font-size: 0.875rem;
                        transition: all 0.2s;
                        opacity: ${isDisabled ? '0.5' : '1'};
                        border-radius: 4px;
                    `;

                    // Agregar icono si existe
                    if (action.icon) {
                        const icon = document.createElement('ion-icon');
                        icon.name = action.icon;
                        icon.style.fontSize = '1.25rem';
                        icon.style.color = getVariantColor(action.variant, isDisabled);
                        icon.style.transition = 'all 0.2s';
                        button.appendChild(icon);
                    }

                    // Agregar label si showLabel es true
                    if (action.showLabel && action.label) {
                        const label = document.createElement('span');
                        label.textContent = action.label;
                        label.style.color = getVariantColor(action.variant, isDisabled);
                        button.appendChild(label);
                    }

                    // Event listener con callback scoped
                    if (!isDisabled) {
                        button.addEventListener('click', async (e) => {
                            e.stopPropagation();

                            // Confirmación si es necesaria
                            if (action.confirm) {
                                const confirmed = await showConfirmDialog(action.confirmMessage);
                                if (!confirmed) return;
                            }

                            // Ejecutar callback scoped (busca en el scope del módulo)
                            executeCallback(action.callback, params.data, tableId);
                        });

                        // Hover effects - solo al ícono
                        button.addEventListener('mouseenter', () => {
                            if (!isDisabled) {
                                button.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
                                const icon = button.querySelector('ion-icon');
                                if (icon) {
                                    icon.style.transform = 'scale(1.15)';
                                }
                            }
                        });
                        button.addEventListener('mouseleave', () => {
                            if (!isDisabled) {
                                button.style.backgroundColor = 'transparent';
                                const icon = button.querySelector('ion-icon');
                                if (icon) {
                                    icon.style.transform = 'scale(1)';
                                }
                            }
                        });
                    }

                    container.appendChild(button);
                });

                return container;
            }
        };
    }

    /**
     * Obtiene el color según el variant
     */
    function getVariantColor(variant, isDisabled) {
        if (isDisabled) {
            return '#9ca3af'; // gray-400
        }
        const colors = {
            'primary': '#4F46E5',    // indigo-600
            'secondary': '#6b7280',  // gray-500
            'danger': '#dc2626',     // red-600
            'success': '#059669',    // green-600
            'warning': '#f59e0b'     // amber-500
        };
        return colors[variant] || colors.secondary;
    }

    /**
     * Muestra un diálogo de confirmación
     */
    async function showConfirmDialog(message) {
        // Usar AlertService si está disponible
        if (window.lego && window.lego.alert && window.lego.alert.confirm) {
            return await window.lego.alert.confirm({
                title: 'Confirmar',
                text: message,
                icon: 'warning'
            });
        }
        // Fallback a confirm nativo
        return confirm(message);
    }

    /**
     * Ejecuta un callback scoped al componente
     * Busca la función en el scope del módulo, NO en window global
     */
    function executeCallback(callbackName, rowData, tableId) {
        console.log(`[LEGO Table] Ejecutando callback: ${callbackName} para tabla ${tableId}`);

        // Intentar buscar en window primero (por retrocompatibilidad)
        if (typeof window[callbackName] === 'function') {
            console.log(`[LEGO Table] Callback encontrado en window.${callbackName}`);
            window[callbackName](rowData, tableId);
            return;
        }

        // Si no está en window, emitir evento para que el componente lo maneje
        if (window.lego && window.lego.events) {
            console.log(`[LEGO Table] Emitiendo evento: table:action:${callbackName}`);
            window.lego.events.emit(`table:action:${callbackName}`, {
                rowData,
                tableId
            });
            return;
        }

        console.error(`[LEGO Table] No se encontró el callback: ${callbackName}`);
        console.error('[LEGO Table] Asegúrate de definir la función en window o suscribirte al evento table:action:' + callbackName);
    }

    function initTable() {
        const gridDiv = document.getElementById(id);
        if (!gridDiv) {
            console.error(`[LEGO Table] No se encontró el elemento con ID: ${id}`);
            return;
        }

        // Integrar con el sistema de temas de LEGO Framework
        const body = document.body;
        const html = document.documentElement;

        // Función para sincronizar el tema de AG Grid con LEGO
        const syncTheme = (theme) => {
            // Determinar si es modo oscuro
            let isDark = false;
            if (theme) {
                // Si recibimos el tema como parámetro, usarlo
                isDark = theme === 'dark';
            } else {
                // Si no, detectar del DOM
                isDark = html.classList.contains('dark') || body.classList.contains('dark');
            }

            console.log('[LEGO Table] Sincronizando tema. Parámetro:', theme, '| isDark:', isDark, '| html.classList:', html.classList.toString());

            // Aplicar atributos para CSS
            body.setAttribute('data-ag-theme-mode', isDark ? 'dark' : 'light');
            html.setAttribute('data-ag-theme-mode', isDark ? 'dark' : 'light');

            if (gridDiv) {
                gridDiv.setAttribute('data-ag-theme-mode', isDark ? 'dark' : 'light');

                // Aplicar variables CSS directamente al div de AG Grid
                if (isDark) {
                    // Tema oscuro - mismo fondo para todas las filas (transparente para heredar del contenedor)
                    const bgColor = 'transparent';
                    gridDiv.style.setProperty('--ag-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-header-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-odd-row-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-row-hover-color', 'rgba(255, 255, 255, 0.05)');
                    gridDiv.style.setProperty('--ag-selected-row-background-color', 'rgba(59, 130, 246, 0.2)');
                    gridDiv.style.setProperty('--ag-border-color', 'rgba(255, 255, 255, 0.1)');
                    gridDiv.style.setProperty('--ag-header-foreground-color', '#f3f4f6');
                    gridDiv.style.setProperty('--ag-foreground-color', '#e5e7eb');
                    gridDiv.style.setProperty('--ag-secondary-foreground-color', '#9ca3af');
                    gridDiv.style.setProperty('--ag-input-border-color', '#4b5563');
                } else {
                    // Tema claro - mismo fondo para todas las filas
                    const bgColor = '#ffffff';
                    gridDiv.style.setProperty('--ag-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-header-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-odd-row-background-color', bgColor);
                    gridDiv.style.setProperty('--ag-row-hover-color', '#f3f4f6');
                    gridDiv.style.setProperty('--ag-selected-row-background-color', 'rgba(59, 130, 246, 0.1)');
                    gridDiv.style.setProperty('--ag-border-color', '#e5e7eb');
                    gridDiv.style.setProperty('--ag-header-foreground-color', '#1f2937');
                    gridDiv.style.setProperty('--ag-foreground-color', '#374151');
                    gridDiv.style.setProperty('--ag-secondary-foreground-color', '#6b7280');
                    gridDiv.style.setProperty('--ag-input-border-color', '#d1d5db');
                }

                // Quitar sombra de la tabla
                gridDiv.style.boxShadow = 'none';
                gridDiv.style.setProperty('--ag-wrapper-border-radius', '0px');
            }

            console.log('[LEGO Table] Tema sincronizado:', isDark ? 'dark' : 'light');
        };

        // Aplicar tema inicial
        const initialTheme = window.themeManager ? window.themeManager.getCurrentTheme() :
                           (html.classList.contains('dark') ? 'dark' : 'light');
        syncTheme(initialTheme);
        console.log('[LEGO Table] Tema inicial aplicado:', initialTheme);

        // Suscribirse a cambios de tema del LEGO Framework
        if (window.themeManager) {
            window.themeManager.subscribe((theme) => {
                console.log('[LEGO Table] Cambio de tema detectado:', theme);
                syncTheme(theme);
            });
            console.log('[LEGO Table] Suscrito al ThemeManager');
        } else {
            // Fallback: escuchar cambios en la preferencia del sistema si no hay ThemeManager
            console.warn('[LEGO Table] ThemeManager no disponible, usando fallback');
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                syncTheme(e.matches ? 'dark' : 'light');
            });
        }

        // Procesar columnDefs para convertir código JavaScript a funciones
        let processedColumnDefs = processColumnDefs(columnDefs);

        // Si hay rowActions, agregar columna de acciones
        if (config.rowActions && config.rowActions.length > 0) {
            const actionsColumn = createActionsColumn(config.rowActions, id);
            processedColumnDefs.push(actionsColumn);
        }

        // Configuración completa de AG Grid
        const fullGridOptions = {
            columnDefs: processedColumnDefs,
            // Solo agregar rowData si NO es modo server-side
            ...(config.serverSide ? {} : { rowData: rowData }),
            ...gridOptions,

            // Habilitar selección y copia de texto
            enableCellTextSelection: true,
            ensureDomOrder: true,

            // Habilitar tercera opción de ordenamiento (sin orden)
            unSortIcon: true,

            // Mejorar configuración de filtros (accesibles desde menú de columna)
            defaultColDef: {
                ...(gridOptions.defaultColDef || {}),
                filter: 'agTextColumnFilter', // Filtro de texto por defecto
                filterParams: {
                    buttons: ['reset', 'apply'],
                    closeOnApply: true
                }
            },

            // Overlay personalizado para "sin datos"
            overlayNoRowsTemplate: `
                <div style="padding: 40px; text-align: center; color: #6b7280;">
                    <ion-icon name="folder-open-outline" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></ion-icon>
                    <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">
                        ${gridOptions.noRowsOverlayComponentParams?.message || 'No hay datos disponibles'}
                    </div>
                    <div style="font-size: 14px; color: #9ca3af;">
                        Aún no se han agregado registros
                    </div>
                </div>
            `,

            // Overlay personalizado para "cargando"
            overlayLoadingTemplate: `
                <div style="padding: 40px; text-align: center;">
                    <div class="ag-custom-loading-cell" style="padding-left: 10px;">
                        <i class="fas fa-spinner fa-pulse"></i> Cargando datos...
                    </div>
                </div>
            `,

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

                // Inicializar registry global de tablas
                if (!window.LEGO_TABLES) {
                    window.LEGO_TABLES = {};
                }

                // Guardar referencia estructurada
                window.LEGO_TABLES[id] = {
                    api: event.api,
                    columnApi: event.columnApi,
                    tableId: id,
                    jsId: jsId
                };

                // Disparar evento personalizado para que otros componentes sepan que la tabla está lista
                const tableReadyEvent = new CustomEvent('lego:table:ready', {
                    detail: {
                        tableId: id,
                        jsId: jsId,
                        api: event.api,
                        columnApi: event.columnApi
                    }
                });
                window.dispatchEvent(tableReadyEvent);
                console.log('[LEGO Table] Evento lego:table:ready disparado para:', id);

                // Callback personalizado onGridReady
                if (callbacks.onGridReady) {
                    window[callbacks.onGridReady]?.(event);
                }

                // Autoajustar columnas si es necesario
                event.api.sizeColumnsToFit();

                // Hacer la tabla responsive al resize del viewport
                const resizeObserver = new ResizeObserver(() => {
                    // Debounce para evitar demasiadas llamadas
                    if (window._legoTableResizeTimeout) {
                        clearTimeout(window._legoTableResizeTimeout);
                    }
                    window._legoTableResizeTimeout = setTimeout(() => {
                        if (event.api) {
                            event.api.sizeColumnsToFit();
                        }
                    }, 150);
                });

                // Observar cambios en el tamaño del contenedor de la grid
                resizeObserver.observe(gridDiv);

                // También escuchar eventos de resize de ventana
                window.addEventListener('resize', () => {
                    if (window._legoTableWindowResizeTimeout) {
                        clearTimeout(window._legoTableWindowResizeTimeout);
                    }
                    window._legoTableWindowResizeTimeout = setTimeout(() => {
                        if (event.api) {
                            event.api.sizeColumnsToFit();
                        }
                    }, 150);
                });

                console.log('[LEGO Table] Auto-resize configurado');
            }
        };

        // ========================================
        // SERVER-SIDE MODE (Model-Driven)
        // ========================================
        if (config.serverSide && config.apiConfig) {
            console.log('[LEGO Table] Modo server-side habilitado:', config.apiConfig);

            // Remover rowData inicial (se cargará desde API)
            delete fullGridOptions.rowData;

            // Configurar paginación server-side
            fullGridOptions.rowModelType = 'infinite';
            fullGridOptions.cacheBlockSize = config.apiConfig.perPage;
            fullGridOptions.cacheOverflowSize = 2;
            fullGridOptions.maxConcurrentDatasourceRequests = 1;
            fullGridOptions.infiniteInitialRowCount = 1000;
            fullGridOptions.maxBlocksInCache = 10;

            // Guardar el onGridReady original
            const originalOnGridReady = fullGridOptions.onGridReady;

            // Crear datasource para cargar datos desde API
            fullGridOptions.onGridReady = function(gridReadyParams) {
                // Ejecutar el onGridReady original primero
                if (originalOnGridReady) {
                    originalOnGridReady(gridReadyParams);
                }

                const gridApiRef = gridReadyParams.api;

                const dataSource = {
                    rowCount: null,
                    getRows: function(params) {
                        console.log('[LEGO Table] Solicitando filas:', params.startRow, '-', params.endRow);
                        console.log('[LEGO Table] Sort model:', params.sortModel);
                        console.log('[LEGO Table] Filter model:', params.filterModel);

                        // Calcular página actual
                        const page = Math.floor(params.startRow / config.apiConfig.perPage) + 1;

                        // Construir URL con parámetros
                        const url = new URL(config.apiConfig.apiEndpoint, window.location.origin);
                        url.searchParams.append('page', page);
                        url.searchParams.append('limit', config.apiConfig.perPage);

                        // Agregar parámetros de ordenamiento
                        if (params.sortModel && params.sortModel.length > 0) {
                            const sortBy = params.sortModel[0].colId;
                            const sortOrder = params.sortModel[0].sort; // 'asc' or 'desc'
                            url.searchParams.append('sort', sortBy);
                            url.searchParams.append('order', sortOrder);
                        }

                        // Agregar parámetros de filtrado
                        if (params.filterModel && Object.keys(params.filterModel).length > 0) {
                            // Convertir filterModel a parámetros de query
                            Object.entries(params.filterModel).forEach(([field, filter]) => {
                                if (filter.filterType === 'text') {
                                    url.searchParams.append(`filter[${field}]`, filter.filter || '');
                                    if (filter.type) {
                                        url.searchParams.append(`filter[${field}_type]`, filter.type); // contains, equals, etc
                                    }
                                } else if (filter.filterType === 'number') {
                                    if (filter.filter !== undefined) {
                                        url.searchParams.append(`filter[${field}]`, filter.filter);
                                    }
                                    if (filter.type) {
                                        url.searchParams.append(`filter[${field}_type]`, filter.type); // equals, greaterThan, etc
                                    }
                                }
                            });
                        }

                        console.log('[LEGO Table] Fetching:', url.toString());

                        // Hacer fetch a la API
                        fetch(url.toString())
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('[LEGO Table] Datos recibidos:', data.data.length, 'filas');

                                    // Actualizar total de filas
                                    let lastRow = -1;
                                    if (data.pagination && data.pagination.total !== undefined) {
                                        lastRow = data.pagination.total;
                                    }

                                    params.successCallback(data.data, lastRow);

                                    // Mostrar overlay si no hay datos
                                    if (data.data.length === 0 && params.startRow === 0) {
                                        setTimeout(() => {
                                            if (gridApiRef && gridApiRef.showNoRowsOverlay) {
                                                gridApiRef.showNoRowsOverlay();
                                            }
                                        }, 100);
                                    }
                                } else {
                                    console.error('[LEGO Table] Error en respuesta API:', data);
                                    params.failCallback();
                                }
                            })
                            .catch(error => {
                                console.error('[LEGO Table] Error fetching data:', error);
                                params.failCallback();
                            });
                    }
                };

                gridReadyParams.api.setGridOption('datasource', dataSource);
            };
        }

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
                // AG Grid Community no soporta Excel export
                // Usar CSV como alternativa compatible
                console.log('[LEGO Table] Exportando datos...');
                console.warn('[LEGO Table] Excel export requiere AG Grid Enterprise. Usando formato CSV.');

                api.exportDataAsCsv({
                    fileName: config.exportFileName || 'export'
                });

                console.log('[LEGO Table] ✓ Archivo CSV descargado exitosamente');
            } else {
                console.error('[LEGO Table] API de tabla no disponible');
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

        // Función para recargar datos del servidor (purge cache)
        window[`legoTable_${jsId}_refresh`] = function() {
            const api = window[`legoTable_${jsId}_api`];
            if (api) {
                console.log('[LEGO Table] Recargando datos del servidor...');
                api.refreshInfiniteCache();
                console.log('[LEGO Table] Datos recargados');
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
