<?php

namespace Components\App\Analysis\Integrantes;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/analysis/integrantes', methods: ['GET'])]
class AnalysisIntegrantesComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./integrantes.css"];
    protected $JS_PATHS = [];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-integrantes">
            <div class="analysis-integrantes__container">
                <h1 class="analysis-integrantes__title">Integrantes</h1>

                <div class="analysis-integrantes__grid">
                    <!-- Sergio Luis Vega Martínez -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Sergio Luis Vega Martínez</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Justin Hernández -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Justin Hernández</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Julio Bonifacio Martínez -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Julio Bonifacio Martínez</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Andrés David Rodríguez Camargo -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Andrés David Rodríguez Camargo</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
