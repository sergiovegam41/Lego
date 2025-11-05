<?php

namespace Components\App\Analysis\Acerca;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/analysis/acerca', methods: ['GET'])]
class AnalysisAcercaComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./acerca.css"];
    protected $JS_PATHS = [];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-acerca">
            <div class="analysis-acerca__container">
                <!-- Header Section -->
                <div class="analysis-acerca__header">
                    <h1 class="analysis-acerca__title">Acerca de la Solución</h1>
                    <p class="analysis-acerca__intro">
                        Este módulo pretende exponer y justificar el diseño de la solución técnica creada para este problema
                    </p>
                </div>

                <!-- Arquitectura Section -->
                <div class="analysis-acerca__section">
                    <h2 class="analysis-acerca__section-title">Arquitectura</h2>
                    <div class="analysis-acerca__board">
                        <iframe
                            width="100%"
                            height="632"
                            src="https://miro.com/app/live-embed/uXjVPLndMU8=/?moveToViewport=-947,-219,1775,888&embedId=357866847762"
                            frameborder="0"
                            scrolling="no"
                            allowfullscreen
                            class="analysis-acerca__iframe"
                        ></iframe>
                    </div>
                </div>

                <!-- Componentes Section -->
                <div class="analysis-acerca__section">
                    <h2 class="analysis-acerca__section-title">Componentes</h2>
                    <p class="analysis-acerca__description">
                        Para diseñar esta solución web fue necesario implementar algunas componentes como:
                    </p>

                    <div class="analysis-acerca__components">
                        <!-- Bases de datos -->
                        <div class="analysis-acerca__component-card">
                            <div class="analysis-acerca__component-icon">
                                <ion-icon name="server-outline"></ion-icon>
                            </div>
                            <h3 class="analysis-acerca__component-title">Bases de datos</h3>
                            <p class="analysis-acerca__component-desc">
                                Usada con el propósito de almacenar y mantener la disponibilidad y/o accesibilidad de los datos usados para esta solución.
                            </p>
                        </div>

                        <!-- Cache -->
                        <div class="analysis-acerca__component-card">
                            <div class="analysis-acerca__component-icon">
                                <ion-icon name="flash-outline"></ion-icon>
                            </div>
                            <h3 class="analysis-acerca__component-title">Cache</h3>
                            <p class="analysis-acerca__component-desc">
                                Usada con el propósito de obtener los resultados del análisis de forma más rápida y accesible.
                            </p>
                        </div>

                        <!-- APIs -->
                        <div class="analysis-acerca__component-card">
                            <div class="analysis-acerca__component-icon">
                                <ion-icon name="cloud-outline"></ion-icon>
                            </div>
                            <h3 class="analysis-acerca__component-title">APIs</h3>
                            <p class="analysis-acerca__component-desc">
                                Creamos APIs públicas para obtener los resultados en cachés que serán consumidos por el front-end.
                            </p>
                        </div>

                        <!-- Jobs -->
                        <div class="analysis-acerca__component-card">
                            <div class="analysis-acerca__component-icon">
                                <ion-icon name="time-outline"></ion-icon>
                            </div>
                            <h3 class="analysis-acerca__component-title">Jobs</h3>
                            <p class="analysis-acerca__component-desc">
                                Este se encargará de procesar, analizar y almacenar los datos en cache cada 30 minutos, con el objetivo de mantener los resultados del análisis actualizados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
