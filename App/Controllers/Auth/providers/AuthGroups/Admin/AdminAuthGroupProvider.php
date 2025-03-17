<?php
namespace App\Controllers\Auth\Providers\AuthGroups\Admin;

use App\Controllers\Auth\Contracts\AbstractAuthCoreContract;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use Core\Models\ResponseDTO;

class AdminAuthGroupProvider extends AbstractAuthCoreContract 
{

    public const AUTH_GROUP_NAME = "admins";

    public function login(AuthRequestDTO $request): ResponseDTO
    {
        p($request,"AdminAuthGroupProvider" );
        
        return new ResponseDTO(false, "error", null);
    }
    
    public function refresh_token(AuthRequestDTO $request): ResponseDTO
    {

        p($request);

        return new ResponseDTO(false, "error", null);
    }
    
    public function logout(AuthRequestDTO $request): ResponseDTO
    {

        p($request);

        return new ResponseDTO(false, "error", null);
    }

    public function register(AuthRequestDTO $request): ResponseDTO
    {

        p($request);

        return new ResponseDTO(false, "error", null);
    }

}
