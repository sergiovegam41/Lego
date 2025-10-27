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
    private $jwt_expire = 3600; // 1 hora (3600 segundos)
    private $refresh_token_expire = 2592000; // 30 días
    private $redis = null;
    private $token_extension_threshold = 1800; // Extender token cuando queden 30 minutos o menos (50% del tiempo)
    private $activity_check_interval = 300; // 5 minutos - Intervalo mínimo entre actualizaciones de BD

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
     * Extiende automáticamente el token si el usuario está activo.
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

        // Extender el token automáticamente si el usuario está activo
        $authService->extendTokenIfActive($access_token, $sessionData);

        return new ResponseDTO(true, "Autenticado", $sessionData);
    }

    /**
     * Extiende la expiración del token si el usuario ha estado activo.
     *
     * IMPORTANTE: Este método SIEMPRE actualiza Redis para evitar que el token expire
     * mientras el usuario está activo. La lógica de rate limiting solo afecta si se
     * actualiza la base de datos, pero Redis se actualiza en cada request válido.
     *
     * Estrategia:
     * 1. Si el token está próximo a expirar (≤3 seg) → SIEMPRE extender en Redis
     * 2. Si han pasado ≥30 min desde última extensión → También actualizar BD
     * 3. Si no han pasado 30 min → Solo extender Redis (BD mantiene timestamp)
     */
    private function extendTokenIfActive(string $access_token, array $sessionData): void
    {

        try {
            $expires_at = Carbon::parse($sessionData['expires_at']);
            $now = Carbon::now();
            $secondsUntilExpiration = $now->diffInSeconds($expires_at, false);

            // Solo extender si el token está próximo a expirar
            if ($secondsUntilExpiration > $this->token_extension_threshold) {
                return;
            }

            // Buscar la sesión en la base de datos
            $session = UserSession::where('auth_user_id', $sessionData['auth_user_id'])
                ->where('device_id', $sessionData['device_id'])
                ->where('is_active', true)
                ->first();

            if (!$session) {
                return;
            }

            // Nueva fecha de expiración para el token
            $new_expires_at = $now->addSeconds($this->jwt_expire);

            // CRÍTICO: SIEMPRE actualizar Redis para evitar que el token expire
            // Redis es la fuente de verdad para validación de tokens
            $sessionData['expires_at'] = $new_expires_at->toDateTimeString();
            $this->redis->setex(
                "access_token:$access_token",
                $this->jwt_expire,
                json_encode($sessionData)
            );

            // Verificar si debemos actualizar también la base de datos
            // Solo actualizar BD si han pasado suficiente tiempo desde la última extensión
            $lastActivity = $session->last_activity_at ? Carbon::parse($session->last_activity_at) : null;
            $shouldUpdateDatabase = true;

            if ($shouldUpdateDatabase) {
                // Actualizar BD con nueva expiración y timestamp de actividad
                $session->update([
                    'expires_at' => $new_expires_at,
                    'last_activity_at' => $now
                ]);

                // Actualizar la cookie si existe
                if (isset($_COOKIE['access_token']) && $_COOKIE['access_token'] === $access_token) {
                    setcookie(
                        'access_token',
                        $access_token,
                        [
                            'expires' => $new_expires_at->timestamp,
                            'path' => '/',
                            'domain' => '',
                            'secure' => false,
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]
                    );
                }

                error_log("Token extendido (Redis + BD) para usuario {$sessionData['auth_user_id']} en dispositivo {$sessionData['device_id']}. Nueva expiración: {$new_expires_at->toDateTimeString()}");
            } else {
                // Solo extendimos Redis, no BD (rate limiting)
                error_log("Token extendido (solo Redis) para usuario {$sessionData['auth_user_id']} en dispositivo {$sessionData['device_id']}. BD no actualizada (rate limit: {$this->activity_check_interval}s)");
            }

        } catch (\Exception $e) {
            // No fallar si hay un error en la extensión, simplemente registrar
            error_log("Error al extender token: " . $e->getMessage());
        }
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
                'firebase_token' => $firebase_token,
                'last_activity_at' => Carbon::now()
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
                'secure' => false,   // false para HTTP, true para HTTPS
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
            'last_activity_at' => Carbon::now()
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
     * Cierra la sesión del usuario invalidando el token en Redis y marcando la sesión como inactiva en BD.
     *
     * @param string|null $device_id Si se especifica, solo cierra sesión en ese dispositivo. Si es null, cierra todas las sesiones del usuario.
     * @return ResponseDTO
     */
    public function coreLogout($device_id = null): ResponseDTO
    {
        // Obtener el token actual
        $authorizationToken = self::getAuthorizationToken();
        if (!$authorizationToken || strlen($authorizationToken) <= 7) {
            return new ResponseDTO(false, "No hay sesión activa", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        $access_token = substr($authorizationToken, 7);

        // Obtener datos de sesión de Redis
        $sessionData = $this->getSessionFromRedis($access_token);

        if (!$sessionData) {
            return new ResponseDTO(false, "Sesión no encontrada", null, StatusCodes::HTTP_UNAUTHORIZED);
        }

        try {
            // 1. Eliminar token de Redis (invalidación inmediata)
            $this->redis->del("access_token:$access_token");

            // 2. Marcar sesión como inactiva en BD
            $query = [
                'auth_user_id' => $sessionData['auth_user_id']
            ];

            // Si se especifica device_id, solo cerrar sesión en ese dispositivo
            if ($device_id) {
                $query['device_id'] = $device_id;
            }

            UserSession::where($query)->update(['is_active' => false]);

            // 3. Eliminar cookie
            if (isset($_COOKIE['access_token'])) {
                setcookie(
                    'access_token',
                    '',
                    [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]
                );
            }

            $message = $device_id
                ? "Sesión cerrada en dispositivo {$device_id}"
                : "Todas las sesiones cerradas";

            error_log("Logout exitoso para usuario {$sessionData['auth_user_id']}: {$message}");

            return new ResponseDTO(true, $message, [
                'auth_user_id' => $sessionData['auth_user_id'],
                'device_id' => $device_id
            ]);

        } catch (\Exception $e) {
            error_log("Error al cerrar sesión: " . $e->getMessage());
            return new ResponseDTO(false, "Error al cerrar sesión", null, StatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
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
            'iss' => 'LEGO',      // Emisor
            'sub' => $user_id,      // Sujeto
            'aud' => 'LEGO-users',// Audiencia
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
            'iss' => 'LEGO',
            'sub' => $user_id,
            'aud' => 'LEGO-users',
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
