<?php
namespace Core\Services;    

use Core\Models\ResponseDTO;
use App\Models\User;
use App\Models\UserSession;
use App\Utils\RedisClient;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Core\Models\StatusCodes;

class AuthServicesCore
{
    private $jwt_secret = null; 
    private $jwt_expire = 900; // 15 minutos
    private $refresh_token_expire = 2592000; // 30 días
    private $redis = null; 

    public function __construct()
    {
        $this->redis = RedisClient::getInstance();
        $this->jwt_secret = env("JWT_SECRET", "secret-key"); 
    }

    /**
     * Almacena el access token en Redis.
     * Se elimina el uso de $_SESSION para centralizar la gestión en Redis.
     */
    private function storeAccessTokenInRedis($access_token, $auth_user_id, $device_id, $auth_group_id, $role_id, $expires_at): void
    {
        $sessionData = json_encode([
            'auth_user_id' => $auth_user_id,
            'device_id' => $device_id,
            'auth_group_id' => $auth_group_id,
            'role_id' => $role_id,
            'expires_at' => $expires_at,
        ]);

        $this->redis->setex("access_token:$access_token", $this->jwt_expire, $sessionData);
    }

    private function getSessionFromRedis($access_token)
    {
        $sessionData = $this->redis->get("access_token:$access_token");
        return $sessionData ? json_decode($sessionData, true) : null;
    }

    /**
     * Verifica autenticación basándose en la sesión en Redis o cookie.
     */
    static public function isAutenticated(): ResponseDTO
    {
        $authorizationToken = self::getAuthorizationToken();
        if (!$authorizationToken || strlen($authorizationToken) <= 7) {
            return new ResponseDTO(false, "No autenticado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $access_token = substr($authorizationToken, 7);
        $authService = new self();
        $sessionData = $authService->getSessionFromRedis($access_token);

        if (!$sessionData) {
            return new ResponseDTO(false, "No autenticado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $expires_at = Carbon::parse($sessionData['expires_at']);
        if (Carbon::now()->greaterThan($expires_at)) {
            return new ResponseDTO(false, "Token expirado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        return new ResponseDTO(true, "Autenticado", $sessionData);
    }

    /**
     * Función auxiliar para obtener el token de autorización.
     * Se revisa en headers y, en caso de no encontrarlo, se busca en la cookie.
     */
    private static function getAuthorizationToken()
    {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER["HTTP_AUTHORIZATION"]);
        }elseif(isset($_COOKIE['access_token'])){
            return 'Bearer ' . $_COOKIE['access_token'];
        }
        return null;
    }

    /**
     * Proceso de login: valida credenciales, genera tokens, almacena en Redis y establece cookie.
     */
    public function coreLogin($email, $password, $auth_group_id, $device_id, $firebase_token = null): ResponseDTO
    {
        // Validación y sanitización del email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new ResponseDTO(false, "Email inválido", null, StatusCodes::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $email)
            ->where('auth_group_id', $auth_group_id)
            ->first();

        if (!$user || !password_verify($password, $user->password)) {
            return new ResponseDTO(false, "Usuario y/o contraseña inválidos", null, StatusCodes::HTTP_UNAUTHORIZED);
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

        // Establece la cookie con el access token
        setcookie(
            'access_token',
            $access_token,
            [
                'expires' => $expires_at->timestamp,
                'path' => '/',
                'domain' => '', // Define el dominio según corresponda
                'secure' => true,   // true si usas HTTPS
                'httponly' => true, // Evita acceso desde JavaScript
                'samesite' => 'Lax', // o 'Strict', según necesidad
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

    /**
     * Renueva el access token utilizando el refresh token.
     */
    public function coreRefreshToken($refresh_token, $device_id): ResponseDTO
    {
        $session = UserSession::where('refresh_token', $refresh_token)
            ->where('device_id', $device_id)
            ->first();

        if (!$session) {
            return new ResponseDTO(false, "Refresh token inválido o no encontrado", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        if (Carbon::now()->greaterThan($session->refresh_expires_at)) {
            return new ResponseDTO(false, "El refresh token ha expirado. Debes volver a iniciar sesión", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $access_token = $this->generateAccessToken($session->auth_user_id, $device_id);
        $expires_at = Carbon::now()->addSeconds($this->jwt_expire);
        $new_refresh_expires_at = Carbon::now()->addSeconds($this->refresh_token_expire);

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

    /**
     * Obtiene la información del usuario actual autenticado.
     */
    public function coreGetCurrentUser(): ResponseDTO
    {
        return self::isAutenticated();
    }

    /**
     * Registra un nuevo usuario.
     */
    function coreRegister($email, $password, $auth_grup_id, $role_name): ResponseDTO
    {
        // Sanitización del email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new ResponseDTO(false, "Email inválido", null, StatusCodes::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'auth_group_id' => $auth_grup_id,
            'role_name' => $role_name
        ]);

        return new ResponseDTO(true, "Registro exitoso", $user);
    }

    /**
     * Genera un refresh token con claims estándar.
     */
    private function generateRefreshToken($user_id, $device_id): string
    {
        $payload = [
            'iss' => 'tu-app',      // Emisor
            'sub' => $user_id,      // Sujeto
            'aud' => 'tu-app-users',// Audiencia
            'device_id' => $device_id,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addSeconds($this->refresh_token_expire)->timestamp,
            'jti' => bin2hex(random_bytes(16))
        ];
        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    /**
     * Genera un access token con claims estándar.
     */
    private function generateAccessToken($user_id, $device_id): string
    {
        $payload = [
            'iss' => 'tu-app',
            'sub' => $user_id,
            'aud' => 'tu-app-users',
            'device_id' => $device_id,
            'iat' => time(),
            'exp' => time() + $this->jwt_expire,
            'jti' => bin2hex(random_bytes(16))
        ];
        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    /**
     * Verifica y decodifica el JWT, registrando errores en caso de fallo.
     */
    public function verifyJWT($token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return isset($decoded->sub);
        } catch (\Exception $e) {
            error_log("Error al verificar JWT: " . $e->getMessage());
            return false;
        }
    }
}
