<?php
namespace Core\Services;    

use Core\Models\ResponseDTO;
use App\Models\User;
use App\Models\UserSession;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Core\Models\StatusCodes;

class AuthServices
{

    private $jwt_secret = null; // Reemplázala por una más segura
    private $jwt_expire = 900; // 15 minutos
    private $refresh_token_expire = 2592000; // 30 días
    // private $redis = null; 

    public function __construct()
    {
        // $this->redis = RedisClient::getInstance();
        $this->jwt_secret = env("JWT_SECRET", "secret-key");
    }


    
  
    private function storeAccessTokenInRedis($access_token, $auth_user_id, $device_id, $auth_group_id,  $role_id, $expires_at): void
    {
        $sessionData = json_encode([
            'auth_user_id' => $auth_user_id,
            'device_id' => $device_id,
            'auth_group_id' => $auth_group_id,
            'role_id' => $role_id,
            'expires_at' => $expires_at,

        ]);

        // $this->redis->setex("access_token:$access_token", $this->jwt_expire, $sessionData);

        // Guardar en la sesión de PHP
        $_SESSION['access_token'] = $access_token;
        $_SESSION['session_data'] = $sessionData;
    }

    // private function getSessionFromRedis($access_token)
    // {
    //     $sessionData = $this->redis->get("access_token:$access_token");
    //     return $sessionData ? json_decode($sessionData, true) : null;
    // }

    static public function isAutenticated(): ResponseDTO
    {

        
        $authorizationHeader = self::getAuthorizationHeader();
        $access_token = substr($authorizationHeader, 7);
        
        if (!isset($_SESSION['access_token']) || $_SESSION['access_token'] !== $access_token) {
            return new ResponseDTO(false, "No autenticado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }
        
        $sessionData = json_decode($_SESSION['session_data']) ?? null;
        
        
        // p($sessionData);
        if (!$sessionData) {
            return new ResponseDTO(false, "No autenticado", null, StatusCodes::HTTP_UNAUTHORIZED);
            
        }

        // Verificar si el token ha expirado
        $expires_at = Carbon::parse($sessionData->expires_at);

        // p($expires_at, Carbon::now());
        if (Carbon::now()->greaterThan($expires_at)) {
            return new ResponseDTO(false, "Token expirado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        return new ResponseDTO(true, "Autenticado", $sessionData);
    }

    public function coreLogin($email, $password, $auth_group_id, $device_id, $firebase_token = null): ResponseDTO
    {
        $user = User::where('email', $email)->where('auth_group_id', $auth_group_id)->first();

        if (!$user || !password_verify($password, $user->password)) {
            return new ResponseDTO(false, "Usuario y/o contraseña inválidos", StatusCodes::HTTP_OK);
        }

        $access_token = $this->generateAccessToken($user->id, $device_id);
        $refresh_token = $this->generateRefreshToken($user->id, $device_id);
        $expires_at = Carbon::now()->addSeconds($this->jwt_expire);
        $refresh_expires_at = Carbon::now()->addSeconds($this->refresh_token_expire);

        $this->storeAccessTokenInRedis($access_token, $user->id, $device_id, $auth_group_id, $user->role_id, $expires_at);

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

        //buscar usuario
        $user = User::where('id', $session->auth_user_id)->first();


        $this->storeAccessTokenInRedis($access_token, $session->auth_user_id, $device_id, $user->auth_group_id, $user->role_id, $expires_at);

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
            'user' => $user
        ]);
    }

    public function coreGetCurrentUser(): ResponseDTO
    {
        // p('a');

        return self::isAutenticated();
        
 
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