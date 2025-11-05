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
     * Registrar todos los componentes dinámicos
     *
     * NOTA: Se crean instancias temporales para activar el auto-registro.
     * Solo instancia componentes que existen para evitar errores en ambientes de producción.
     */
    public static function register(): void
    {
        // IconButtonComponent - Only register if class exists
        if (class_exists('Components\Shared\Buttons\IconButtonComponent\IconButtonComponent')) {
            new \Components\Shared\Buttons\IconButtonComponent\IconButtonComponent();
        }

        // Agregar aquí nuevos componentes dinámicos en el futuro:
        // if (class_exists('StatusBadgeComponent')) {
        //     new StatusBadgeComponent();
        // }
        // if (class_exists('UserAvatarComponent')) {
        //     new UserAvatarComponent();
        // }
        // etc.

        // Log en desarrollo
        if (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true') {
            error_log('[RegisterDynamicComponents] Componentes dinámicos registrados');
        }
    }
}
