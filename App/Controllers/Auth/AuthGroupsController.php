<?php

namespace App\Controllers\Auth;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;

use App\Controllers\Auth\Providers\AuthGroupsProvider;

use Core\Controller\CoreController;
use Core\Models\StatusCodes;
use Core\providers\Request;
use Core\Response;

class AuthGroupsController extends CoreController
{
  protected $arrayMethods = [ AuthActions::LOGIN, AuthActions::REFRESH_TOKEN, AuthActions::LOGOUT, AuthActions::REGISTER ];
  public function __construct($group, $accion)
  {

      $request = new Request();
      $this->getMethod(new AuthRequestDTO(auth_grup_name: $group, request: $request, auth_accion: $accion ), $accion);
  }

  public function login( AuthRequestDTO $AuthRequestDTO ){

      $AuthRequestDTO->request->setRules([
              
        'username' => 'required|email',
        'password' => 'required'

      ])->validateMake();
      
      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );
      
      return Response::json(StatusCodes::HTTP_OK, (array)$resp);

  }

  public function refresh_token( AuthRequestDTO $AuthRequestDTO ){

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json(StatusCodes::HTTP_OK, (array)$resp);

  }

  public function logout( AuthRequestDTO $AuthRequestDTO ){

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json(StatusCodes::HTTP_OK, (array)$resp);

  }
  
  public function register( AuthRequestDTO $AuthRequestDTO ){

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json(StatusCodes::HTTP_OK, (array)$resp);

  }

 
    
    
}
