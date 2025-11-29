<?php

namespace Core\Traits;

use ReflectionClass;
use Core\Attributes\ApiComponent;

/**
 * ComponentContextTrait - Contexto automático para componentes
 * 
 * FILOSOFÍA LEGO:
 * Cada componente "sabe quién es" - su ruta, su posición en el menú,
 * sus relaciones. Este trait expone ese conocimiento al JavaScript
 * de manera automática, eliminando la necesidad de "magic strings".
 * 
 * CERO CONFIGURACIÓN:
 * El contexto se deriva automáticamente de:
 * - El atributo #[ApiComponent] del componente
 * - La estructura de carpetas
 * - El nombre de la clase
 * 
 * USO EN PHP:
 * ```php
 * class MiComponent extends CoreComponent
 * {
 *     // El trait ya está incluido en CoreComponent
 *     
 *     protected function component(): string
 *     {
 *         return <<<HTML
 *         {$this->renderContext()}  <!-- Expone contexto al JS -->
 *         <div>...</div>
 *         HTML;
 *     }
 * }
 * ```
 * 
 * USO EN JS:
 * ```javascript
 * // El contexto está disponible automáticamente
 * const ctx = ComponentContext.current();
 * 
 * // Rutas derivadas automáticamente
 * ctx.route          // '/component/mi-component'
 * ctx.apiRoute       // '/api/mi-component'
 * ctx.id             // 'mi-component'
 * ctx.parentMenuId   // ID del menú padre (si existe)
 * 
 * // Acciones comunes sin hardcoding
 * ctx.api('delete')  // '/api/mi-component/delete'
 * ctx.child('edit')  // '/component/mi-component/edit'
 * ```
 * 
 * MÉTODOS PHP DISPONIBLES:
 * - getComponentContext(): array  - Contexto completo
 * - getContextId(): string        - ID del componente
 * - getContextApiRoute(): string  - Ruta base de API
 * - getContextParentMenuId(): ?string - ID del menú padre
 * - renderContext(): string       - Script para inyectar al JS
 */
trait ComponentContextTrait
{
    private ?array $_componentContext = null;

    /**
     * Obtener el contexto del componente
     * 
     * Se calcula automáticamente basándose en:
     * - Atributo #[ApiComponent]
     * - Nombre de la clase
     * - Estructura de carpetas
     */
    protected function getComponentContext(): array
    {
        if ($this->_componentContext !== null) {
            return $this->_componentContext;
        }

        $reflection = new ReflectionClass($this);
        
        // Obtener ruta del atributo #[ApiComponent]
        $route = $this->extractRouteFromAttribute($reflection);
        
        // Derivar ID del componente (slug)
        $id = $this->deriveComponentId($route, $reflection);
        
        // Derivar API route
        $apiRoute = $this->deriveApiRoute($route);
        
        // Intentar encontrar el menú padre
        $parentMenuId = $this->findParentMenuId($route);
        
        // Construir contexto
        $this->_componentContext = [
            'id' => $id,
            'route' => $route,
            'apiRoute' => $apiRoute,
            'parentMenuId' => $parentMenuId,
            'className' => $reflection->getShortName(),
            'namespace' => $reflection->getNamespaceName(),
        ];

        return $this->_componentContext;
    }

    /**
     * Extraer la ruta del atributo #[ApiComponent]
     * 
     * IMPORTANTE: El atributo tiene rutas como '/example-crud',
     * pero los componentes se sirven en '/component/example-crud'.
     * Este método agrega el prefijo '/component' automáticamente.
     */
    private function extractRouteFromAttribute(ReflectionClass $reflection): string
    {
        $attributes = $reflection->getAttributes(ApiComponent::class);
        
        if (empty($attributes)) {
            // Fallback: derivar de la estructura de carpetas
            return $this->deriveRouteFromNamespace($reflection);
        }

        $attribute = $attributes[0]->newInstance();
        $route = $attribute->route ?? '';
        
        // El atributo tiene '/example-crud', pero el componente se sirve en '/component/example-crud'
        if ($route && !str_starts_with($route, '/component')) {
            $route = '/component' . $route;
        }
        
        return $route;
    }

    /**
     * Derivar ruta desde el namespace cuando no hay atributo
     */
    private function deriveRouteFromNamespace(ReflectionClass $reflection): string
    {
        $namespace = $reflection->getNamespaceName();
        
        // Components\App\ExampleCrud -> /component/example-crud
        // Components\Core\Home -> /component/home
        if (preg_match('/Components\\\\(App|Core|Shared)\\\\(.+)/', $namespace, $matches)) {
            $path = $matches[2];
            // Convertir CamelCase a kebab-case
            $slug = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $path));
            $slug = str_replace('\\', '/', $slug);
            return '/component/' . $slug;
        }

        return '';
    }

    /**
     * Derivar ID del componente (slug)
     */
    private function deriveComponentId(string $route, ReflectionClass $reflection): string
    {
        if ($route) {
            // /component/example-crud/edit -> example-crud-edit
            $path = str_replace('/component/', '', $route);
            return str_replace('/', '-', trim($path, '/'));
        }

        // Fallback: usar nombre de clase
        $name = $reflection->getShortName();
        $name = str_replace('Component', '', $name);
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    }

    /**
     * Derivar ruta de API desde la ruta del componente
     */
    private function deriveApiRoute(string $componentRoute): string
    {
        // /component/example-crud -> /api/example-crud
        // /component/example-crud/edit -> /api/example-crud
        $path = str_replace('/component/', '', $componentRoute);
        $parts = explode('/', trim($path, '/'));
        
        // Tomar solo la primera parte (el recurso base)
        $basePath = $parts[0] ?? '';
        
        return $basePath ? '/api/' . $basePath : '';
    }

    /**
     * Encontrar el ID del menú padre basándose en la ruta
     */
    private function findParentMenuId(string $route): ?string
    {
        // /component/example-crud/edit -> buscar 'example-crud' en el menú
        $path = str_replace('/component/', '', $route);
        $parts = explode('/', trim($path, '/'));
        
        if (count($parts) > 0) {
            return $parts[0]; // El primer segmento es típicamente el ID del menú
        }

        return null;
    }

    /**
     * Renderizar el contexto como script para JS
     * 
     * Coloca esto en el HTML del componente para que JS tenga acceso
     * al contexto automáticamente.
     */
    protected function renderContext(): string
    {
        $context = $this->getComponentContext();
        $json = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        return <<<HTML
        <script>
            window.__componentContext = {$json};
        </script>
        HTML;
    }

    /**
     * Obtener el ID del contexto del componente
     * 
     * NOTA: Llamado "ContextId" para evitar conflicto con
     * DynamicComponentInterface::getComponentId() que es estático
     */
    protected function getContextId(): string
    {
        return $this->getComponentContext()['id'];
    }

    /**
     * Obtener la ruta de API base
     */
    protected function getContextApiRoute(): string
    {
        return $this->getComponentContext()['apiRoute'];
    }

    /**
     * Obtener el ID del menú padre
     */
    protected function getContextParentMenuId(): ?string
    {
        return $this->getComponentContext()['parentMenuId'];
    }
}

