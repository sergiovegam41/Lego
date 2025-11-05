<?php
namespace Components\App\Analysis\Ligas;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

/**
 * AnalysisLigasComponent - Visualización de ligas/competiciones con Google Charts
 *
 * FILOSOFÍA LEGO:
 * Componente que muestra análisis de participación de ligas mediante un Donut Chart.
 * Layout de 2 columnas: gráfica (60%) + conclusión (40%).
 *
 * CARACTERÍSTICAS:
 * ✅ Fetch de datos desde /api/analysis/partidos
 * ✅ Google Charts Donut Chart (pieHole: 0.4)
 * ✅ Loader mientras carga datos
 * ✅ Layout responsive con Flexbox
 * ✅ Top 10 ligas + "Otros"
 */
#[ApiComponent('/analysis/ligas', methods: ['GET'])]
class AnalysisLigasComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./ligas.css"];
    protected $JS_PATHS = ["./ligas.js"];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-ligas">
            <!-- Contenedor principal con layout flex -->
            <div class="analysis-ligas__container">

                <!-- Sección izquierda: Gráfica -->
                <div class="analysis-ligas__chart-section">
                    <!-- Loader -->
                    <div id="ligas-loader" class="analysis-ligas__loader">
                        <span class="analysis-ligas__spinner"></span>
                    </div>

                    <!-- Header -->
                    <div class="analysis-ligas__header">
                        <h1 class="analysis-ligas__title">Ligas en el Fútbol</h1>
                        <p class="analysis-ligas__description">
                            Este módulo se enfoca en analizar la participación de cada liga
                            con el objetivo de identificar cuál es la liga destacada.
                        </p>
                    </div>

                    <!-- Chart Container -->
                    <div id="donutchart_ligas" class="analysis-ligas__chart"></div>
                </div>

                <!-- Sección derecha: Conclusión -->
                <div class="analysis-ligas__conclusion-section">
                    <div class="analysis-ligas__divider"></div>

                    <div class="analysis-ligas__conclusion-content">
                        <h3 class="analysis-ligas__conclusion-title">Conclusión</h3>
                        <p class="analysis-ligas__conclusion-text">
                            En esta sección también hicimos uso de la Visualización gráfica.
                            Empleando el gráfico circular descubrimos que la liga más destacada es "La Liga"
                            debido a que esta tiene mayor popularidad e influencia con los espectadores.
                            En otras palabras, "La Liga" representa 43.1% del total de espectadores que visualizan la liga.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
