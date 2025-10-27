<?php
namespace App\Controllers\Auth\Providers\AuthGroups\Api;

use App\Controllers\Auth\Contracts\AbstractAuthCoreContract;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Constants\AuthGroupsIDs;
use App\Controllers\Auth\Providers\AuthGroupsProvider;
use Core\Models\ResponseDTO;
use Core\Services\AuthServicesCore;

class ApiAuthGroupProvider extends AbstractAuthCoreContract
{

    public const AUTH_GROUP_NAME = [
        "id"=>AuthGroupsIDs::APIS, // una vez definido no debe cambiar nunca el identificador
        "route"=>"api", // este sera el nombre de la ruta en la url
        "description"=>"destinado a usuarios externos y aplicaciones web o moviles" 
    ];

    public function login(AuthRequestDTO $authRequestDTO): ResponseDTO
    {
        $email = $authRequestDTO->request->request['username'];
        $password = $authRequestDTO->request->request['password'];

        return (new AuthServicesCore())->coreLogin( $email, $password, ApiAuthGroupProvider::AUTH_GROUP_NAME["id"], 2);

        return new ResponseDTO(false, "error", null);
    }
    

    public function loginByCode(AuthRequestDTO $request): ResponseDTO
    {
        p($request,"ApiAuthGroupProvider loginByCode" );
        
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
    public function getProfile(AuthRequestDTO $request): ResponseDTO
    {

        p($request);

        return new ResponseDTO(false, "error", null);
    }

}
