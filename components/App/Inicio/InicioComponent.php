<?php

namespace Components\App\Inicio;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;

#[ApiComponent('/inicio', methods: ['GET'])]
class InicioComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./inicio.css"];
    protected $JS_PATHS = [];

    protected function component(): string
    {
        return <<<HTML
        <div class="inicio">
            <div class="inicio__container">
                <!-- Hero Section -->
                <div class="inicio__hero">
                    <div class="inicio__hero-icon">
                        <ion-icon name="analytics-outline"></ion-icon>
                    </div>
                    <h1 class="inicio__title">Análisis de Fútbol</h1>
                    <p class="inicio__subtitle">Sistema de análisis estadístico de competencias deportivas</p>
                </div>

                <!-- Features Grid -->
                <div class="inicio__features">
                    <div class="inicio__feature-card">
                        <div class="inicio__feature-icon">
                            <ion-icon name="football-outline"></ion-icon>
                        </div>
                        <h3 class="inicio__feature-title">Estadios</h3>
                        <p class="inicio__feature-desc">Análisis de uso y distribución de estadios</p>
                    </div>

                    <div class="inicio__feature-card">
                        <div class="inicio__feature-icon">
                            <ion-icon name="trophy-outline"></ion-icon>
                        </div>
                        <h3 class="inicio__feature-title">Ligas</h3>
                        <p class="inicio__feature-desc">Competencias y torneos analizados</p>
                    </div>

                    <div class="inicio__feature-card">
                        <div class="inicio__feature-icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <h3 class="inicio__feature-title">Managers</h3>
                        <p class="inicio__feature-desc">Participación de entrenadores</p>
                    </div>

                    <div class="inicio__feature-card">
                        <div class="inicio__feature-icon">
                            <ion-icon name="earth-outline"></ion-icon>
                        </div>
                        <h3 class="inicio__feature-title">Países</h3>
                        <p class="inicio__feature-desc">Distribución geográfica mundial</p>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="inicio__info">
                    <div class="inicio__info-card">
                        <ion-icon name="time-outline"></ion-icon>
                        <div class="inicio__info-content">
                            <h4>Actualizaciones automáticas</h4>
                            <p>Los datos se actualizan cada 30 minutos mediante jobs programados</p>
                        </div>
                    </div>

                    <div class="inicio__info-card">
                        <ion-icon name="flash-outline"></ion-icon>
                        <div class="inicio__info-content">
                            <h4>Alto rendimiento</h4>
                            <p>Sistema de caché Redis para respuestas rápidas</p>
                        </div>
                    </div>

                    <div class="inicio__info-card">
                        <ion-icon name="server-outline"></ion-icon>
                        <div class="inicio__info-content">
                            <h4>Base de datos robusta</h4>
                            <p>MongoDB para almacenamiento escalable de datos</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="inicio__cta">
                    <h2>Comienza a explorar los datos</h2>
                    <p>Selecciona una categoría del menú lateral para ver análisis detallados</p>
                </div>
            </div>
        </div>
        HTML;
    }
}
