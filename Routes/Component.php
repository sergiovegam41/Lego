<?php
/**
 * Component Routes - Componentes SPA + Assets Estáticos
 *
 * PROPÓSITO DUAL:
 * 1. Componentes SPA que se cargan dinámicamente vía JavaScript
 * 2. Assets estáticos (.css, .js) de componentes
 *
 * RUTAS MANEJADAS:
 * - /component/nombre              → HTML del componente (sin DOCTYPE/HEAD/BODY)
 * - /component/nombre/file.css     → Archivo CSS del componente
 * - /component/nombre/file.js      → Archivo JS del componente
 *
 * FILOSOFÍA LEGO - "SIN ESTADO EN FRONTEND":
 * En lugar de mantener estado en el frontend (Redux, Vuex, etc.),
 * los componentes siempre se refrescan desde el servidor.
 *
 * VENTAJAS:
 * ✅ Información siempre actualizada (sin desfases)
 * ✅ Sin sincronización compleja de estado
 * ✅ Backend como única fuente de verdad
 * ✅ Desarrollo más simple y predecible
 * ✅ Consistencia total: /component/ para HTML y assets
 *
 * AUTO-DISCOVERY:
 * El sistema escanea automáticamente todos los componentes en /components
 * que tengan el decorador #[ApiComponent('/ruta', methods: ['GET'])]
 * y los registra como rutas accesibles vía /component/ruta
 *
 * EJEMPLO DE USO:
 * 1. Componente con decorador:
 *    #[ApiComponent('/inicio', methods: ['GET'])]
 *    class HomeComponent extends CoreComponent { ... }
 *
 * 2. JavaScript hace fetch:
 *    fetch('/component/inicio')
 *
 * 3. Sistema retorna:
 *    Solo el HTML del componente (sin <html><head><body>)
 *
 * 4. JavaScript inserta en DOM:
 *    document.getElementById('home-page').innerHTML = response
 *
 * 5. Assets del componente se cargan automáticamente:
 *    <link rel="stylesheet" href="/component/inicio/HomeComponent.css">
 *    <script src="/component/inicio/HomeComponent.js"></script>
 *
 * RUTAS DESCUBIERTAS:
 * - GET /component/inicio        - HomeComponent
 * - GET /component/automation    - AutomationComponent
 * - GET /component/nombre/*.css  - Assets CSS
 * - GET /component/nombre/*.js   - Assets JS
 * - [Cualquier componente con #[ApiComponent]]
 */

namespace Routes;

use Core\Services\ApiRouteDiscovery;
use Core\Router;

// PRIORIDAD 1: Servir assets estáticos (.css, .js) antes del auto-discovery
// Esto evita conflictos y mantiene eficiencia similar a nginx
// Patrón específico: solo captura archivos que terminen en .css o .js
\Flight::route('GET /@componentName/@file.css', function($componentName, $file) {
    // Construir ruta al archivo CSS
    $basePath = __DIR__ . '/../components/' . $componentName;
    $filePath = $basePath . '/' . $file . '.css';
    Router::serveStaticFile($filePath);
});

\Flight::route('GET /@componentName/@file.js', function($componentName, $file) {
    // Construir ruta al archivo JS
    $basePath = __DIR__ . '/../components/' . $componentName;
    $filePath = $basePath . '/' . $file . '.js';
    Router::serveStaticFile($filePath);
});

// PRIORIDAD 2: Auto-descubrimiento de componentes con decorador #[ApiComponent]
ApiRouteDiscovery::discover();

// PRIORIDAD 3: Ruta catch-all para componentes no encontrados (placeholder)
// Útil para items del menú que se crean pero aún no tienen componente asociado
\Flight::route('GET /@componentName', function($componentName) {
    // Verificar que no sea un archivo estático
    if (str_ends_with($componentName, '.css') || str_ends_with($componentName, '.js')) {
        \Flight::notFound();
        return;
    }
    
    // Mostrar componente placeholder
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    
    $componentRoute = '/component/' . $componentName;
    
    echo <<<HTML
    <div class="lego-screen lego-screen--padded" style="display: flex; align-items: center; justify-content: center; min-height: 400px; flex-direction: column; gap: 16px;">
        <div style="text-align: center;">
            <ion-icon name="construct-outline" style="font-size: 64px; color: var(--color-primary, #3b82f6);"></ion-icon>
            <h2 style="margin: 16px 0 8px 0; color: var(--text-primary, #1a1a1a);">Componente no encontrado</h2>
            <p style="color: var(--text-secondary, #6b7280); margin: 0 0 24px 0;">
                El componente para la ruta <code style="background: var(--bg-item, #f9fafb); padding: 4px 8px; border-radius: 4px; font-size: 14px;">{$componentRoute}</code> aún no ha sido creado.
            </p>
            <div style="background: var(--bg-item, #f9fafb); border: 1px solid var(--border-default, #e5e7eb); border-radius: 8px; padding: 16px; max-width: 600px; margin: 0 auto;">
                <p style="margin: 0 0 12px 0; font-weight: 500; color: var(--text-primary, #1a1a1a);">Para crear este componente:</p>
                <ol style="margin: 0; padding-left: 20px; color: var(--text-secondary, #6b7280); text-align: left;">
                    <li style="margin-bottom: 8px;">Crea un componente en <code>components/App/{$componentName}/{$componentName}Component.php</code></li>
                    <li style="margin-bottom: 8px;">Agrega el atributo <code>#[ApiComponent('/{$componentName}', methods: ['GET'])]</code></li>
                    <li style="margin-bottom: 8px;">Recarga la página</li>
                </ol>
            </div>
        </div>
    </div>
    HTML;
});

