<?php

namespace Views\Core\Home\Components\MainComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;
use Views\Core\Home\Components\MenuComponent\MenuComponent;
use Views\Core\Home\Components\HeaderComponent\HeaderComponent;

class MainComponent extends CoreComponent 
{

  use StringMethods;
  protected $config;
  protected $JS_PATHS = [

  ];

  protected $JS_PATHS_WITH_ARG = [ ];

  protected $CSS_PATHS = [

  ];

  public function __construct( $config)
  {

    $this->config = $config;
  }


  protected function component(): string
  {
    $this->JS_PATHS_WITH_ARG[] = [

      new ScriptCoreDTO("assets/js/home/home.js?v=1", [
        "hello" => "Word"
      ])

    ];

    $MenuComponent = (new MenuComponent([]))->render();
    $HeaderComponent = (new HeaderComponent([]))->render();

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
          <link rel="shortcut icon" href="./assets/favicon.ico" type="image/x-icon">
          <!-- Solo necesitamos una versión de Babel -->
          
          <!-- Universal Theme Initialization -->
          <script src="./assets/js/core/universal-theme-init.js"></script>

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
          
          <script type="module" src="./assets/js/core/base-lego-framework.js" defer></script>
          <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>


      </body>

      </html>

     
    HTML;

  }
}
