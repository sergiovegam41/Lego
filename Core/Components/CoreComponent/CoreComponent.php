<?php
namespace Core\Components\CoreComponent;

use Core\Dtos\ScriptCoreDTO;
use MatthiasMullie\Minify;

abstract class CoreComponent {
    protected $config;
    protected $JS_PATHS = [];


    /**  @var ScriptCoreDTO[] */
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct($config) {
        $this->config = $config;
    }

    abstract protected function component(): string;

    
    protected function css_imports(): string {

        $cssDependencies = $this->CSS_PATHS;
        return $this->generate_imports($cssDependencies, 'css');
    }
    
    protected function js_imports(): string {
        $jsDependencies = $this->JS_PATHS;
        return $this->generate_modulesJs($jsDependencies);
    }
  
    protected function js_imports_with_arg() {

        return $this->generate_modulesJsWithArg();
    }

    private function generate_modulesJsWithArg(){
        
        global $url_servidor, $id_usuario_actual;

        $modules = json_encode([
            "context"=>[
                "url_servidor" => $url_servidor,
                "id_usuario_actual" => $id_usuario_actual
            ],
            "data"=>$this->JS_PATHS_WITH_ARG
        ]);

   
        return <<<HTML
         <script>window.addEventListener('load',()=>window.lego.loadModulesWithArguments({$modules}));</script>
        HTML;
    }


    
    

    /**  @var $dependencies ScriptCoreDTO[] */
    private function generate_modulesJs(array $dependencies){
        global $url_servidor;

        $modules = json_encode($dependencies);
        return <<<HTML
            <script>window.addEventListener('load',()=>window.lego.loadModules({$modules}));</script>
        HTML;
    }

    private function generate_imports(array $dependencies, string $type): string {
        global $url_servidor;

        $html_result = "";
        $r = uniqid();
        foreach ($dependencies as $path) {
            if ($type == 'css') {


// HTML;
            $html_result .= <<<HTML
            
            <link rel="stylesheet" href="{$path}?v={$r}" />
HTML;



            } else if ($type == 'js') {
                $html_result  .= <<<HTML
                    
                <script src="{$path}?v={$r}"></script>

                HTML;
            }
        }
        return $html_result;
    }


    public function clearCache()
    {
        $result = [];
        foreach($this->JS_PATHS  as $val){
            $date = date('Y-m-d h:m:s');
            $result[] = $val . "?v=$date";
        }
        
        $this->JS_PATHS = $result;

        $result = [];
        foreach($this->CSS_PATHS  as $val){
            $date = date('Y-m-d h:m:s');
            $result[] = $val . "?v=$date";
        }
        $this->CSS_PATHS = $result;
    }

    public function render(): string
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
  
    public function html(): string
    {
      $component = $this->component();
      return $component;
    }
}