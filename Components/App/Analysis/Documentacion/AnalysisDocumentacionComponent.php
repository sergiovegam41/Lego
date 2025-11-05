<?php

namespace Components\App\Analysis\Documentacion;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/analysis/documentacion', methods: ['GET'])]
class AnalysisDocumentacionComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./documentacion.css"];
    protected $JS_PATHS = [];

    protected function component(): string
    {
        return <<<HTML
        <div class="analysis-documentacion">
            <div class="analysis-documentacion__container">
                <h1 class="analysis-documentacion__title">Documentación</h1>

                <div class="analysis-documentacion__grid">
                    <!-- ABP -->
                    <div class="analysis-documentacion__card" onclick="window.open('https://docs.google.com/document/d/1_7y6Gj3b0hYy3blNysYG78nyhKIm5y-HAx2-6z4IRe8/edit?tab=t.0', '_blank')">
                        <div class="analysis-documentacion__icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </div>
                        <p class="analysis-documentacion__doc-title">ABP</p>
                        <a href="https://docs.google.com/document/d/1_7y6Gj3b0hYy3blNysYG78nyhKIm5y-HAx2-6z4IRe8/edit?tab=t.0" target="_blank" class="analysis-documentacion__link">
                            Ver documento
                        </a>
                    </div>

                    <!-- Caso de Uso Estadístico -->
                    <div class="analysis-documentacion__card" onclick="window.open('./assets/analysis-docs/uso.pdf', '_blank')">
                        <div class="analysis-documentacion__icon">
                            <ion-icon name="document-outline"></ion-icon>
                        </div>
                        <p class="analysis-documentacion__doc-title">Caso de uso estadístico - Diagrama de caso de uso</p>
                        <a href="./assets/analysis-docs/uso.pdf" target="_blank" class="analysis-documentacion__link">
                            Ver documento
                        </a>
                    </div>

                    <!-- Manual de Empleado -->
                    <div class="analysis-documentacion__card" onclick="window.open('./assets/analysis-docs/empleado.pdf', '_blank')">
                        <div class="analysis-documentacion__icon">
                            <ion-icon name="document-outline"></ion-icon>
                        </div>
                        <p class="analysis-documentacion__doc-title">Manual de empleado</p>
                        <a href="./assets/analysis-docs/empleado.pdf" target="_blank" class="analysis-documentacion__link">
                            Ver documento
                        </a>
                    </div>

                    <!-- Margen Estadístico de Error -->
                    <div class="analysis-documentacion__card" onclick="window.open('https://docs.google.com/document/d/1_x6GBx6D-q0REDOcTrIgH3MUu6szTtpVhbBB_unNmQ0/edit', '_blank')">
                        <div class="analysis-documentacion__icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </div>
                        <p class="analysis-documentacion__doc-title">Margen estadístico de error</p>
                        <a href="https://docs.google.com/document/d/1_x6GBx6D-q0REDOcTrIgH3MUu6szTtpVhbBB_unNmQ0/edit" target="_blank" class="analysis-documentacion__link">
                            Ver documento
                        </a>
                    </div>

                    <!-- Manual de Uso -->
                    <div class="analysis-documentacion__card" onclick="window.open('./assets/analysis-docs/manual-uso.pdf', '_blank')">
                        <div class="analysis-documentacion__icon">
                            <ion-icon name="document-outline"></ion-icon>
                        </div>
                        <p class="analysis-documentacion__doc-title">Manual de uso</p>
                        <a href="./assets/analysis-docs/manual-uso.pdf" target="_blank" class="analysis-documentacion__link">
                            Ver documento
                        </a>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}
