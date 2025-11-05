<?php

namespace Components\Core\Home;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Response;

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
       
        $HOST_NAME = env('HOST_NAME');

        return <<<HTML
        <div class="dashboard-container">
            <div class="welcome-header">
                <h1>¡Bienvenido a FloraFresh!</h1>
                <p>Sistema de gestión de florería</p>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card" data-module-id="flowers" data-module-url="{$HOST_NAME}/component/flowers">
                    <div class="card-icon" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                        <ion-icon name="flower-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Flores</h3>
                        <p>Gestionar catálogo de productos</p>
                    </div>
                </div>

                <div class="dashboard-card" data-module-id="categories" data-module-url="{$HOST_NAME}/component/categories">
                    <div class="card-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <ion-icon name="folder-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Categorías</h3>
                        <p>Organizar productos por tipo</p>
                    </div>
                </div>

                <div class="dashboard-card" data-module-id="featured-products" data-module-url="{$HOST_NAME}/component/featured-products">
                    <div class="card-icon" style="background: rgba(234, 179, 8, 0.1); color: #eab308;">
                        <ion-icon name="star-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Productos Destacados</h3>
                        <p>Flores populares y promociones</p>
                    </div>
                </div>

                <div class="dashboard-card" data-module-id="testimonials" data-module-url="{$HOST_NAME}/component/testimonials">
                    <div class="card-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                        <ion-icon name="chatbubble-ellipses-outline"></ion-icon>
                    </div>
                    <div class="card-content">
                        <h3>Testimonios</h3>
                        <p>Opiniones de clientes</p>
                    </div>
                </div>
            </div>

            <!-- <div class="quick-actions">
                <h2>Accesos Rápidos</h2>
                <div class="actions-grid">
                    <button class="action-button action-primary" data-module-id="flowers-create" data-module-url="{$HOST_NAME}/component/flowers/create">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        <span>Nueva Flor</span>
                    </button>
                    <button class="action-button action-secondary" data-module-id="categories-create" data-module-url="{$HOST_NAME}/component/categories/create">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        <span>Nueva Categoría</span>
                    </button>
                    <button class="action-button action-accent" data-module-id="featured-products-create" data-module-url="{$HOST_NAME}/component/featured-products/create">
                        <ion-icon name="star-outline"></ion-icon>
                        <span>Destacar Producto</span>
                    </button>
                    <button class="action-button action-success" data-module-id="testimonials-create" data-module-url="{$HOST_NAME}/component/testimonials/create">
                        <ion-icon name="chatbubble-outline"></ion-icon>
                        <span>Nuevo Testimonio</span>
                    </button>
                </div>
            </div> -->
        </div>
        HTML;
    }

   
    // public function get($request = null): array
    // {
    //     p("get HomeComponent");
    //     return Response::uri( $this->render() );
    // }

 
}