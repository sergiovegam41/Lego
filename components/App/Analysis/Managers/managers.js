/**
 * Analysis Managers - Google Charts 3D Pie Chart Integration
 *
 * FLUJO:
 * 1. Cargar Google Charts library
 * 2. Fetch datos desde /api/analysis/jugadores
 * 3. Transformar de objeto a array para Google Charts
 * 4. Dibujar gráfico 3D Pie Chart
 * 5. Remover loader
 */

// Esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initManagersChart);
} else {
    initManagersChart();
}

function initManagersChart() {
    console.log('[Managers] Inicializando módulo de managers...');

    // Cargar Google Charts library
    loadGoogleCharts();
}

/**
 * Cargar Google Charts desde CDN
 */
function loadGoogleCharts() {
    // Verificar si Google Charts ya está cargado
    if (typeof google !== 'undefined' && google.charts) {
        console.log('[Managers] Google Charts ya está cargado');
        fetchDataAndRender();
        return;
    }

    // Cargar script de Google Charts
    const script = document.createElement('script');
    script.src = 'https://www.gstatic.com/charts/loader.js';
    script.onload = () => {
        console.log('[Managers] Google Charts cargado exitosamente');
        fetchDataAndRender();
    };
    script.onerror = () => {
        console.error('[Managers] Error al cargar Google Charts');
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
        console.log('[Managers] Fetching datos desde API...');

        // Fetch usando apiClient si está disponible, sino fetch nativo
        let response;
        if (window.apiClient) {
            response = await window.apiClient.get('/api/analysis/jugadores');
        } else {
            const res = await fetch('/api/analysis/jugadores');
            response = await res.json();
        }

        console.log('[Managers] Datos recibidos:', response);

        // Validar respuesta
        if (!response.success || !response.data) {
            throw new Error(response.message || 'No se recibieron datos válidos');
        }

        // Transformar datos de objeto a array para Google Charts
        const chartData = transformDataForChart(response.data);

        // Cargar paquete de Google Charts y dibujar
        google.charts.load('current', { packages: ['corechart'] });
        google.charts.setOnLoadCallback(() => {
            drawPieChart(chartData);
            // Remover loader después de un breve delay para UX suave
            setTimeout(hideLoader, 500);
        });

    } catch (error) {
        console.error('[Managers] Error al cargar datos:', error);
        hideLoader();
        showError('Error al cargar los datos de managers: ' + error.message);
    }
}

/**
 * Transformar datos de objeto a array para Google Charts
 * Agrupa los managers más pequeños en "Otros" para mejor visualización
 *
 * Input:  { "Manager A": 145, "Manager B": 23, ... }
 * Output: [["Manager", "Partidos"], ["Manager A", 145], ["Manager B", 23], ..., ["Otros", 45]]
 */
function transformDataForChart(dataObject) {
    console.log('[Managers] Transformando datos para Google Charts...');

    const TOP_LIMIT = 15; // Mostrar solo los top 15 managers

    // Header del array
    const chartData = [['Manager', 'Partidos']];

    // Convertir objeto a array de pares [key, value]
    const entries = Object.entries(dataObject);

    // Ordenar de mayor a menor
    entries.sort((a, b) => b[1] - a[1]);

    console.log('[Managers] Total de managers:', entries.length);

    // Si hay menos de TOP_LIMIT managers, mostrar todos
    if (entries.length <= TOP_LIMIT) {
        entries.forEach(([manager, cantidad]) => {
            chartData.push([manager, cantidad]);
        });
        console.log('[Managers] Mostrando todos los managers:', entries.length);
    } else {
        // Tomar los top TOP_LIMIT
        const topEntries = entries.slice(0, TOP_LIMIT);

        // Agrupar el resto en "Otros"
        const otherEntries = entries.slice(TOP_LIMIT);
        const otrosTotal = otherEntries.reduce((sum, [, cantidad]) => sum + cantidad, 0);

        // Agregar top managers
        topEntries.forEach(([manager, cantidad]) => {
            chartData.push([manager, cantidad]);
        });

        // Agregar "Otros" si hay datos agrupados
        if (otrosTotal > 0) {
            chartData.push(['Otros', otrosTotal]);
        }

        console.log(`[Managers] Mostrando top ${TOP_LIMIT} managers + "Otros" (${otherEntries.length} managers agrupados)`);
    }

    return chartData;
}

/**
 * Dibujar el gráfico 3D Pie Chart
 */
function drawPieChart(chartData) {
    console.log('[Managers] Dibujando gráfico...');

    const data = google.visualization.arrayToDataTable(chartData);

    const options = {
        title: '',
        is3D: true,
        backgroundColor: 'transparent',
        legend: {
            position: 'right',
            alignment: 'center',
            textStyle: {
                fontSize: 12
            }
        },
        chartArea: {
            width: '90%',
            height: '80%'
        },
        pieSliceText: 'percentage',
        pieSliceTextStyle: {
            fontSize: 11
        },
        // Colores personalizados (opcional)
        colors: [
            '#4285F4', '#EA4335', '#FBBC04', '#34A853', '#FF6D01',
            '#46BDC6', '#7BAAF7', '#F07B72', '#FCD04F', '#71C287'
        ]
    };

    const chartContainer = document.getElementById('piechart_managers');

    if (!chartContainer) {
        console.error('[Managers] Contenedor del gráfico no encontrado');
        return;
    }

    const chart = new google.visualization.PieChart(chartContainer);
    chart.draw(data, options);

    console.log('[Managers] Gráfico dibujado exitosamente');
}

/**
 * Ocultar el loader
 */
function hideLoader() {
    const loader = document.getElementById('managers-loader');
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
    const chartContainer = document.getElementById('piechart_managers');
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

// Exportar funciones para testing (opcional)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        transformDataForChart,
        drawPieChart
    };
}
