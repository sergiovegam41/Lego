<?php
namespace Components\App\Analysis\Estadios;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Essentials\Essentials\{Div, Row, Column};

/**
 * AnalysisEstadiosComponent - Visualización de estadios con Google Charts
 *
 * FILOSOFÍA LEGO:
 * Componente que muestra análisis de uso de estadios mediante un gráfico 3D Pie Chart.
 * Layout de 2 columnas: gráfica (60%) + conclusión (40%).
 *
 * CARACTERÍSTICAS:
 * ✅ Fetch de datos desde /api/analysis/estadios
 * ✅ Google Charts 3D Pie Chart
 * ✅ Loader mientras carga datos
 * ✅ Layout responsive con Flexbox
 * ✅ Separación de estilos y lógica
 */
#[ApiComponent('/analysis/estadios', methods: ['GET'])]
class AnalysisEstadiosComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./estadios.css"];
    protected $JS_PATHS = ["./estadios.js"];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-estadios">
            <!-- Contenedor principal con layout flex -->
            <div class="analysis-estadios__container">

                <!-- Sección izquierda: Gráfica -->
                <div class="analysis-estadios__chart-section">
                    <!-- Loader -->
                    <div id="estadios-loader" class="analysis-estadios__loader">
                        <span class="analysis-estadios__spinner"></span>
                    </div>

                    <!-- Header -->
                    <div class="analysis-estadios__header">
                        <h1 class="analysis-estadios__title">Estadios</h1>
                        <p class="analysis-estadios__description">
                            Este módulo se enfoca en analizar el uso que se le da a todos los estadios
                            con el objetivo de identificar los estadios más usados con base en los datos.
                        </p>
                    </div>

                    <!-- Chart Container -->
                    <div id="piechart_estadios" class="analysis-estadios__chart"></div>
                </div>

                <!-- Sección derecha: Conclusión -->
                <div class="analysis-estadios__conclusion-section">
                    <div class="analysis-estadios__divider"></div>

                    <div class="analysis-estadios__conclusion-content">
                        <h3 class="analysis-estadios__conclusion-title">Conclusión</h3>
                        <p class="analysis-estadios__conclusion-text">
                            En esta sección hicimos uso de la Visualización gráfica
                            mediante un gráfico circular. Gracias a utilizar un gráfico circular,
                            la información se traslada mejor que a través de una simple tabla dado que se puede apreciar mejor
                            el peso que representa cada categoría sobre el total, mostrando así desde el estadio menos popular al más popular.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
