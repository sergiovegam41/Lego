<?php
namespace App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares;

use Core\Helpers\LegoHelpers;

class AdminMiddlewares {
   
    public static function isAutenticated(): bool {

        $autenticated = LegoHelpers::isAutenticated();

        if($autenticated->success){
            return true;
        } 

        LegoHelpers::redirect('login');
        return false;
        
    }
}
