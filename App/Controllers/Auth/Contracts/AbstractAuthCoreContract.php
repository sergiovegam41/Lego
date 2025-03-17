<?php

namespace App\Controllers\Auth\Contracts;

use App\Controllers\Auth\DTOs\AuthRequestDTO;
use Core\Models\ResponseDTO;

abstract class AbstractAuthCoreContract implements AuthCoreContract {


    public function __construct() { 
    
        if (!defined('static::AUTH_GROUP_NAME')) {
            throw new \Exception('The constant AUTH_GROUP_NAME must be defined in the class ' . get_class($this));
        }
     
    }
   
    abstract public function login(AuthRequestDTO $request): ResponseDTO;
    abstract public function refresh_token(AuthRequestDTO $request): ResponseDTO;
    abstract public function logout(AuthRequestDTO $request): ResponseDTO;
    abstract public function register(AuthRequestDTO $request): ResponseDTO;



}
