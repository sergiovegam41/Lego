<?php
namespace App\Controllers\Auth\Providers\AuthGroups\Admin;

use App\Controllers\Auth\Contracts\AbstractAuthCoreContract;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Constants\AuthGruopsIDs;
use App\Controllers\Auth\Providers\AuthGroupsProvider;
use Core\Models\ResponseDTO;
use Core\Services\AuthServicesCore;
use Flight;
class AdminAuthGroupProvider extends AbstractAuthCoreContract 
{

    public const AUTH_GROUP_NAME = [
        "id"=> AuthGruopsIDs::ADMINS, // una vez definido no debe cambiar nunca el identificador
        "route"=>"admin", // este sera el nombre de la ruta en la url
        "description"=>"destinado a usuarios de administración y backoffice" 
    ];
    public function login(AuthRequestDTO $authRequestDTO): ResponseDTO
    {
   
        $email = $authRequestDTO->request->request['username'];
        $password = $authRequestDTO->request->request['password'];
        $device_id = $authRequestDTO->request->request['device_id']??1;
        $firebase_token = $authRequestDTO->request->request['firebase_token']??null;

        return (new AuthServicesCore())->coreLogin( $email, $password, AdminAuthGroupProvider::AUTH_GROUP_NAME["id"], $device_id,  $firebase_token);

    }

    public function loginByCode(AuthRequestDTO $request): ResponseDTO
    {
        p($request,"AdminAuthGroupProvider loginByCode" );
        
        return new ResponseDTO(false, "error", null);
    }
    
    public function refresh_token(AuthRequestDTO $authRequestDTO): ResponseDTO
    {
        $refresh_token = $authRequestDTO->request->request['refresh_token'];
        $device_id = $authRequestDTO->request->request['device_id']??1;
        return (new AuthServicesCore())->coreRefreshToken(  $refresh_token, $device_id );

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

        return AuthServicesCore::isAutenticated();
    }

}
