/**
 * Analysis Ligas - Google Charts Donut Chart Integration
 *
 * FLUJO:
 * 1. Cargar Google Charts library
 * 2. Fetch datos desde /api/analysis/partidos
 * 3. Transformar de objeto a array para Google Charts
 * 4. Dibujar gráfico Donut Chart
 * 5. Remover loader
 */

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLigasChart);
} else {
    initLigasChart();
}

function initLigasChart() {
    console.log('[Ligas] Inicializando módulo de ligas...');

    // Cargar Google Charts library
    loadGoogleCharts();
}

/**
 * Cargar Google Charts desde CDN
 */
function loadGoogleCharts() {
    // Verificar si Google Charts ya está cargado
    if (typeof google !== 'undefined' && google.charts) {
        console.log('[Ligas] Google Charts ya está cargado');
        fetchDataAndRender();
        return;
    }

    // Cargar script de Google Charts
    const script = document.createElement('script');
    script.src = 'https://www.gstatic.com/charts/loader.js';
    script.onload = () => {
        console.log('[Ligas] Google Charts cargado exitosamente');
        fetchDataAndRender();
    };
    script.onerror = () => {
        console.error('[Ligas] Error al cargar Google Charts');
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
        console.log('[Ligas] Fetching datos desde API...');

        // Fetch usando apiClient si está disponible, sino fetch nativo
        let response;
        if (window.apiClient) {
            response = await window.apiClient.get('/api/analysis/partidos');
        } else {
            const res = await fetch('/api/analysis/partidos');
            response = await res.json();
        }

        console.log('[Ligas] Datos recibidos:', response);

        // Validar respuesta
        if (!response.success || !response.data) {
            throw new Error(response.message || 'No se recibieron datos válidos');
        }

        // Transformar datos de objeto a array para Google Charts
        const chartData = transformDataForChart(response.data);

        // Cargar paquete de Google Charts y dibujar
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(() => {
            drawDonutChart(chartData);
            // Remover loader después de un breve delay para UX suave
            setTimeout(hideLoader, 500);
        });

    } catch (error) {
        console.error('[Ligas] Error al cargar datos:', error);
        hideLoader();
        showError('Error al cargar los datos de ligas: ' + error.message);
    }
}

/**
 * Transformar datos de objeto a array para Google Charts
 * Agrupa las ligas más pequeñas en "Otros" para mejor visualización
 *
 * Input:  { "La Liga": 541, "Premier League": 380, ... }
 * Output: [["Liga", "Partidos"], ["La Liga", 541], ["Premier League", 380], ..., ["Otros", 45]]
 */
function transformDataForChart(dataObject) {
    console.log('[Ligas] Transformando datos para Google Charts...');

    const TOP_LIMIT = 10; // Mostrar solo las top 10 ligas

    // Header del array
    const chartData = [['Liga', 'Partidos']];

    // Convertir objeto a array de pares [key, value]
    const entries = Object.entries(dataObject);

    // Ordenar de mayor a menor
    entries.sort((a, b) => b[1] - a[1]);

    console.log('[Ligas] Total de ligas:', entries.length);

    // Si hay menos de TOP_LIMIT ligas, mostrar todas
    if (entries.length <= TOP_LIMIT) {
        entries.forEach(([liga, cantidad]) => {
            chartData.push([liga, cantidad]);
        });
        console.log('[Ligas] Mostrando todas las ligas:', entries.length);
    } else {
        // Tomar las top TOP_LIMIT
        const topEntries = entries.slice(0, TOP_LIMIT);

        // Agrupar el resto en "Otros"
        const otherEntries = entries.slice(TOP_LIMIT);
        const otrosTotal = otherEntries.reduce((sum, [, cantidad]) => sum + cantidad, 0);

        // Agregar top ligas
        topEntries.forEach(([liga, cantidad]) => {
            chartData.push([liga, cantidad]);
        });

        // Agregar "Otros" si hay datos agrupados
        if (otrosTotal > 0) {
            chartData.push(['Otros', otrosTotal]);
        }

        console.log(`[Ligas] Mostrando top ${TOP_LIMIT} ligas + "Otros" (${otherEntries.length} ligas agrupadas)`);
    }

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
 * Dibujar el gráfico Donut Chart
 */
function drawDonutChart(chartData) {
    console.log('[Ligas] Dibujando gráfico...');

    const data = google.visualization.arrayToDataTable(chartData);
    const textColor = getTextColor();

    const options = {
        title: '',
        pieHole: 0.4, // Esto hace que sea Donut en lugar de Pie
        backgroundColor: 'transparent',
        legend: {
            position: 'right',
            alignment: 'center',
            textStyle: {
                fontSize: 12,
                color: textColor
            }
        },
        chartArea: {
            width: '90%',
            height: '80%'
        },
        pieSliceText: 'percentage',
        pieSliceTextStyle: {
            fontSize: 11,
            color: textColor
        },
        tooltip: {
            textStyle: {
                color: textColor
            }
        },
        // Colores personalizados (opcional)
        colors: [
            '#4285F4', '#EA4335', '#FBBC04', '#34A853', '#FF6D01',
            '#46BDC6', '#7BAAF7', '#F07B72', '#FCD04F', '#71C287'
        ]
    };

    const chartContainer = document.getElementById('donutchart_ligas');

    if (!chartContainer) {
        console.error('[Ligas] Contenedor del gráfico no encontrado');
        return;
    }

    const chart = new google.visualization.PieChart(chartContainer);
    chart.draw(data, options);

    // Guardar referencia para redibujar en cambio de tema
    window.ligasChartData = chartData;
    window.ligasChart = chart;

    console.log('[Ligas] Gráfico dibujado exitosamente');
}

/**
 * Ocultar el loader
 */
function hideLoader() {
    const loader = document.getElementById('ligas-loader');
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
    const chartContainer = document.getElementById('donutchart_ligas');
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
                console.log('[Ligas] Tema cambiado, redibujando gráfico...');
                // Redibujar el gráfico con los nuevos colores
                if (window.ligasChartData && window.ligasChart) {
                    drawDonutChart(window.ligasChartData);
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
        drawDonutChart
    };
}
