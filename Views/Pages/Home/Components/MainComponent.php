<?php

namespace Views\Pages\Home\Components;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;

class MainComponent extends CoreComponent 
{

  use StringMethods;
  protected $config;
  protected $JS_PATHS = [

  ];

  protected $JS_PATHS_WITH_ARG = [

  ];

  protected $CSS_PATHS = [
    "assets/css/base.css"
  ];

  public function __construct( $config)
  {

    $this->config = $config;
  }

  public function renderAll(): string
  {

    $component   = $this->component();
    $css_imports = $this->css_imports();
    $js_imports  = $this->js_imports();
    $js_imports_with_arg  = $this->js_imports_with_arg();

    return <<<HTML

      <!-- dependencias css -->
      {$css_imports}

      <!-- cuerpo del componente -->
      {$component}

      <!-- dependencias js -->
      {$js_imports}
      
      <!-- dependencias with arg js -->
      {$js_imports_with_arg}

    HTML;
  }

  public function render(): string
  {
    $component = $this->component();
    return $component;
  }

  protected function component(): string
  {
    $this->JS_PATHS_WITH_ARG[] = [

      new ScriptCoreDTO("assets/js/Home/home.js?v=1", [
        "hello" => "Word",
        
      ])

    ];
    return <<<HTML

      <!DOCTYPE html>
      <html lang="en">
      <head>
          <meta charset="UTF-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Home</title> 


      </head>
      <body>
          
          <side-bar> </side-bar>

          <section class="home">

          


              <div class="content" >

                  <img src="assets/images/hi.jpg" alt="">
                  <h1 id="saludo" class="text">  Bienvenido  </h1>
                  <p>Navega por el menu de inicio para empezar.</p>
              
              </div>

            
              
          </section>

          <!-- Incluye funciones base del freamework -->
          <script src="assets/js/Main/sidebar/SidebarScrtipt.js" type="module"></script>
          
          <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>




      </body>

      </html>

     
    HTML;

  }
}
