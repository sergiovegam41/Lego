<?php
namespace Core\Components\CoreComponent;

use Core\Dtos\ScriptCoreDTO;

/**
 * CoreComponent - Clase base para todos los componentes Lego
 *
 * FILOSOFÍA LEGO:
 * Los componentes son bloques reutilizables que se ensamblan de forma declarativa.
 * Cada componente define sus propios parámetros con tipos específicos usando named arguments.
 *
 * CARACTERÍSTICAS:
 * - Named arguments: Parámetros claros y type-safe
 * - Auto-loading de CSS/JS: Define $CSS_PATHS y $JS_PATHS
 * - Rutas relativas: Usa "./file.css" y se resuelve automáticamente
 * - Composición: Los componentes pueden contener otros componentes
 *
 * EJEMPLO:
 * class MenuComponent extends CoreComponent {
 *     protected $CSS_PATHS = ["./menu.css"];
 *
 *     public function __construct(
 *         public MenuItemCollection $options,
 *         public string $title,
 *         public bool $searchable = false
 *     ) {}
 *
 *     protected function component(): string {
 *         return "<div>...</div>";
 *     }
 * }
 */
abstract class CoreComponent {

    protected $JS_PATHS = [];

    /**  @var ScriptCoreDTO[] */
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    /**
     * Children components - para composición tipo LEGO
     * @var array<CoreComponent|string>
     */
    protected array $children = [];

    // Los componentes hijos definen su propio constructor con named arguments
    // No hay constructor obligatorio aquí

    /**
     * Resuelve rutas relativas basadas en la ubicación del componente
     * ./file.css -> components/Core/Home/file.css
     * ../shared/utils.js -> components/Core/shared/utils.js
     */
    private function resolveRelativePath($path) {
        // Si no es ruta relativa, devolver tal como está
        if (!str_starts_with($path, './') && !str_starts_with($path, '../')) {
            return $path;
        }

        // Obtener la clase del componente actual
        $reflection = new \ReflectionClass(static::class);
        $classPath = $reflection->getFileName();
        
        // Extraer la ruta del componente relativa a components/
        // Ejemplo: /path/to/components/Core/Home/HomeComponent.php -> Core/Home
        if (preg_match('/components[\/\\\\](.+)[\/\\\\][^\/\\\\]+Component\.php$/', $classPath, $matches)) {
            $componentDir = str_replace(['/', '\\'], '/', $matches[1]);
            $basePath = "components/" . $componentDir . "/";
            
            // Resolver la ruta relativa
            if (str_starts_with($path, './')) {
                return $basePath . substr($path, 2);
            } elseif (str_starts_with($path, '../')) {
                $parentDir = dirname($componentDir);
                if ($parentDir === '.') $parentDir = '';
                else $parentDir = $parentDir . '/';
                return "components/" . $parentDir . substr($path, 3);
            }
        }
        
        return $path;
    }

    abstract protected function component(): string;

    
    protected function css_imports(): string {
        // Resolver rutas relativas en CSS_PATHS
        $cssDependencies = array_map([$this, 'resolveRelativePath'], $this->CSS_PATHS);
        return $this->generate_imports($cssDependencies, 'css');
    }
    
    protected function js_imports(): string {
        // Resolver rutas relativas en JS_PATHS
        $jsDependencies = array_map([$this, 'resolveRelativePath'], $this->JS_PATHS);
        // Agregar cache buster a cada ruta
        $cacheBuster = time();
        $jsDependencies = array_map(function($path) use ($cacheBuster) {
            return $path . (strpos($path, '?') !== false ? '&' : '?') . 'v=' . $cacheBuster;
        }, $jsDependencies);
        return $this->generate_modulesJs($jsDependencies);
    }
  
    protected function js_imports_with_arg() {
        return $this->generate_modulesJsWithArg();
    }

    private function generate_modulesJsWithArg(){
        
        global $url_servidor, $id_usuario_actual;

        // Resolver rutas relativas en JS_PATHS_WITH_ARG
        $resolvedJsPaths = [];
        $cacheBuster = time(); // Usar timestamp para forzar recarga
        foreach ($this->JS_PATHS_WITH_ARG as $scriptArray) {
            $resolvedArray = [];
            foreach ($scriptArray as $scriptDto) {
                if ($scriptDto instanceof ScriptCoreDTO) {
                    // Crear una copia para no modificar el original
                    $resolvedPath = $this->resolveRelativePath($scriptDto->path);
                    // Agregar cache buster a la ruta
                    $resolvedPath .= (strpos($resolvedPath, '?') !== false ? '&' : '?') . 'v=' . $cacheBuster;
                    $resolvedDto = new ScriptCoreDTO(
                        $resolvedPath,
                        $scriptDto->arg
                    );
                    $resolvedArray[] = $resolvedDto;
                } else {
                    $resolvedArray[] = $scriptDto;
                }
            }
            $resolvedJsPaths[] = $resolvedArray;
        }

        $modules = json_encode([
            "context"=>[
                "url_servidor" => $url_servidor,
                "id_usuario_actual" => $id_usuario_actual
            ],
            "data"=> $resolvedJsPaths
        ]);

        if( $this->JS_PATHS_WITH_ARG == [] ){
            return "";
        }

        return <<<HTML
         <script>
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                window.lego.loadModulesWithArguments({$modules});
            } else {
                window.addEventListener('load', ()=>{
                    window.lego.loadModulesWithArguments({$modules});
                });
            }
        </script>
        HTML;
    }


    
    

    /**  @var $dependencies ScriptCoreDTO[] */
    private function generate_modulesJs(array $dependencies){

        $modules = json_encode($dependencies);

        if( $dependencies == [] ){
            return "";
        }

        return <<<HTML
            <script>

                if (document.readyState === 'complete' || document.readyState === 'interactive') {

                   window.lego.loadModules({$modules})
                
                }else{
                
                    window.addEventListener('load',()=>window.lego.loadModules({$modules}));
                
                }
            
            </script>
        HTML;
    }

    private function generate_imports(array $dependencies, string $type): string {

        $html_result = "";
        $r = uniqid();
        foreach ($dependencies as $path) {
            if ($type == 'css') {


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

    /**
     * Renderiza los componentes children
     * Soporta:
     * - CoreComponent instances (llama a ->render())
     * - Strings (HTML directo)
     * - Arrays de children (recursivo)
     *
     * @return string HTML renderizado de todos los children
     */
    protected function renderChildren(): string
    {
        return implode('', array_map(
            fn($child) => match(true) {
                $child instanceof CoreComponent => $child->render(),
                is_array($child) => implode('', array_map(
                    fn($c) => $c instanceof CoreComponent ? $c->render() : (string)$c,
                    $child
                )),
                default => (string)$child
            },
            $this->children
        ));
    }

    public function render(): string
    {    

      $component = $this->component();
      $css_imports = $this->css_imports();
      $js_imports  = $this->js_imports();
      $js_imports_with_arg  = $this->js_imports_with_arg();
    
      return <<<HTML
  
  
        {$css_imports}
  

        {$component}
  

        {$js_imports}
        

        {$js_imports_with_arg}
  
      HTML;
    }
  
    public function html(): string
    {
      $component = $this->component();
      return $component;
    }
}