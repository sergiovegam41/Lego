<?php

namespace App\Controllers\Auth\Providers;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Admin\AdminAuthGroupProvider;
use Core\Models\ResponseDTO;



class AuthGroupsProvider 
{

    private $providers = [];

    public function __construct() {
        // Definir la lista de proveedores de pago
        $this->providers = [

            new AdminAuthGroupProvider(),
           
        ];
    
    }

    public function handle( AuthRequestDTO $AuthRequestDTO, $user = null ):ResponseDTO{
    
        $accion =  $AuthRequestDTO->auth_accion;


        foreach ($this->providers as $provider) {
            
            if ( defined(get_class($provider) . '::AUTH_GROUP_NAME') && strtolower(constant(get_class($provider) . '::AUTH_GROUP_NAME')) === strtolower( $AuthRequestDTO->auth_grup_name ) ) {
                
                switch ($accion) {

                    case AuthActions::LOGIN:
                        return $this->login($provider, $AuthRequestDTO );
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

                }

            }

        }

        return new ResponseDTO( false, "Provider not found for integration: " . null, null );

    }

    public function login( $provider,  AuthRequestDTO $AuthRequestDTO ):ResponseDTO {
        
        /**
         * @var ResponsePymentLinkDTO $resp
        */
        $resp = $provider->login( $AuthRequestDTO );

        if($resp->success) {



        }
        
        return new ResponseDTO(false, "Error creating link", null);

    }

    public function refresh_token( $provider,  AuthRequestDTO $AuthRequestDTO, $user ):ResponseDTO {
        
        /**
         * @var ResponsePymentLinkDTO $resp
        */
        $resp = $provider->refresh_token( $AuthRequestDTO );

        if($resp->success) {



        }
        
        return new ResponseDTO(false, "Error creating link", null);

    }

    public function logout( $provider,  AuthRequestDTO $AuthRequestDTO, $user ):ResponseDTO {
        
        /**
         * @var ResponsePymentLinkDTO $resp
        */
        $resp = $provider->logout( $AuthRequestDTO );

        if($resp->success) {



        }
        
        return new ResponseDTO(false, "Error creating link", null);

    }

    public function register( $provider,  AuthRequestDTO $AuthRequestDTO, $user = null):ResponseDTO {
        
        /**
         * @var ResponsePymentLinkDTO $resp
        */
        $resp = $provider->register(  $AuthRequestDTO );

        if($resp->success) {



        }
        
        return new ResponseDTO(false, "Error creating link", null);

    }




}
