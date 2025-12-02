<?php

namespace Components\Core\Home\Components\MainComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Providers\StringMethods;
use Components\Core\Home\Components\MenuComponent\MenuComponent;
use Components\Core\Home\Components\HeaderComponent\HeaderComponent;
use Components\Core\Home\Collections\MenuItemCollection;
use Components\Core\Home\Dtos\MenuItemDto;
use App\Models\MenuItem;

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

    /**
     * Constructor vacío intencional.
     * 
     * RAZÓN ARQUITECTÓNICA:
     * MainComponent es el layout principal SPA que renderiza HTML completo (DOCTYPE).
     * Carga el menú desde base de datos internamente y no requiere configuración
     * externa porque es el contenedor raíz de toda la aplicación autenticada.
     */
    public function __construct() {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("assets/js/home/home.js?v=1", [
                "hello" => "Word"
            ])
        ];

        $HOST_NAME = env('HOST_NAME');

        // Cargar menú desde base de datos
        $menuItems = $this->buildMenuFromDatabase();

        // Crear el menú con items desde DB
        $MenuComponent = (new MenuComponent(
            options: $menuItems,
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
          <link rel="stylesheet" href="./assets/css/core/confirmation-service.css">
          <link rel="shortcut icon" href="./assets/favicon.ico" type="image/x-icon">

          <!-- ═══════════════════════════════════════════════════════════════
               LEGO FRAMEWORK - SISTEMA DE CARGA DE ASSETS
               
               ORDEN DE CARGA (IMPORTANTE):
               1. CSS Base + Plugins externos
               2. Theme Init (previene FOUC)
               3. Servicios Core (UI feedback)
               4. Servicios Modulares (opcionales, cargados bajo demanda)
               ═══════════════════════════════════════════════════════════════ -->

          <!-- ═══ CSS: Plugins Externos ═══ -->
          <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
          <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet"/>

          <!-- ═══ BOOTSTRAP: Theme Init (CRÍTICO - previene flash) ═══ -->
          <script src="./assets/js/core/universal-theme-init.js"></script>

          <!-- ═══ SERVICIOS CORE UI: Siempre necesarios para feedback ═══ -->
          <script src="./assets/js/services/AlertService.js"></script>
          <script src="./assets/js/services/ConfirmationService.js"></script>

          <!-- ═══ SERVICIOS MODULARES: Disponibles globalmente ═══ -->
          <!-- Estos servicios se cargan síncronamente porque son usados
               por componentes dinámicos que los necesitan inmediatamente -->
          <script src="./assets/js/core/services/StateManager.js"></script>
          <script src="./assets/js/core/services/ValidationEngine.js"></script>
          <script src="./assets/js/core/services/TableManager.js"></script>
          <script src="./assets/js/core/services/FormBuilder.js"></script>

          <!-- ═══ COMPONENT CONTEXT: Elimina magic strings en JS ═══ -->
          <!-- Permite que JS conozca el contexto del componente actual
               (rutas, APIs, menú padre) sin valores hardcodeados -->
          <script src="./assets/js/core/component-context.js"></script>

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
          
          <!-- ═══════════════════════════════════════════════════════════════
               SCRIPTS DE CIERRE (antes de </body>)
               
               ORDEN:
               1. Plugins externos (FilePond)
               2. Sistema de eventos LEGO
               3. Framework base (SPA, módulos dinámicos)
               4. Iconos
               ═══════════════════════════════════════════════════════════════ -->

          <!-- ═══ PLUGINS EXTERNOS: FilePond ═══ -->
          <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
          <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
          <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
          <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
          <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

          <!-- ═══ LEGO CORE: Sistema de eventos (requerido por framework) ═══ -->
          <script src="./assets/js/core/modules/events/lego-events.js"></script>

          <!-- ═══ LEGO FRAMEWORK: SPA y carga dinámica de módulos ═══ -->
          <script type="module" src="./assets/js/core/base-lego-framework.js?v=<?= time() ?>" defer></script>

          <!-- ═══ ICONOS: Ionicons ═══ -->
          <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>

      </body>

      </html>

     
    HTML;

  }

  /**
   * Construye el menú desde la base de datos
   */
  private function buildMenuFromDatabase(): MenuItemCollection
  {
      try {
          $HOST_NAME = env('HOST_NAME');
          
          // Obtener items raíz desde DB
          $rootItems = MenuItem::root()->visible()->get();
          
          // Debug: verificar si hay items
          if ($rootItems->isEmpty()) {
              error_log('[MainComponent] No se encontraron items de menú en la base de datos');
              // Retornar menú por defecto si no hay datos
              return $this->getDefaultMenu();
          }
          
          error_log('[MainComponent] Encontrados ' . $rootItems->count() . ' items raíz');
          
          // Convertir a MenuItemDto
          $menuDtos = [];
          foreach ($rootItems as $item) {
              $menuDtos[] = $this->buildMenuItemDto($item, $HOST_NAME);
          }
          
          return new MenuItemCollection(...$menuDtos);
      } catch (\Exception $e) {
          error_log('[MainComponent] Error cargando menú desde DB: ' . $e->getMessage());
          // Retornar menú por defecto en caso de error
          return $this->getDefaultMenu();
      }
  }
  
  /**
   * Construye un MenuItemDto desde un MenuItem (recursivo para hijos)
   */
  private function buildMenuItemDto(MenuItem $item, string $hostName): MenuItemDto
  {
      // Obtener hijos
      $children = $item->children()->visible()->get();
      $childDtos = [];
      
      foreach ($children as $child) {
          $childDtos[] = $this->buildMenuItemDto($child, $hostName);
      }
      
      // Determinar el nombre a mostrar
      $displayName = $item->hasChildren() && $item->index_label 
          ? $item->index_label 
          : $item->label;
      
      // Si no tiene ruta, es un grupo (url = null)
      // Si tiene hijos, es un grupo (url = null)
      // Si tiene ruta y no tiene hijos, es un item con link
      $url = null;
      if (!$item->hasChildren() && !empty($item->route)) {
          $url = $hostName . $item->route;
      }
      
      return new MenuItemDto(
          id: $item->id,
          name: $displayName,
          url: $url,
          iconName: $item->icon,
          childs: $childDtos  // Ya es array vacío [] si no hay hijos
      );
  }
  
  /**
   * Menú por defecto (fallback si DB falla o está vacía)
   */
  private function getDefaultMenu(): MenuItemCollection
  {
      $HOST_NAME = env('HOST_NAME');
      
      return new MenuItemCollection(
          new MenuItemDto(
              id: "inicio",
              name: "Inicio",
              url: $HOST_NAME . '/component/inicio',
              iconName: "home-outline"
          ),
          new MenuItemDto(
              id: "example-crud",
              name: "Example CRUD",
              url: $HOST_NAME . '/component/example-crud',
              iconName: "cube-outline"
          ),
          new MenuItemDto(
              id: "todo",
              name: "TODO List",
              url: $HOST_NAME . '/component/todo',
              iconName: "checkbox-outline"
          )
      );
  }
}
