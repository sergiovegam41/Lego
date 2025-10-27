<?php

namespace App\Controllers\Auth\Controllers;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;

use App\Controllers\Auth\Providers\AuthGroupsProvider;

use Core\Controller\CoreController;
use Core\Models\StatusCodes;
use Core\providers\Request;
use Core\Response;

class AuthGroupsController extends CoreController
{

  const ROUTE = 'auth';
  
  protected $arrayMethods = [ AuthActions::LOGIN, AuthActions::LOGIN_BY_CODE,AuthActions::REFRESH_TOKEN, AuthActions::LOGOUT, AuthActions::REGISTER, AuthActions::PROFILE ];

  public function __construct($group, $accion)
  {
    $request = new Request();
    $this->getMethod(new AuthRequestDTO(auth_grup_name: $group, request: $request, auth_accion: $accion ), $accion);
  }


  public function login( AuthRequestDTO $AuthRequestDTO ){

      $AuthRequestDTO->request->setRules([
        'username' => 'required|email',
        'password' => 'required|min:6'
      ])->validateMake();

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_OK, (array)$resp);

  }

  public function login_by_code( AuthRequestDTO $AuthRequestDTO ){

      $AuthRequestDTO->request->setRules([
        'email' => 'email',
        'number_phone' => 'required',
        'code' => 'required'
      ])->validateMake();

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_OK, (array)$resp);

  }

  public function refresh_token( AuthRequestDTO $AuthRequestDTO ){

      $AuthRequestDTO->request->setRules([
        'refresh_token' => 'required'
      ])->validateMake();

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_OK, (array)$resp);

  }

  public function logout( AuthRequestDTO $AuthRequestDTO ){

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_OK, (array)$resp);

  }

  public function register( AuthRequestDTO $AuthRequestDTO ){

      $AuthRequestDTO->request->setRules([
        'email' => 'required|email',
        'password' => 'required|min:8'
      ])->validateMake();

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_CREATED, (array)$resp);

  }

  public function profile( AuthRequestDTO $AuthRequestDTO ){

      $resp = (new AuthGroupsProvider())->handle( $AuthRequestDTO );

      return Response::json($resp->statusCode ?? StatusCodes::HTTP_OK, (array)$resp);

  }

 
}
