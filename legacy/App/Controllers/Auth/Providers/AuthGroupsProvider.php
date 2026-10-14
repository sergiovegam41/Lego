<?php

namespace App\Controllers\Auth\Providers;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Admin\AdminAuthGroupProvider;
use App\Controllers\Auth\Providers\AuthGroups\Api\ApiAuthGroupProvider;
use Core\Models\ResponseDTO;
use Core\Models\StatusCodes;

class AuthGroupsProvider
{
    /**
     * Mapa estático de providers por ruta.
     * Se inicializa una sola vez para mejor rendimiento.
     */
    private static ?array $providerMap = null;

    /**
     * Obtiene el mapa de providers (lazy initialization).
     */
    private static function getProviderMap(): array
    {
        if (self::$providerMap === null) {
            self::$providerMap = [
                'admin' => new AdminAuthGroupProvider(),
                'api' => new ApiAuthGroupProvider(),
            ];
        }

        return self::$providerMap;
    }

    /**
     * Maneja las requests de autenticación delegando al provider correcto.
     */
    public function handle(AuthRequestDTO $authRequestDTO): ResponseDTO
    {
        $providerMap = self::getProviderMap();
        $routeName = strtolower($authRequestDTO->auth_grup_name);

        // Buscar provider en el mapa (O(1) vs O(n) del foreach anterior)
        $provider = $providerMap[$routeName] ?? null;

        if (!$provider) {
            return new ResponseDTO(
                false,
                "Provider not found for route: {$authRequestDTO->auth_grup_name}",
                null,
                StatusCodes::HTTP_NOT_FOUND
            );
        }

        // Usar match expression (PHP 8) en lugar de switch
        return match ($authRequestDTO->auth_accion) {
            AuthActions::LOGIN => $provider->login($authRequestDTO),
            AuthActions::LOGIN_BY_CODE => $provider->loginByCode($authRequestDTO),
            AuthActions::REFRESH_TOKEN => $provider->refresh_token($authRequestDTO),
            AuthActions::LOGOUT => $provider->logout($authRequestDTO),
            AuthActions::REGISTER => $provider->register($authRequestDTO),
            AuthActions::PROFILE => $provider->getProfile($authRequestDTO),
            default => new ResponseDTO(
                false,
                "Action not supported: {$authRequestDTO->auth_accion}",
                null,
                StatusCodes::HTTP_BAD_REQUEST
            )
        };
    }
}
