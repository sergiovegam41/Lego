<?php

namespace App\Controllers\Auth\Providers;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Admin\AdminAuthGroupProvider;
use App\Controllers\Auth\Providers\AuthGroups\Api\ApiAuthGroupProvider;
use Core\Models\ResponseDTO;

class AuthGroupsProvider 
{

    private $providers = [];

    public function __construct() {
        // Definir la lista de proveedores de pago
        $this->providers = [

            new AdminAuthGroupProvider(),
            
            new ApiAuthGroupProvider(),
           
        ];

      

    }

    public function handle( AuthRequestDTO $AuthRequestDTO, $user = null ):ResponseDTO{
    
        $accion =  $AuthRequestDTO->auth_accion;

        foreach ($this->providers as $provider) {
            
            if ( defined(get_class($provider) . '::AUTH_GROUP_NAME') && strtolower(constant(get_class($provider) . '::AUTH_GROUP_NAME')['route']) === strtolower( $AuthRequestDTO->auth_grup_name ) ) {
                
                switch ($accion) {

                    case AuthActions::LOGIN:
                        return $this->login($provider, $AuthRequestDTO );
                      break;
                    case AuthActions::LOGIN_BY_CODE:
                        return $this->loginByCode($provider, $AuthRequestDTO );
                      break;
                    case AuthActions::REFRESH_TOKEN:
                        return $this->refresh_token($provider, $AuthRequestDTO, $user);
                      break;
                    case AuthActions::LOGOUT:
                        return $this->logout($provider, $AuthRequestDTO, $user);
                      break;
                    case AuthActions::REGISTER:
                        return $this->register($provider, $AuthRequestDTO, $user);
                      break;
                    case AuthActions::PROFILE:
                        return $this->getProfile($provider, $AuthRequestDTO, $user);
                      break;

                }

            }

        }

        return new ResponseDTO( false, "Provider not found for integration: " . null, null );

    }

    private function login( $provider,  AuthRequestDTO $AuthRequestDTO ):ResponseDTO {
        return $provider->login( $AuthRequestDTO );
    }

    private function loginByCode( $provider,  AuthRequestDTO $AuthRequestDTO, $user = null):ResponseDTO {
        return $provider->loginByCode(  $AuthRequestDTO );
    }

    private function refresh_token( $provider,  AuthRequestDTO $AuthRequestDTO, $user ):ResponseDTO {
        return $provider->refresh_token( $AuthRequestDTO );
    }

    private function logout( $provider,  AuthRequestDTO $AuthRequestDTO, $user ):ResponseDTO {
        return $provider->logout( $AuthRequestDTO );
    }

    private function register( $provider,  AuthRequestDTO $AuthRequestDTO, $user = null):ResponseDTO {
        return $provider->register(  $AuthRequestDTO );
    }
    private function getProfile( $provider,  AuthRequestDTO $AuthRequestDTO, $user = null):ResponseDTO {
        return $provider->getProfile(  $AuthRequestDTO );
    }




}
