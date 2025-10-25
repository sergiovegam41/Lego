<?php

namespace Components\Core\Home;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent
{

    protected $CSS_PATHS = ["./home.css"];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./home.js", [])
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

   
    // public function apiGet($request = null): array
    // {
     
    //     return Response::uri( $this->render() );
    // }

 
}