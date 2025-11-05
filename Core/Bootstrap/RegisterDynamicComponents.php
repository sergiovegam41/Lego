<?php

namespace Core\Bootstrap;

/**
 * RegisterDynamicComponents - Registro centralizado de componentes dinámicos
 *
 * FILOSOFÍA LEGO:
 * Asegura que todos los componentes dinámicos estén registrados
 * antes de ser usados desde JavaScript.
 *
 * IMPORTANTE: Este archivo debe ser incluido en el bootstrap de la aplicación
 * (por ejemplo, en public/index.php o en el autoloader).
 */
class RegisterDynamicComponents
{
    /**
     * Lista de componentes dinámicos a registrar
     * Formato: [ruta_del_archivo, nombre_de_clase]
     */
    private static array $dynamicComponents = [
        // IconButtonComponent
        [
            'path' => __DIR__ . '/../../components/shared/Buttons/IconButtonComponent/IconButtonComponent.php',
            'class' => 'Components\Shared\Buttons\IconButtonComponent\IconButtonComponent'
        ],
        // Agregar aquí nuevos componentes dinámicos en el futuro:
        // [
        //     'path' => __DIR__ . '/../../components/shared/StatusBadgeComponent/StatusBadgeComponent.php',
        //     'class' => 'Components\Shared\StatusBadgeComponent\StatusBadgeComponent'
        // ],
    ];

    /**
     * Registrar todos los componentes dinámicos
     *
     * NOTA: Se crean instancias temporales para activar el auto-registro.
     * Este método es resiliente a errores y no fallará si un componente no se puede cargar.
     */
    public static function register(): void
    {
        $registered = 0;
        $failed = 0;

        foreach (self::$dynamicComponents as $component) {
            try {
                // Verificar si el archivo existe
                if (!file_exists($component['path'])) {
                    if (self::isDebugMode()) {
                        error_log("[RegisterDynamicComponents] Archivo no encontrado: {$component['path']}");
                    }
                    $failed++;
                    continue;
                }

                // Intentar cargar el archivo manualmente
                require_once $component['path'];

                // Verificar si la clase existe después de cargar
                if (!class_exists($component['class'])) {
                    if (self::isDebugMode()) {
                        error_log("[RegisterDynamicComponents] Clase no encontrada después de cargar: {$component['class']}");
                    }
                    $failed++;
                    continue;
                }

                // Crear instancia para activar auto-registro
                new $component['class']();
                $registered++;

            } catch (\Throwable $e) {
                // Capturar cualquier error durante el registro
                if (self::isDebugMode()) {
                    error_log("[RegisterDynamicComponents] Error al registrar {$component['class']}: {$e->getMessage()}");
                }
                $failed++;
            }
        }

        // Log en desarrollo
        if (self::isDebugMode()) {
            error_log("[RegisterDynamicComponents] Componentes registrados: {$registered}, Fallidos: {$failed}");
        }
    }

    /**
     * Verificar si estamos en modo debug
     */
    private static function isDebugMode(): bool
    {
        return getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true';
    }
}
