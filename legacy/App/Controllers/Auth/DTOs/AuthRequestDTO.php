<?php

namespace App\Controllers\Auth\DTOs;

use Core\Providers\Request;

class AuthRequestDTO {
    public function __construct( 
        public string $auth_grup_name,
        public string $auth_accion,
        public Request $request
    ) { }

}

