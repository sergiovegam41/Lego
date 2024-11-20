<?php
namespace Core\Controller;

use Rakit\Validation\Validator;

abstract class CoreViewController{
    
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
}