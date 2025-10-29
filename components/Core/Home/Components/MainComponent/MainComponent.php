<?php

namespace Components\Core\Home\Components\MainComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;
use Components\Core\Home\Components\MenuComponent\MenuComponent;
use Components\Core\Home\Components\HeaderComponent\HeaderComponent;
use Components\Core\Home\Collections\MenuItemCollection;
use Components\Core\Home\Dtos\MenuItemDto;

/**
 * MainComponent - Layout principal de la aplicación SPA
 *
 * PROPÓSITO:
 * Renderiza el layout completo de la aplicación incluyendo:
 * - MenuComponent (sidebar)
 * - HeaderComponent (barra superior)
 * - Contenedor principal (#home-page) para módulos dinámicos
 *
 * Este es un componente de página completa (retorna HTML con DOCTYPE)
 */
class MainComponent extends CoreComponent
{
    use StringMethods;

    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("assets/js/home/home.js?v=1", [
                "hello" => "Word"
            ])
        ];

        $HOST_NAME = env('HOST_NAME');

        // Crear el menú con la nueva API
        $MenuComponent = (new MenuComponent(
            options: new MenuItemCollection(
                new MenuItemDto(
                    id: "1",
                    name: "Inicio",
                    url: $HOST_NAME . '/component/inicio',
                    iconName: "home-outline"
                ),
                new MenuItemDto(
                    id: "2",
                    name: "Tablero",
                    url: $HOST_NAME . '/tablero',
                    iconName: "grid-outline"
                ),
                new MenuItemDto(
                    id: "3",
                    name: "Actividades recientes",
                    url: $HOST_NAME . '/actividades',
                    iconName: "time-outline"
                ),
                new MenuItemDto(
                    id: "4",
                    name: "Configuración",
                    url: "#",
                    iconName: "settings-outline",
                    childs: [
                        new MenuItemDto(
                            id: "5",
                            name: "Reportes",
                            url: $HOST_NAME . '/reportes',
                            iconName: "stats-chart-outline"
                        )
                    ]
                ),
                new MenuItemDto(
                    id: "6",
                    name: "Automatización",
                    url: $HOST_NAME . '/component/automation',
                    iconName: "flash-outline"
                ),
                new MenuItemDto(
                    id: "8",
                    name: "Forms Showcase",
                    url: $HOST_NAME . '/component/forms-showcase',
                    iconName: "create-outline"
                ),
                new MenuItemDto(
                    id: "9",
                    name: "Table Showcase",
                    url: $HOST_NAME . '/component/table-showcase',
                    iconName: "grid-outline"
                ),
                new MenuItemDto(
                    id: "10",
                    name: "Products CRUD",
                    url: $HOST_NAME . '/component/products-crud',
                    iconName: "cube-outline"
                ),
                new MenuItemDto(
                    id: "11",
                    name: "Products CRUD V2",
                    url: $HOST_NAME . '/component/products-crud-v2',
                    iconName: "cube-outline",
                    badge: "New"
                )
            ),
            title: "Lego",
            subtitle: "Framework",
            icon: "menu-outline",
            searchable: true,
            resizable: true
        ))->render();

        $HeaderComponent = (new HeaderComponent())->render();

    return <<<HTML

      <!DOCTYPE html>
      <html lang="en">
      <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Home</title>
          <link rel="stylesheet" href="./assets/css/core/base.css">
          <link rel="stylesheet" href="./assets/css/core/windows-manager.css">
          <link rel="stylesheet" href="./assets/css/core/alert-service.css">
          <link rel="shortcut icon" href="./assets/favicon.ico" type="image/x-icon">

          <!-- FilePond CSS -->
          <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
          <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet"/>

          <!-- Solo necesitamos una versión de Babel -->

          <!-- Universal Theme Initialization -->
          <script src="./assets/js/core/universal-theme-init.js"></script>

          <!-- Alert Service -->
          <script src="./assets/js/services/AlertService.js"></script>

          <!-- LEGO Modular Services (Bloques) -->
          <script src="./assets/js/core/services/ApiClient.js"></script>
          <script src="./assets/js/core/services/StateManager.js"></script>
          <script src="./assets/js/core/services/ValidationEngine.js"></script>
          <script src="./assets/js/core/services/TableManager.js"></script>
          <script src="./assets/js/core/services/FormBuilder.js"></script>

      </head>
      <body>
          

          {$MenuComponent}

          {$HeaderComponent}

          <div id="parent-content" >

            <div id="content-sidebar-shade"> 
            
              <!-- esto etsa puesto para hacer la 'sombra' del sidebar y que el contenido se adapte -->

            </div>

            <div id="principal-content-viwer"> 
              
              <div id="home-page">
       

              </div>

            </div>
              
          </div>
          
          <!-- FilePond JS - Cargar antes de base-lego-framework -->
          <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
          <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
          <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
          <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
          <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

          <script type="module" src="./assets/js/core/base-lego-framework.js" defer></script>
          <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>

      </body>

      </html>

     
    HTML;

  }
}
