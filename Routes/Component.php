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

