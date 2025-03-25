<?php
namespace Core\Helpers;

use Core\Models\ResponseDTO;
use Core\Services\AuthServicesCore;

class LegoHelpers { 

    static function redirect($route){
        
       header("Location: /$route");
       exit();

    }

    static function isAutenticated(): ResponseDTO {
        return AuthServicesCore::isAutenticated(); 
    }

}