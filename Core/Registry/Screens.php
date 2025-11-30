<?php

namespace Core\Registry;

use Core\Registry\ScreenRegistry;

// ═══════════════════════════════════════════════════════════════════
// SCREEN REGISTRATION
// ═══════════════════════════════════════════════════════════════════
// 
// Este archivo registra todos los screens disponibles en la aplicación.
// Los screens se auto-registran aquí y el menú consume desde ScreenRegistry.
//
// AGREGAR UN NUEVO SCREEN:
// 1. Crear el componente implementando ScreenInterface
// 2. Agregar el use statement abajo
// 3. Agregar al array de registerMany()
//
// ═══════════════════════════════════════════════════════════════════

// Example CRUD Screens
use Components\App\ExampleCrud\ExampleCrudComponent;
use Components\App\ExampleCrud\Childs\ExampleCreate\ExampleCreateComponent;
use Components\App\ExampleCrud\Childs\ExampleEdit\ExampleEditComponent;

/**
 * Registra todos los screens de la aplicación
 * 
 * Llamar desde el bootstrap de la aplicación:
 * require_once 'Core/Registry/Screens.php';
 */
function registerAllScreens(): void
{
    ScreenRegistry::registerMany([
        // ═══════════════════════════════════════════════════════════════
        // EXAMPLE CRUD
        // ═══════════════════════════════════════════════════════════════
        ExampleCrudComponent::class,
        ExampleCreateComponent::class,
        ExampleEditComponent::class,
        
        // ═══════════════════════════════════════════════════════════════
        // Agregar más screens aquí...
        // ═══════════════════════════════════════════════════════════════
    ]);
}

// Auto-registrar al incluir este archivo
registerAllScreens();

