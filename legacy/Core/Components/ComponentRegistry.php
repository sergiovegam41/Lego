<?php

namespace Core\Components;

use Core\Interfaces\DynamicComponentInterface;
use Core\Exceptions\ComponentIdCollisionException;

/**
 * ComponentRegistry - Registro centralizado de componentes dinámicos
 *
 * FILOSOFÍA LEGO:
 * Registry global que mantiene el mapeo de IDs a clases de componentes.
 * Valida unicidad de IDs y proporciona renderizado batch.
 *
 * CARACTERÍSTICAS:
 * - Validación de colisiones en runtime
 * - Renderizado individual y batch
 * - Logs en modo desarrollo
 * - Thread-safe (static)
 *
 * EJEMPLO DE USO:
 * ```php
 * // En el constructor del componente (auto-registro)
 * ComponentRegistry::register('icon-button', IconButtonComponent::class);
 *
 * // Renderizar uno
 * $html = ComponentRegistry::render('icon-button', ['action' => 'edit', 'entityId' => 14]);
 *
 * // Renderizar batch
 * $htmlList = ComponentRegistry::renderBatch('icon-button', [
 *     ['action' => 'edit', 'entityId' => 1],
 *     ['action' => 'delete', 'entityId' => 1]
 * ]);
 * ```
 */
class ComponentRegistry
{
    /**
     * Mapeo de ID a nombre de clase
     * @var array<string, string>
     */
    private static array $components = [];

    /**
     * Mapeo inverso para detectar colisiones
     * @var array<string, string>
     */
    private static array $collisionMap = [];

    /**
     * Registrar un componente en el registry
     *
     * @param string $id ID único del componente
     * @param string $class Nombre completo de la clase (FQCN)
     *
     * @throws ComponentIdCollisionException Si el ID ya está registrado
     */
    public static function register(string $id, string $class): void
    {
        // Validar colisión
        if (isset(self::$collisionMap[$id])) {
            $existing = self::$collisionMap[$id];

            throw new ComponentIdCollisionException(
                "Component ID collision detected!\n\n" .
                "ID: '{$id}'\n" .
                "Already registered by: {$existing}\n" .
                "Attempted by: {$class}\n\n" .
                "Solution: Change the COMPONENT_ID constant in one of these classes " .
                "to a unique value.\n\n" .
                "Registered components: " . implode(', ', array_keys(self::$components))
            );
        }

        // Registrar
        self::$components[$id] = $class;
        self::$collisionMap[$id] = $class;

        // Log en desarrollo
        if (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true') {
            error_log("[ComponentRegistry] ✓ Registered: {$id} → {$class}");
        }
    }

    /**
     * Verificar si un componente está registrado
     *
     * @param string $id ID del componente
     * @return bool
     */
    public static function isRegistered(string $id): bool
    {
        return isset(self::$components[$id]);
    }

    /**
     * Obtener la clase de un componente registrado
     *
     * @param string $id ID del componente
     * @return string|null Nombre de la clase o null si no existe
     */
    public static function getClass(string $id): ?string
    {
        return self::$components[$id] ?? null;
    }

    /**
     * Renderizar un componente con parámetros
     *
     * @param string $id ID del componente
     * @param array $params Parámetros para el renderizado
     *
     * @return string HTML renderizado
     *
     * @throws \InvalidArgumentException Si el componente no existe o no implementa la interfaz
     */
    public static function render(string $id, array $params): string
    {
        // Validar que existe
        if (!isset(self::$components[$id])) {
            throw new \InvalidArgumentException(
                "Component not found: '{$id}'\n\n" .
                "Available components: " . implode(', ', array_keys(self::$components))
            );
        }

        $className = self::$components[$id];

        // Validar que la clase existe
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(
                "Component class not found: {$className}"
            );
        }

        // Validar que implementa la interfaz
        if (!is_subclass_of($className, DynamicComponentInterface::class)) {
            throw new \InvalidArgumentException(
                "Component '{$className}' must implement DynamicComponentInterface"
            );
        }

        // Crear instancia y renderizar
        $component = new $className();

        return $component->renderWithParams($params);
    }

    /**
     * Renderizar múltiples instancias de un componente (batch)
     *
     * IMPORTANTE: Retorna el HTML en el mismo orden que los parámetros.
     *
     * @param string $id ID del componente
     * @param array $paramsList Array de arrays de parámetros
     *
     * @return array<int, string> Array de HTMLs renderizados (mismo orden que entrada)
     *
     * @throws \InvalidArgumentException Si el componente no existe
     */
    public static function renderBatch(string $id, array $paramsList): array
    {
        // Validar límite de batch (prevenir abusos)
        $maxBatchSize = 100;
        if (count($paramsList) > $maxBatchSize) {
            throw new \InvalidArgumentException(
                "Batch size exceeds maximum of {$maxBatchSize}. " .
                "Received: " . count($paramsList)
            );
        }

        $results = [];

        foreach ($paramsList as $index => $params) {
            try {
                $results[$index] = self::render($id, $params);
            } catch (\Exception $e) {
                // En caso de error, incluir información de debugging
                $results[$index] = "<!-- Error rendering component at index {$index}: " .
                                 htmlspecialchars($e->getMessage()) . " -->";

                error_log(
                    "[ComponentRegistry] Error rendering batch item {$index} " .
                    "for component '{$id}': " . $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Obtener todos los componentes registrados
     *
     * @return array<string, string> Mapeo de ID a clase
     */
    public static function getAll(): array
    {
        return self::$components;
    }

    /**
     * Limpiar el registry (útil para testing)
     */
    public static function clear(): void
    {
        self::$components = [];
        self::$collisionMap = [];
    }
}
