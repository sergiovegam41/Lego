<?php

namespace App\Controllers\Auth\Providers;

use App\Controllers\Auth\DTOs\AuthActions;
use App\Controllers\Auth\DTOs\AuthRequestDTO;
use App\Controllers\Auth\Providers\AuthGroups\Admin\AdminAuthGroupProvider;
use App\Controllers\Auth\Providers\AuthGroups\Api\ApiAuthGroupProvider;
use App\Models\User;
use App\Models\UserSession;
use Core\Models\ResponseDTO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utils\RedisClient;
use Carbon\Carbon;
use Core\Models\StatusCodes;

class AuthGroupsProvider 
{

    private $providers = [];
    private $jwt_secret = null; // Reemplázala por una más segura
    private $jwt_expire = 900; // 15 minutos
    private $refresh_token_expire = 2592000; // 30 días
    private $redis = null; 

    public function __construct() {
        // Definir la lista de proveedores de pago
        $this->providers = [

            new AdminAuthGroupProvider(),
            
            new ApiAuthGroupProvider(),
           
        ];

        $this->redis = RedisClient::getInstance();

        $this->jwt_secret = env("JWT_SECRET", "secret-key");

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



  
    private function storeAccessTokenInRedis($access_token, $auth_user_id, $device_id): void
    {
        $sessionData = json_encode([
            'auth_user_id' => $auth_user_id,
            'device_id' => $device_id
        ]);

        $this->redis->setex("access_token:$access_token", $this->jwt_expire, $sessionData);
    }

    private function getSessionFromRedis($access_token)
    {
        $sessionData = $this->redis->get("access_token:$access_token");
        return $sessionData ? json_decode($sessionData, true) : null;
    }

    public function coreLogin($email, $password, $auth_group_id, $device_id, $firebase_token = null): ResponseDTO
    {
        $user = User::where('email', $email)->where('auth_group_id', $auth_group_id)->first();

        if (!$user || !password_verify($password, $user->password)) {
            return new ResponseDTO(false, "Usuario y/o contraseña inválidos", null);
        }

        $access_token = $this->generateAccessToken($user->id, $device_id);
        $refresh_token = $this->generateRefreshToken($user->id, $device_id);
        $expires_at = Carbon::now()->addSeconds($this->jwt_expire);
        $refresh_expires_at = Carbon::now()->addSeconds($this->refresh_token_expire);

        $this->storeAccessTokenInRedis($access_token, $user->id, $device_id);

        UserSession::updateOrCreate(
            ['auth_user_id' => $user->id, 'device_id' => $device_id],
            [
                'refresh_token' => $refresh_token,
                'access_token' => $access_token,
                'expires_at' => $expires_at,
                'refresh_expires_at' => $refresh_expires_at,
                'firebase_token' => $firebase_token
            ]
        );

        return new ResponseDTO(true, "Login exitoso.", [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at' => $expires_at,
            'refresh_expires_at' => $refresh_expires_at,
            'user' => $user
        ]);
    }

    public function coreRefreshToken($refresh_token, $device_id): ResponseDTO
    {
        $session = UserSession::where('refresh_token', $refresh_token)
            ->where('device_id', $device_id)
            ->first();

        if (!$session) {
            return new ResponseDTO(false, "Refresh token inválido o no encontrado", null);
        }

        if (Carbon::now()->greaterThan($session->refresh_expires_at)) {
            return new ResponseDTO(false, "El refresh token ha expirado. Debes volver a iniciar sesión", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $access_token = $this->generateAccessToken($session->auth_user_id, $device_id);
        $expires_at = Carbon::now()->addSeconds($this->jwt_expire);
        $new_refresh_expires_at = Carbon::now()->addSeconds($this->refresh_token_expire);

        $this->storeAccessTokenInRedis($access_token, $session->auth_user_id, $device_id);

        $session->update([
            'access_token' => $access_token,
            'expires_at' => $expires_at,
            'refresh_expires_at' => $new_refresh_expires_at,
        ]);

        return new ResponseDTO(true, "Token renovado exitosamente", [
            'access_token' => $access_token,
            'expires_at' => $expires_at,
            'refresh_token' => $session->refresh_token,
            'refresh_expires_at' => $new_refresh_expires_at,
        ]);
    }

    public function coreGetCurrentUser(): ResponseDTO
    {
        $authorizationHeader = self::getAuthorizationHeader();

        if (!$authorizationHeader || stripos($authorizationHeader, 'Bearer ') !== 0) {
            return new ResponseDTO(false, "Token no proporcionado o formato inválido", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $access_token = substr($authorizationHeader, 7);
        $session = $this->getSessionFromRedis($access_token);

        if (!$session) {
            return new ResponseDTO(false, "Token inválido o no encontrado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $auth_user_id = $session['auth_user_id'];
        $device_id = $session['device_id'];

        $dbSession = UserSession::where('auth_user_id', $auth_user_id)
            ->where('device_id', $device_id)
            ->where('access_token', $access_token)
            ->first();

        if (!$dbSession) {
            return new ResponseDTO(false, "Sesión no válida o expirada", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $user = User::find($auth_user_id);

        if (!$user) {
            return new ResponseDTO(false, "Usuario no encontrado", null, StatusCodes::HTTP_NOT_FOUND);
        }

        return new ResponseDTO(true, "Usuario actual obtenido exitosamente", [
            'user' => $user,
            'session' => $dbSession,
        ]);
    }
    
    
    function coreRegister($email, $password, $auth_grup_id, $role_name):ResponseDTO {

        $user = User::create([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'auth_group_id' => $auth_grup_id,
            'role_name' => $role_name
        ]);

        return new ResponseDTO(true, "ok", $user);

    }

    private function generateRefreshToken($user_id, $device_id): string {
       $payload = [
            'user_id' => $user_id,
            'device_id' => $device_id,
            'iat' => Carbon::now(),
            'exp' => Carbon::now()->addSeconds($this->jwt_expire),
            'token' => Carbon::now().bin2hex(random_bytes(255)),
        ];
        return JWT::encode($payload, $this->jwt_secret, 'HS256');
       
    }


    private function generateAccessToken($user_id, $device_id): string {
        $payload = [
            'user_id' => $user_id,
            'device_id' => $device_id,
            'iat' => time(),
            'exp' => time() + $this->jwt_expire,
            'token' => time(). bin2hex(random_bytes(100)),
        ];
        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    public function verifyJWT($token): bool {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return isset($decoded->sub);
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function getAuthorizationHeader() {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                return trim($requestHeaders['Authorization']);
            }
        }
        return null;
    }

}
