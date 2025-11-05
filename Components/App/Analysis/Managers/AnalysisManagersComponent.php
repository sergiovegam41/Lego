<?php
namespace Components\App\Analysis\Managers;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

/**
 * AnalysisManagersComponent - Visualización de managers con Google Charts
 *
 * FILOSOFÍA LEGO:
 * Componente que muestra análisis de participación de managers mediante un 3D Pie Chart.
 * Layout de 2 columnas: gráfica (60%) + conclusión (40%).
 *
 * CARACTERÍSTICAS:
 * ✅ Fetch de datos desde /api/analysis/jugadores
 * ✅ Google Charts 3D Pie Chart
 * ✅ Loader mientras carga datos
 * ✅ Layout responsive con Flexbox
 * ✅ Top 15 managers + "Otros"
 */
#[ApiComponent('/analysis/managers', methods: ['GET'])]
class AnalysisManagersComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./managers.css"];
    protected $JS_PATHS = ["./managers.js"];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-managers">
            <!-- Contenedor principal con layout flex -->
            <div class="analysis-managers__container">

                <!-- Sección izquierda: Gráfica -->
                <div class="analysis-managers__chart-section">
                    <!-- Loader -->
                    <div id="managers-loader" class="analysis-managers__loader">
                        <span class="analysis-managers__spinner"></span>
                    </div>

                    <!-- Header -->
                    <div class="analysis-managers__header">
                        <h1 class="analysis-managers__title">Managers</h1>
                        <p class="analysis-managers__description">
                            Este módulo se enfoca en analizar la participación de los managers del equipo local de cada partido
                            con el objetivo de identificar cuál es el que más destaca entre ellos.
                        </p>
                    </div>

                    <!-- Chart Container -->
                    <div id="piechart_managers" class="analysis-managers__chart"></div>
                </div>

                <!-- Sección derecha: Conclusión -->
                <div class="analysis-managers__conclusion-section">
                    <div class="analysis-managers__divider"></div>

                    <div class="analysis-managers__conclusion-content">
                        <h3 class="analysis-managers__conclusion-title">Conclusión</h3>
                        <p class="analysis-managers__conclusion-text">
                            Mediante otro gráfico circular y algunos análisis estadísticos descubrimos la mayor participación de los managers en cada partido,
                            descubriendo así que otros tipos de managers en el equipo local tienen mayor participación en los partidos siendo esta la de un 7%.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
