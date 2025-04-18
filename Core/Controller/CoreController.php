<?php
namespace Core\Controller;

use App\Controllers\Auth\Controllers\AuthGroupsController;
use Core\Contracts\CoreControllerContract;
use Core\Models\ResponseDTO;
use Core\Response;
use Exception;
use Rakit\Validation\Validator;

abstract class CoreController implements CoreControllerContract {
    
    protected $arrayMethods = ['get','put','delete','post'];


    public function getMethod($request,$accion){
        
        return $this->validateMethod($accion)?$this->validateMethod($accion):$this->$accion($request);

    }

    private function validateMethod($accion){

        if(!in_array($accion,$this->arrayMethods))
        {
            return (['status'=>'error','msg'=>'accion no permitida']);
        }else{
            return false;
        }

    }


    static function mapControllers()
    {
        $rutaControllers = __DIR__ . '/../../App/Controllers';

        $arrayCarpetas = self::getListNamesByDir($rutaControllers);

        $objectRoutes = [];

        foreach ($arrayCarpetas as $carpeta) {
            $archivosControladores = self::getListNamesByDir("$rutaControllers/$carpeta/Controllers");
        
            foreach ($archivosControladores as $archivo) {
                $nombreClase = str_replace('.php', '', $archivo);
                $namespaceClase = "\\App\\Controllers\\$carpeta\\Controllers\\$nombreClase";
        
                if (!class_exists($namespaceClase)) {
                    continue;
                }
        
                $ruta = $namespaceClase::ROUTE ?? null;
        
                if ($ruta === null) {
                    continue;
                }
        
                $ruta = $ruta === '' ? str_replace('Controller', '', $nombreClase) : $ruta;
        
                $objectRoutes[$ruta] = $namespaceClase;
            }
        }
        

        return $objectRoutes;
    }

    static function getMymapControllers() : array
    {
        
        try {
            
            $rutaControlles = file_get_contents(__DIR__ . '/../../routeMap.json');

            $listVas = json_decode($rutaControlles,true);

            return $listVas;

        } catch (Exception $e) {

            Response::json(500,(array)new ResponseDTO(false,'Error al mapear rutas',$e));

        }
    }

    private static function getListNamesByDir(string $dir){

        $listDir = [];

        if(!is_dir($dir)) return $listDir;

        return array_diff(scandir($dir),array('.', '..'));

    }
}