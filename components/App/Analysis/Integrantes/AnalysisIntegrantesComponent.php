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
                    <!-- Sergio Vega -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Sergio Vega</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Justin Hernandez -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Justin Hernandez</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Julio Bonifacio -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Julio Bonifacio</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>

                    <!-- Andres Rodrigez -->
                    <div class="analysis-integrantes__card">
                        <div class="analysis-integrantes__avatar">
                            <ion-icon name="person-circle-outline"></ion-icon>
                        </div>
                        <p class="analysis-integrantes__name">Andres Rodrigez</p>
                        <strong><p class="analysis-integrantes__role">Developer</p></strong>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
