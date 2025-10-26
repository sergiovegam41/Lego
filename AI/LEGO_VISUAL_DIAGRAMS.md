# Lego Framework - Diagramas Visuales y Resumen Ejecutivo

## 1. DIAGRAMA DE FLUJO DE REQUEST

```
┌─────────────────────────────────────────────────────────────────────┐
│                      REQUEST HTTP/HTTPS                            │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               ▼
                   ┌─────────────────────┐
                   │    NGINX (Docker)   │
                   │   Port 8080 (Web)   │
                   │   Port 5678 (n8n)   │
                   └──────────┬──────────┘
                              │
                ┌─────────────────────────────────┐
                │    public/router.php            │
                │  (Servir archivos estáticos)    │
                └─┬───────────────────────────┬───┘
                  │ /assets/*               │ otros
                  │ /components/*.js|*.css  │
                  ▼                         ▼
            [Archivo estático]        public/index.php
                  +                        │
                                           ▼
                        ┌──────────────────────────────┐
                        │ Core/bootstrap.php           │
                        │ - Env vars                   │
                        │ - BD Connection (Eloquent)   │
                        │ - Sesiones PHP               │
                        │ - Funciones globales         │
                        └──────────┬───────────────────┘
                                   │
                        ┌──────────────────────────┐
                        │  Router Principal        │
                        │  Analiza URI             │
                        └────┬──────┬──────┬───────┘
                    ┌────────┘      │      └─────────┐
                    │               │                │
            /api/*  │          /view/*          otros
                    │               │                │
                    ▼               ▼                ▼
            Routes/Api.php  Routes/Views.php  Routes/Web.php
                    │               │                │
                    └───────────────┬────────────────┘
                                    │
                                    ▼
                        ┌──────────────────────────┐
                        │   Flight Framework       │
                        │   (Routing Engine)       │
                        └────────┬─────────────────┘
                                 │
            ┌────────────────────┼────────────────────┐
            │                    │                    │
            ▼                    ▼                    ▼
    Web Routes            API Routes           Auth Routes
    (Flight)          (Flight + Discovery)   (/auth/group/action)
            │                    │                    │
    GET /admin/         GET /inicio      POST /auth/admin/login
    GET /login      (si #[ApiComponent])  POST /auth/api/login
    GET /                                         │
            │                    │                │
            └────────────────────┼────────────────┘
                                 │
                    ┌────────────────────────┐
                    │   Handler/Controlador  │
                    │   (Crear componente)   │
                    └────────┬───────────────┘
                             │
                    ┌────────────────────────────────┐
                    │    Component->render()         │
                    │  ┌─────────────────────────┐   │
                    │  │ - Cargar CSS (rel path) │   │
                    │  │ - Cargar JS (rel path)  │   │
                    │  │ - Generar HTML          │   │
                    │  │ - LoadModules callback  │   │
                    │  └─────────────────────────┘   │
                    └────────┬───────────────────────┘
                             │
                    ┌────────────────────────┐
                    │   Response::uri()      │
                    │   (Enviar respuesta)   │
                    └────────┬───────────────┘
                             │
                             ▼
                    ┌────────────────────────┐
                    │   Nginx Response       │
                    │   (HTML + Status 200)  │
                    └────────┬───────────────┘
                             │
                             ▼
                    ┌────────────────────────┐
                    │  Cliente/Navegador     │
                    │  - Parsea HTML         │
                    │  - Carga assets (CSS)  │
                    │  - Ejecuta módulos JS  │
                    └────────────────────────┘
```

---

## 2. ARQUITECTURA DE COMPONENTES

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPONENTE LEGO                              │
│                                                                 │
│  components/Core/Home/HomeComponent.php                        │
│  components/App/ProductCard/ProductCardComponent.php           │
│                                                                 │
│  Filosofía: "Cada bloque tiene su lugar"                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                  ESTRUCTURA DE CARPETA                           │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ components/App/ProductCard/                                     │
│ │                                                               │
│ ├── ProductCardComponent.php      [PHP - Lógica + HTML]        │
│ │   ├── protected $config;                                     │
│ │   ├── protected $CSS_PATHS = [];                             │
│ │   ├── protected $JS_PATHS_WITH_ARG = [];                     │
│ │   └── protected function component(): string { ... }         │
│ │                                                              │
│ ├── product-card.css              [CSS - Estilos únicos]       │
│ │   ├── .product-card { ... }                                 │
│ │   └── .product-card h3 { ... }                              │
│ │                                                              │
│ └── product-card.js               [JS - Comportamiento]        │
│     ├── class ProductCardComponent { ... }                     │
│     ├── constructor(element)                                  │
│     └── init() { ... }                                        │
│                                                                │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│              HERENCIA Y COMPOSICIÓN                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ProductCardComponent extends CoreComponent                    │
│                                                                 │
│   CoreComponent (abstract)                                      │
│   │                                                             │
│   ├── abstract function component(): string                    │
│   │   └── Implementar en cada componente                       │
│   │                                                             │
│   ├── function render(): string                                │
│   │   ├── Genera <link rel="stylesheet">                       │
│   │   ├── Llama component()                                    │
│   │   ├── Genera <script>window.lego.loadModules(...)</script> │
│   │   └── Retorna HTML completo                               │
│   │                                                             │
│   └── Soporte para:                                            │
│       ├── Rutas relativas (./file.css, ../shared/util.js)     │
│       ├── Cache busting (?v=abc123)                           │
│       └── Scripts con argumentos (JS_PATHS_WITH_ARG)           │
│                                                                │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│              COMPOSICIÓN DE COMPONENTES                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ MainComponent                                                   │
│   ├── new MenuComponent()->render()                            │
│   ├── new HeaderComponent()->render()                          │
│   └── new HomeComponent()->render()                            │
│                                                                │
│ ¡Puedes anidar componentes infinitamente!                      │
│                                                                │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. MAPEO DE DIRECTORIOS: CORE vs APP vs COMPONENTS

```
Lego/
│
├── Core/                          ← Motor Framework
│   ├── bootstrap.php              (Inicialización)
│   ├── Response.php               (Gestión respuestas)
│   ├── Components/
│   │   └── CoreComponent/         (Clase base abstracta)
│   ├── Controller/
│   │   ├── CoreController.php     (API controllers)
│   │   └── CoreViewController.php (View controllers)
│   ├── Models/
│   │   └── Model.php              (ORM casero)
│   ├── Commands/
│   │   ├── CommandRouter.php      (CLI router)
│   │   ├── CoreCommand.php        (CLI base)
│   │   ├── MakeComponentCommand   (Generador)
│   │   ├── MigrateCommand         (BD)
│   │   └── MapRoutesCommand       (Mapeo rutas)
│   ├── Services/
│   │   ├── ApiRouteDiscovery.php  (Auto-registro API)
│   │   └── AuthServicesCore.php   (JWT + Sesiones)
│   ├── Helpers/
│   │   └── LegoHelpers.php        (Utilidades)
│   └── Providers/
│       ├── StringMethods.php
│       ├── Request.php
│       ├── Middleware.php
│       └── TimeSet.php
│
├── App/                           ← Lógica específica
│   ├── Controllers/
│   │   └── Auth/                  (Autenticación)
│   │       ├── Controllers/
│   │       │   └── AuthGroupsController.php
│   │       ├── Providers/
│   │       │   ├── Admin/
│   │       │   └── Api/
│   │       └── DTOs/
│   ├── Models/
│   │   ├── User.php              (extends Model/Eloquent)
│   │   └── UserSession.php
│   └── Utils/
│       ├── global.php
│       └── RedisClient.php
│
├── components/                    ← Vistas/Componentes
│   ├── Core/                      (Componentes base)
│   │   ├── Home/
│   │   │   ├── HomeComponent.php
│   │   │   ├── home.css
│   │   │   ├── home.js
│   │   │   └── Components/
│   │   │       ├── HeaderComponent/
│   │   │       ├── MenuComponent/
│   │   │       └── MainComponent/
│   │   ├── Login/
│   │   └── Automation/
│   ├── App/                       (Tus componentes)
│   │   └── TestButton/
│   │       ├── TestButtonComponent.php
│   │       ├── test-button.css
│   │       └── test-button.js
│   └── shared/                    (Componentes reutilizables)
│       └── butons/
│           └── select.css
│
└── Routes/                        ← Gestión de rutas
    ├── Web.php                    (Rutas web)
    ├── Api.php                    (Rutas API)
    └── Views.php                  (Rutas vistas)
```

---

## 4. FLUJO DE AUTENTICACIÓN

```
┌────────────────────────────────────────────────────────────┐
│         FLUJO AUTENTICACIÓN JWT                           │
└────────────────────────────────────────────────────────────┘

┌─────────────────────┐
│  POST /auth/admin   │
│    /login           │
└──────────┬──────────┘
           │
           ▼
┌──────────────────────────────────────────┐
│  AuthGroupsController                    │
│  ($group='admin', $accion='login')       │
└──────────┬───────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────┐
│  AuthGroupsProvider::getProvider()       │
│  ┌──────────────────────────────────┐    │
│  │ 'admin' → AdminAuthGroupProvider │    │
│  │ 'api'   → ApiAuthGroupProvider   │    │
│  └──────────────────────────────────┘    │
└──────────┬───────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────┐
│  AdminAuthGroupProvider->login()         │
│  ┌──────────────────────────────────┐    │
│  │ email    = request['username']   │    │
│  │ password = request['password']   │    │
│  │ device_id = request['device_id'] │    │
│  └──────────────────────────────────┘    │
└──────────┬───────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────┐
│  AuthServicesCore->coreLogin()           │
│  ┌──────────────────────────────────┐    │
│  │ 1. Buscar User por email         │    │
│  │ 2. Validar contraseña (bcrypt)   │    │
│  │ 3. Generar JWT tokens            │    │
│  │    - access_token (15min)        │    │
│  │    - refresh_token (7 días)      │    │
│  │ 4. Guardar en auth_user_sessions │    │
│  │ 5. Guardar en Redis              │    │
│  └──────────────────────────────────┘    │
└──────────┬───────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────┐
│  Response::json()                        │
│  ┌──────────────────────────────────┐    │
│  │ {                                │    │
│  │   "success": true,               │    │
│  │   "access_token": "eyJ0...",     │    │
│  │   "refresh_token": "eyJ1...",    │    │
│  │   "user": { ... }                │    │
│  │ }                                │    │
│  └──────────────────────────────────┘    │
└──────────┬───────────────────────────────┘
           │
           ▼
┌────────────────────────────────────────────┐
│  Navegador                                 │
│  1. Guarda tokens en localStorage         │
│  2. Headers siguientes:                    │
│     Authorization: Bearer eyJ0...         │
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│  Flujo de Validación en Requests           │
├────────────────────────────────────────────┤
│                                            │
│  AdminMiddlewares::isAutenticated()        │
│  ├─ 1. Lee token de header                │
│  ├─ 2. Valida firma JWT                   │
│  ├─ 3. Verifica no expirado               │
│  ├─ 4. Busca en auth_user_sessions        │
│  └─ 5. Valida device_id                   │
│                                            │
│  Device Management:                        │
│  - Un usuario puede tener sesiones en     │
│    múltiples dispositivos                 │
│  - device_id = identificador único        │
│    del dispositivo                        │
│                                            │
└────────────────────────────────────────────┘
```

---

## 5. CICLO DE VIDA DE UN REQUEST

```
FASE 1: SETUP
═══════════════════════════════════════════
1. Nginx recibe request en puerto 8080
2. router.php verifica si es archivo estático
   - /assets/* → Sirve directo (MIME header)
   - /components/*.css|*.js → Sirve directo
   - Otros → Redirige a index.php

FASE 2: BOOTSTRAP
═══════════════════════════════════════════
3. public/index.php
   - require autoload.php (PSR-4 loading)
   - require Core/bootstrap.php

4. Core/bootstrap.php
   - Cargar variables .env
   - Iniciar sesión PHP
   - Conectar BD (Eloquent)
   - Definir funciones globales (p, dd, consultar, etc)

FASE 3: ROUTING
═══════════════════════════════════════════
5. public/index.php analiza URI
   - GET /api/datos → Routes/Api.php
   - GET /view/home → Routes/Views.php
   - GET /admin → Routes/Web.php

FASE 4: DESCUBRIMIENTO
═══════════════════════════════════════════
6. Si es Routes/Api.php:
   - ApiRouteDiscovery::discover()
   - Busca componentes con #[ApiComponent]
   - Registra dinámicamente con Flight

7. Si es Routes/Web.php:
   - Define rutas estáticas con Flight

FASE 5: EJECUCIÓN
═══════════════════════════════════════════
8. Flight::start()
   - Coincide ruta con request
   - Ejecuta callable/handler
   - Pasa parámetros desde URL

FASE 6: RENDERIZADO
═══════════════════════════════════════════
9. Handler crea instancia de componente
   $component = new MainComponent([...])

10. Llama component->render()
    - Genera <link rel="stylesheet">
    - Genera HTML de component()
    - Genera <script> con loadModules()

FASE 7: RESPUESTA
═══════════════════════════════════════════
11. Response::uri($html)
    - Header Content-Type: text/html
    - Envía HTML
    - Status 200 OK

FASE 8: CLIENTE
═══════════════════════════════════════════
12. Navegador recibe HTML
    - Parsea estructura
    - Carga CSS de <link>
    - Load event → Ejecuta window.lego.loadModules()

13. JavaScript Loader
    - Carga archivos JS dinámicamente
    - Pasa argumentos a scripts
    - Inicializa componentes
```

---

## 6. TABLA COMPARATIVA: CORE vs APP

| Aspecto | Core | App | Components |
|---------|------|-----|-----------|
| **Propósito** | Motor framework | Lógica aplicación | Vistas/UI |
| **Responsabilidad** | Infraestructura | Controllers, Models | Renderizado |
| **Editar en cambios** | Raramente | Siempre | Siempre |
| **Heredar de** | CoreComponent, CoreController, Model | App clases | CoreComponent |
| **Namespace** | Core\* | App\* | Components\* |
| **Ejemplos** | CoreComponent, Flight, JWT | Auth, User, UserSession | MainComponent, LoginComponent |
| **Comportamiento** | Genérico/Reutilizable | Específico/Único | UI/Presentación |

---

## 7. TECNOLOGÍAS Y VERSIONES

```
┌─────────────────────────────────────────────────────┐
│           STACK TÉCNICO DE LEGO                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  BACKEND                                            │
│  ├─ PHP 8.1+ (PSR-4 Autoloading)                  │
│  ├─ Flight 3.13+ (Micro framework web)             │
│  ├─ Illuminate/Database 11.33+ (Eloquent ORM)      │
│  ├─ Firebase/PHP-JWT 6.11+ (Tokens JWT)            │
│  ├─ Predis 2.3+ (Redis client)                     │
│  └─ Vlucas/phpdotenv 5.6+ (Env vars)              │
│                                                     │
│  DATABASE                                           │
│  ├─ PostgreSQL 12+ (Relacional principal)          │
│  ├─ MongoDB (NoSQL - opcional)                     │
│  └─ Redis 6+ (Cache/Sesiones)                      │
│                                                     │
│  FRONTEND                                           │
│  ├─ HTML5 + CSS3 (Estándares W3C)                 │
│  ├─ JavaScript ES6+ (Módulos dinámicos)            │
│  ├─ Ionicons 5.5.2 (Icon library)                 │
│  └─ window.lego.* (Framework JavaScript custom)    │
│                                                     │
│  INFRAESTRUCTURA                                    │
│  ├─ Docker 20+ (Contenedores)                      │
│  ├─ Docker Compose 2+ (Orquestación)              │
│  ├─ Nginx 1.21+ (Web server)                       │
│  ├─ pgAdmin 6+ (DB management)                    │
│  └─ n8n (Automatización - opcional)                │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 8. FLUJO DE CREACIÓN DE COMPONENTE

```
PASO 1: GENERAR ESTRUCTURA
═══════════════════════════════════════════════════════
$ php lego make:component ProductCard --path=App

Resultado:
components/App/ProductCard/
├── ProductCardComponent.php     ← Generado automáticamente
├── product-card.css             ← Generado automáticamente
└── product-card.js              ← Generado automáticamente

PASO 2: EDITAR PHP (LÓGICA + HTML)
═══════════════════════════════════════════════════════
<?php
namespace Components\App\ProductCard;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class ProductCardComponent extends CoreComponent {
    protected $CSS_PATHS = ["./product-card.css"];
    
    public function component(): string {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./product-card.js", [
                "productId" => $this->config['id'],
                "price" => $this->config['price']
            ])
        ];
        
        return <<<HTML
        <div class="product-card">
            <h3>{$this->config['name']}</h3>
            <p>${$this->config['price']}</p>
        </div>
        HTML;
    }
}

PASO 3: EDITAR CSS (ESTILOS)
═══════════════════════════════════════════════════════
.product-card {
    padding: var(--spacing-md, 1rem);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--border-radius, 0.5rem);
    background-color: var(--bg-surface, #ffffff);
}

.product-card h3 {
    color: var(--text-primary, #1a202c);
}

PASO 4: EDITAR JS (COMPORTAMIENTO)
═══════════════════════════════════════════════════════
class ProductCardComponent {
    constructor(element) {
        this.element = element;
        this.init();
    }

    init() {
        console.log('ProductCard initialized', this.element);
        this.element.addEventListener('click', () => {
            console.log('Product clicked');
        });
    }
}

PASO 5: USAR EN OTRA VISTA
═══════════════════════════════════════════════════════
// En MainComponent.php o cualquier otro lugar:
$product = new ProductCardComponent([
    'id' => 1,
    'name' => 'Laptop Premium',
    'price' => 1299.99
]);

echo $product->render();

// O con MainComponent que lo compone:
class MainComponent extends CoreComponent {
    protected function component(): string {
        $productCard = (new ProductCardComponent([
            'id' => 1,
            'name' => 'Laptop',
            'price' => 999.99
        ]))->render();
        
        return <<<HTML
        <div class="main">
            {$productCard}
        </div>
        HTML;
    }
}

PASO 6: RENDERIZAR (AUTOMÁTICO)
═══════════════════════════════════════════════════════
→ Browser HTML (con CSS + HTML + Script loader)
→ CSS cargado
→ JS ejecutado en window.addEventListener('load')
→ Component inicializado
```

---

## 9. MATRIZ DE DECISIÓN: ¿DÓNDE PONGO ESTO?

```
┌─────────────────────────────────────────────────────────┐
│  DECIDIR DÓNDE COLOCAR NUEVO CÓDIGO                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ¿Es lógica reutilizable del framework?                 │
│ └─ SÍ → Core/                                          │
│    └─ Controllers? → Core/Controller/                  │
│    └─ Modelos? → Core/Models/                          │
│    └─ Servicios? → Core/Services/                      │
│    └─ Helpers? → Core/Helpers/                         │
│    └─ Base de componentes? → Core/Components/          │
│                                                         │
│ ¿Es lógica específica de tu app?                       │
│ └─ SÍ → App/                                           │
│    └─ Controladores auth? → App/Controllers/Auth/      │
│    └─ Modelos de negocio? → App/Models/               │
│    └─ Utilidades globales? → App/Utils/               │
│                                                         │
│ ¿Es UI / Presentación?                                │
│ └─ SÍ → components/                                    │
│    └─ Es core? → components/Core/                     │
│    └─ Es app-specific? → components/App/              │
│    └─ Es reutilizable? → components/shared/           │
│                                                         │
│ ¿Es configuración de rutas?                            │
│ └─ SÍ → Routes/                                        │
│    └─ Rutas web tradicionales? → Routes/Web.php       │
│    └─ APIs? → Routes/Api.php (o #[ApiComponent])      │
│    └─ Vistas? → Routes/Views.php                      │
│                                                         │
│ ¿Es configuración BD?                                  │
│ └─ SÍ → database/                                      │
│    └─ Migraciones base? → database/sql/base/           │
│    └─ Migraciones nuevas? → database/sql/migrations/   │
│    └─ Control versiones? → database/migrations.json    │
│                                                         │
│ ¿Es un script CLI?                                     │
│ └─ SÍ → Core/Commands/                                 │
│    └─ Extiende CoreCommand                            │
│    └─ Auto-descubierto por CommandRouter              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 10. VENTAJAS LEGO vs OTROS FRAMEWORKS

```
LEGO vs LARAVEL
═══════════════════════════════════════════════════════
╔═══════════════════╦═══════════════╦═══════════════════╗
║ Aspecto           ║ LEGO          ║ Laravel           ║
╠═══════════════════╬═══════════════╬═══════════════════╣
║ Curva aprendizaje ║ Muy baja      ║ Moderada-Alta     ║
║ Componentes       ║ Integrados    ║ Blade + assets    ║
║ Tamaño proyecto   ║ Lightweight   ║ Pesado            ║
║ BD                ║ Flexible      ║ Migraciones built  ║
║ Escalabilidad     ║ Buena         ║ Excelente         ║
║ Comunidad         ║ Pequeña       ║ Muy grande        ║
╚═══════════════════╩═══════════════╩═══════════════════╝

LEGO vs NEXTJS/NUXT
═══════════════════════════════════════════════════════
╔═══════════════════╦═══════════════╦═══════════════════╗
║ Aspecto           ║ LEGO          ║ NextJS/Nuxt       ║
╠═══════════════════╬═══════════════╬═══════════════════╣
║ Lenguaje          ║ PHP           ║ JS/TS             ║
║ Componentes       ║ PHP + CSS/JS  ║ JSX/Vue           ║
║ SSR/SSG           ║ Nativo        ║ Built-in          ║
║ API routes        ║ Sí            ║ Sí                ║
║ Aprendizaje PHP   ║ Requerido     ║ No                ║
║ Performance       ║ Buena         ║ Excelente         ║
║ Hosting           ║ Cualquiera    ║ Vercel/specializado
╚═══════════════════╩═══════════════╩═══════════════════╝

VENTAJAS LEGO
═══════════════════════════════════════════════════════
✅ Componentes autocontenidos (PHP + CSS + JS)
✅ Zero-config para empezar (Docker Compose)
✅ CLI integrado (make:component, migrate)
✅ Routing flexible (Web + API + Views)
✅ Autenticación modular (Admin, Api, etc)
✅ JavaScript modernizado (ES6 modules)
✅ Migraciones versionadas
✅ Muy fácil de aprender
```

---

## CONCLUSIÓN: RESUMEN EJECUTIVO

**Lego Framework** es una solución innovadora para desarrolladores que buscan:

1. **Componentes Visuales Reutilizables**
   - PHP + CSS + JS en la misma carpeta
   - Composición natural de componentes

2. **Desarrollo Rápido**
   - CLI para scaffolding
   - Configuración mínima
   - Docker ready

3. **Arquitectura Escalable**
   - De prototipo a empresa
   - Separación clara Core/App/Components
   - Patrones consistentes

4. **Experiencia de Desarrollador**
   - Routing inteligente
   - Autenticación flexible
   - Asset management automático
   - Debugging utilities

5. **Flexibilidad de BD**
   - ORM casero + Eloquent
   - Migraciones versionadas
   - PostgreSQL + Redis + MongoDB

**Filosofía**: "Todo encaja perfectamente como bloques LEGO"

---

## CONTACTO Y RECURSOS

Ubicación del proyecto:
`/Users/serioluisvegamartinez/Documents/GitHub/Lego`

Archivos importantes:
- README.md → Documentación principal
- lego → CLI ejecutable
- docker-compose.yml → Stack completo
- .env.example → Variables de entorno

