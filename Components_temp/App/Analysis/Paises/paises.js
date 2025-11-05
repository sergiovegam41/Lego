/**
 * Analysis Países - Google GeoChart Integration
 *
 * FLUJO:
 * 1. Cargar Google Charts library
 * 2. Fetch datos desde /api/analysis/mapa
 * 3. Transformar de objeto a array para Google Charts
 * 4. Dibujar gráfico GeoChart (mapa mundial)
 * 5. Remover loader
 */

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPaisesChart);
} else {
    initPaisesChart();
}

function initPaisesChart() {
    console.log('[Países] Inicializando módulo de países...');

    // Cargar Google Charts library
    loadGoogleCharts();
}

/**
 * Cargar Google Charts desde CDN
 */
function loadGoogleCharts() {
    // Verificar si Google Charts ya está cargado
    if (typeof google !== 'undefined' && google.charts) {
        console.log('[Países] Google Charts ya está cargado');
        fetchDataAndRender();
        return;
    }

    // Cargar script de Google Charts
    const script = document.createElement('script');
    script.src = 'https://www.gstatic.com/charts/loader.js';
    script.onload = () => {
        console.log('[Países] Google Charts cargado exitosamente');
        fetchDataAndRender();
    };
    script.onerror = () => {
        console.error('[Países] Error al cargar Google Charts');
        hideLoader();
        showError('Error al cargar la librería de gráficos');
    };
    document.head.appendChild(script);
}

/**
 * Fetch datos desde el API y renderizar gráfico
 */
async function fetchDataAndRender() {
    try {
        console.log('[Países] Fetching datos desde API...');

        // Fetch usando apiClient si está disponible, sino fetch nativo
        let response;
        if (window.apiClient) {
            response = await window.apiClient.get('/api/analysis/mapa');
        } else {
            const res = await fetch('/api/analysis/mapa');
            response = await res.json();
        }

        console.log('[Países] Datos recibidos:', response);

        // Validar respuesta
        if (!response.success || !response.data) {
            throw new Error(response.message || 'No se recibieron datos válidos');
        }

        // Transformar datos de objeto a array para Google Charts
        const chartData = transformDataForChart(response.data);

        // Cargar paquete de Google Charts y dibujar
        google.charts.load('current', { packages: ['geochart'] });
        google.charts.setOnLoadCallback(() => {
            drawGeoChart(chartData);
            // Remover loader después de un breve delay para UX suave
            setTimeout(hideLoader, 500);
        });

    } catch (error) {
        console.error('[Países] Error al cargar datos:', error);
        hideLoader();
        showError('Error al cargar los datos de países: ' + error.message);
    }
}

/**
 * Transformar datos de objeto a array para Google Charts
 * Para GeoChart: [["País", "Competencias"], ["Spain", 541], ["England", 380], ...]
 *
 * Input:  { "Spain": 541, "England": 380, ... }
 * Output: [["País", "Competencias"], ["Spain", 541], ["England", 380], ...]
 */
function transformDataForChart(dataObject) {
    console.log('[Países] Transformando datos para Google Charts...');

    // Header del array
    const chartData = [['País', 'Competencias']];

    // Convertir objeto a array de pares [key, value]
    const entries = Object.entries(dataObject);

    // GeoChart no necesita ordenamiento ni agrupación
    // Muestra todos los países en el mapa con intensidad según el valor
    entries.forEach(([pais, cantidad]) => {
        chartData.push([pais, cantidad]);
    });

    console.log('[Países] Total de países:', entries.length);

    return chartData;
}

/**
 * Obtener el color de texto según el tema actual
 */
function getTextColor() {
    const isDark = document.documentElement.classList.contains('dark-theme');
    return isDark ? '#e0e0e0' : '#333333';
}

/**
 * Obtener colores de regiones según el tema actual
 */
function getRegionColors() {
    const isDark = document.documentElement.classList.contains('dark-theme');
    return isDark ? '#424242' : '#f5f5f5';
}

/**
 * Dibujar el gráfico GeoChart (mapa mundial)
 */
function drawGeoChart(chartData) {
    console.log('[Países] Dibujando gráfico...');

    const data = google.visualization.arrayToDataTable(chartData);
    const textColor = getTextColor();
    const regionColor = getRegionColors();

    const options = {
        backgroundColor: 'transparent',
        colorAxis: {
            colors: ['#E8F5E9', '#66BB6A', '#2E7D32']
        },
        datalessRegionColor: regionColor,
        defaultColor: regionColor,
        legend: {
            numberFormat: 'decimal',
            textStyle: {
                color: textColor,
                fontSize: 12
            }
        },
        tooltip: {
            textStyle: {
                color: textColor
            }
        }
    };

    const chartContainer = document.getElementById('geochart_paises');

    if (!chartContainer) {
        console.error('[Países] Contenedor del gráfico no encontrado');
        return;
    }

    const chart = new google.visualization.GeoChart(chartContainer);
    chart.draw(data, options);

    // Guardar referencia para redibujar en cambio de tema
    window.paisesChartData = chartData;
    window.paisesChart = chart;

    console.log('[Países] Gráfico dibujado exitosamente');
}

/**
 * Ocultar el loader
 */
function hideLoader() {
    const loader = document.getElementById('paises-loader');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            loader.remove();
        }, 300);
    }
}

/**
 * Mostrar mensaje de error
 */
function showError(message) {
    const chartContainer = document.getElementById('geochart_paises');
    if (chartContainer) {
        chartContainer.innerHTML = `
            <div style="
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100%;
                color: var(--text-secondary);
                flex-direction: column;
                gap: 1rem;
            ">
                <ion-icon name="alert-circle-outline" style="font-size: 4rem; color: var(--error-color, #EA4335);"></ion-icon>
                <p style="font-size: 1.1rem; text-align: center;">${message}</p>
                <button onclick="location.reload()" style="
                    padding: 0.5rem 1rem;
                    background: var(--primary-color, #4285F4);
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Reintentar</button>
            </div>
        `;
    }
}

/**
 * Listener para cambios de tema
 */
function setupThemeListener() {
    // Observar cambios en la clase del html
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                console.log('[Países] Tema cambiado, redibujando gráfico...');
                // Redibujar el gráfico con los nuevos colores
                if (window.paisesChartData && window.paisesChart) {
                    drawGeoChart(window.paisesChartData);
                }
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Inicializar listener de tema
setupThemeListener();

// Exportar funciones para testing (opcional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        transformDataForChart,
        drawGeoChart
    };
}
