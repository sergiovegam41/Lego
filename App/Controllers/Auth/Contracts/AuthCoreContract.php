<?php
namespace App\Controllers\Auth\Contracts;

use App\Controllers\Auth\DTOs\AuthRequestDTO;
use Core\Models\ResponseDTO;

interface AuthCoreContract {
   
    public function login(AuthRequestDTO $request): ResponseDTO;
    public function refresh_token(AuthRequestDTO $request): ResponseDTO;
    public function logout(AuthRequestDTO $request): ResponseDTO;
    public function register(AuthRequestDTO $request): ResponseDTO;

}
