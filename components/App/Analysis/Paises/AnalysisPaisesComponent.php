<?php
namespace Components\App\Analysis\Paises;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

/**
 * AnalysisPaisesComponent - Visualización de países con Google GeoChart
 *
 * FILOSOFÍA LEGO:
 * Componente que muestra análisis geográfico de competencias mediante un mapa mundial interactivo.
 * Layout de 2 columnas: gráfica (60%) + conclusión (40%).
 *
 * CARACTERÍSTICAS:
 * ✅ Fetch de datos desde /api/analysis/mapa
 * ✅ Google Charts GeoChart (mapa mundial)
 * ✅ Loader mientras carga datos
 * ✅ Layout responsive con Flexbox
 * ✅ Visualización interactiva por países
 */
#[ApiComponent('/analysis/paises', methods: ['GET'])]
class AnalysisPaisesComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./paises.css"];
    protected $JS_PATHS = ["./paises.js"];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-paises">
            <!-- Contenedor principal con layout flex -->
            <div class="analysis-paises__container">

                <!-- Sección izquierda: Gráfica -->
                <div class="analysis-paises__chart-section">
                    <!-- Loader -->
                    <div id="paises-loader" class="analysis-paises__loader">
                        <span class="analysis-paises__spinner"></span>
                    </div>

                    <!-- Header -->
                    <div class="analysis-paises__header">
                        <h1 class="analysis-paises__title">Países</h1>
                        <p class="analysis-paises__description">
                            Este módulo se enfoca en analizar la ubicación geográfica de todas las competencias realizadas en todo el mundo
                            con el objetivo de identificar los países más involucrados con el fútbol con base en nuestros datos.
                        </p>
                    </div>

                    <!-- Chart Container -->
                    <div id="geochart_paises" class="analysis-paises__chart"></div>
                </div>

                <!-- Sección derecha: Conclusión -->
                <div class="analysis-paises__conclusion-section">
                    <div class="analysis-paises__divider"></div>

                    <div class="analysis-paises__conclusion-content">
                        <h3 class="analysis-paises__conclusion-title">Conclusión</h3>
                        <p class="analysis-paises__conclusion-text">
                            Haciendo uso de un análisis con distribución de frecuencias en el mapa geográfico del mundo encontramos que la mayoría de competencias
                            realizadas en el mundo se la lleva España teniendo 541 competencias realizadas en el mundo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
