<?php

namespace Views\Core\Home;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class HomeComponent extends CoreComponent
{
    protected $config;

    protected $JS_PATHS = [];

    protected $JS_PATHS_WITH_ARG = [];

    protected $CSS_PATHS = ["components/Core/Home/home.css"];

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("components/Core/Home/home.js", [])
        ];
       
        return <<<HTML
        <div class="dashboard-container">
            <div class="welcome-header">
                <h1>¡Bienvenido a Lego!</h1>
                <p>Dashboard de administración</p>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-icon">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Estadísticas</h3>
                        <p>Vista general del sistema</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <ion-icon name="flash-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Automatización</h3>
                        <p>Flujos de trabajo</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <ion-icon name="time-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Actividades</h3>
                        <p>Eventos recientes</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon">
                        <ion-icon name="settings-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Configuración</h3>
                        <p>Ajustes del sistema</p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}