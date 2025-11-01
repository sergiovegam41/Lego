<?php

namespace Core\Bootstrap;

use Components\Shared\Buttons\IconButtonComponent\IconButtonComponent;

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
     */
    public static function register(): void
    {
        // IconButtonComponent
        new IconButtonComponent();

        // Agregar aquí nuevos componentes dinámicos en el futuro:
        // new StatusBadgeComponent();
        // new UserAvatarComponent();
        // etc.

        // Log en desarrollo
        if (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true') {
            error_log('[RegisterDynamicComponents] Componentes dinámicos registrados');
        }
    }
}
