# Análisis Completo de Lego Framework - Arquitectura del Proyecto

## 1. ESTRUCTURA GENERAL DEL PROYECTO

```
Lego/
├── Core/                          # Motor principal del framework
│   ├── bootstrap.php              # Inicialización, conexión BD, helpers globales
│   ├── Response.php               # Gestión de respuestas
│   ├── Attributes/                # Atributos PHP (ApiComponent)
│   ├── Commands/                  # Sistema CLI
│   ├── Components/                # Clase base para componentes
│   ├── Controller/                # Controladores base (CoreController, CoreViewController)
│   ├── Contracts/                 # Interfaces y contratos
│   ├── Dtos/                      # Data Transfer Objects
│   ├── Helpers/                   # Funciones auxiliares
│   ├── Models/                    # Clase base Model (ORM propio)
│   ├── providers/                 # Providers (StringMethods, Request, Middleware, TimeSet)
│   └── Services/                  # Servicios (ApiRouteDiscovery, AuthServicesCore)
│
├── App/                           # Lógica específica de la aplicación
│   ├── Controllers/               # Controladores específicos
│   │   └── Auth/                  # Sistema de autenticación
│   │       ├── Controllers/       # Endpoints de auth
│   │       ├── Contracts/         # Contratos de auth
│   │       ├── DTOs/              # Data Transfer Objects de auth
│   │       └── Providers/         # Proveedores de auth por grupos
│   ├── Models/                    # Modelos específicos (User, UserSession)
│   └── Utils/                     # Utilidades (global.php, RedisClient)
│
├── components/                    # Componentes visuales (modelo LEGO)
│   ├── Core/                      # Componentes base del framework
│   │   ├── Home/                  # Dashboard principal
│   │   │   ├── HomeComponent.php
│   │   │   ├── home.css
│   │   │   ├── home.js
│   │   │   ├── Components/        # Subcomponentes
│   │   │   │   ├── HeaderComponent/
│   │   │   │   ├── MenuComponent/
│   │   │   │   └── MainComponent/
│   │   │   └── Dtos/              # DTOs del componente
│   │   ├── Login/                 # Componente de login
│   │   └── Automation/            # Componente de automatización
│   ├── App/                       # Componentes específicos de la app
│   │   └── TestButton/
│   └── shared/                    # Componentes reutilizables
│       └── butons/                # Componentes compartidos
│
├── Routes/                        # Sistema de routing
│   ├── Web.php                    # Rutas web tradicionales
│   ├── Api.php                    # Rutas API dinámicas
│   └── Views.php                  # Rutas de vistas
│
├── assets/                        # Recursos estáticos
│   ├── css/                       # Estilos globales
│   │   └── core/                  # Estilos del framework
│   ├── js/                        # Scripts globales
│   │   └── core/                  # Módulos del framework
│   │       ├── modules/
│   │       │   ├── sidebar/
│   │       │   ├── windows-manager/
│   │       │   ├── loading/
│   │       │   ├── storage/
│   │       │   └── theme/
│   │       └── universal-theme-init.js
│   └── images/                    # Imágenes estáticas
│
├── database/                      # Gestión de base de datos
│   ├── migrate.php                # Script de migraciones
│   ├── migrations.json            # Registro de migraciones ejecutadas
│   ├── sql/
│   │   ├── base/                  # Migraciones base
│   │   │   ├── migrations.sql     # Tabla de migraciones
│   │   │   └── inital_structure.sql  # Estructura inicial (usuarios, sesiones)
│   │   └── migrations/            # Migraciones del proyecto
│   └── seeds/                     # Datos de prueba
│
├── public/                        # Punto de entrada público
│   ├── index.php                  # Punto de entrada principal
│   └── router.php                 # Router para archivos estáticos
│
├── AI/                            # Configuración de contratos IA
│   ├── Contracts/                 # Contratos para desarrolladores
│   ├── Bitacora/                  # Registro de cambios
│   └── Utils/                     # Utilidades IA
│
├── lego                           # CLI de Lego Framework
├── composer.json                  # Dependencias PHP
├── .env                           # Variables de entorno
├── docker-compose.yml             # Configuración Docker
└── nginx.conf                     # Configuración Nginx

```

---

## 2. SISTEMA DE COMPONENTES

### Filosofía LEGO
Cada componente es autocontenido con una carpeta que contiene:
- **PHP**: Lógica y HTML
- **CSS**: Estilos específicos
- **JS**: Comportamiento interactivo

### Estructura de un Componente

```
components/Core/Home/
├── HomeComponent.php              # Clase que extiende CoreComponent
├── home.css                       # Estilos
├── home.js                        # Scripts
├── Components/                    # Subcomponentes
│   ├── HeaderComponent/
│   ├── MenuComponent/
│   └── MainComponent/
└── Dtos/                          # Data Transfer Objects

components/App/TestButton/
├── TestButtonComponent.php
├── test-button.css
└── test-button.js

components/shared/butons/
└── select.css                     # Componentes compartidos
```

### Clase CoreComponent

**Ubicación**: `/Users/serioluisvegamartinez/Documents/GitHub/Lego/Core/Components/CoreComponent/CoreComponent.php`

```php
abstract class CoreComponent {
    protected $config;
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct($config) { }
    abstract protected function component(): string;
    
    // Carga CSS, JS y renderiza
    public function render(): string { }
    public function html(): string { }
}
```

**Características**:
- Resolución de rutas relativas (`./`, `../`)
- Importación automática de CSS y JS
- Soporte para scripts con argumentos
- Cache busting con versiones

**Ejemplo de uso**:
```php
class UserCardComponent extends CoreComponent {
    protected $CSS_PATHS = ["./user-card.css"];
    
    public function component(): string {
        return "<div class='user-card'>{$this->config['name']}</div>";
    }
}

$card = new UserCardComponent(['name' => 'Juan']);
echo $card->render();
```

---

## 3. SISTEMA DE ROUTING

### Tipos de Rutas

#### A. Web Routes (`Routes/Web.php`)
- Rutas tradicionales para páginas web
- Usan componentes Core (Login, MainComponent)
- Ejemplo: `GET /admin/`, `GET /login`, `GET /`

```php
Flight::route('GET /admin/', function() {
    if(AdminMiddlewares::isAutenticated()) {
        $component = new MainComponent([]);
        return Response::uri($component->render());
    }
});
```

#### B. API Routes (`Routes/Api.php`)
- Rutas dinámicas descubiertos automáticamente
- Usan el atributo `#[ApiComponent]` en componentes
- Sistema de autenticación integrado

```php
#[ApiComponent('/inicio', methods: ['GET'])]
class HomeComponent extends CoreComponent {
    // ...
}
```

#### C. View Routes (`Routes/Views.php`)
- Rutas para vistas específicas
- No documentado pero existe

### Sistema de Descubrimiento de Rutas

**ApiRouteDiscovery** busca automáticamente componentes con `#[ApiComponent]`:
1. Recorre el directorio `components/`
2. Busca clases que extienden `CoreComponent`
3. Lee el atributo `ApiComponent`
4. Registra la ruta automáticamente con Flight

```php
class ApiRouteDiscovery {
    public static function discover(): void {
        // Encuentra archivos *Component.php
        // Lee atributo #[ApiComponent]
        // Registra con Flight::route()
    }
}
```

### Router Principal (`public/index.php`)

```
REQUEST -> /api/...     -> Routes/Api.php
       -> /view/...     -> Routes/Views.php
       -> otros         -> Routes/Web.php
                            -> Flight::start()
```

**Static Router** (`public/router.php`):
- Sirve assets (CSS, JS, imágenes)
- Sirve archivos de componentes
- Manejo MIME automático

---

## 4. ARQUITECTURA DEL CORE

### 4.1 Bootstrap (`Core/bootstrap.php`)

Inicializa:
- Variables de entorno (`.env`)
- Conexión a PostgreSQL (Illuminate Eloquent)
- Sesiones PHP
- Funciones globales helper

**Conexión BD**:
```php
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

**Funciones Globales Principales**:
- `p()`, `dd()` - Debug output
- `plog()` - Logging
- `consultar()` - Ejecuta queries SQL
- `consultar()`, `insertarSinError()` - CRUD
- `consultaSimple()`, `consultaConParametros()` - Queries parametrizadas

### 4.2 Controllers

#### CoreController (`Core/Controller/CoreController.php`)
- Base para todos los controladores API
- Valida métodos HTTP
- Mapea controladores automáticamente
- Lee de `routeMap.json`

```php
abstract class CoreController implements CoreControllerContract {
    static function mapControllers() {
        // Descubre controladores en App/Controllers
        // Genera objeto con rutas => clases
    }
}
```

#### CoreViewController (`Core/Controller/CoreViewController.php`)
- Base para controladores de vistas
- Similar a CoreController pero para rendering

### 4.3 Models

#### Clase Model (`Core/Models/Model.php`)
ORM casero que proporciona:
- CRUD: `create()`, `read()`, `update()`, `delete()`
- Queries: `where()`, `join()`, `orderBy()`
- Métodos encadenables

```php
abstract class Model {
    public $table;
    public $filables;
    
    public function create($request): self { }
    public function read($request): self { }
    public function update($request): self { }
    public function delete($request): self { }
    public function where($campo, $condicion, $valor): self { }
    public function join(Model $modelo, $campo1, $campo2): self { }
    public function get() { }  // Ejecuta
}
```

**Ejemplo**:
```php
class User extends Model {
    public $table = 'auth_users';
}

$user = new User();
$result = $user->read(['email' => 'admin@lego.com'])->get();
```

También usa **Illuminate/Eloquent**:
```php
class User extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'auth_users';
}
```

### 4.4 Services

#### AuthServicesCore
- Maneja login, token refresh
- JWT con firebase/php-jwt
- Sesiones con Redis

#### ApiRouteDiscovery
- Descubre rutas automáticamente
- Registra con Flight
- Valida autenticación

### 4.5 Helpers y Providers

**LegoHelpers**: Funciones de utilidad
- `redirect()`, `url()` - Navegación

**StringMethods**: Trait con métodos string
**Request**: Gestión de requests
**Middleware**: Sistema de middlewares
**TimeSet**: Gestión de tiempos

---

## 5. SISTEMA CLI

### CLI File (`/lego`)
```php
#!/usr/bin/env php
require __DIR__ . '/vendor/autoload.php';
use Core\Commands\CommandRouter;

$router = new CommandRouter();
$success = $router->execute($commandName, $args);
exit($success ? 0 : 1);
```

### CommandRouter

**Ubicación**: `Core/Commands/CommandRouter.php`

Características:
- Auto-descubre comandos en `Core/Commands/`
- Busca clases que extienden `CoreCommand`
- Lee `$name` y `getDescription()`

```php
class CommandRouter {
    private array $commands = [];
    
    public function __construct() {
        $this->discoverCommands();
    }
    
    public function execute(string $commandName, array $arguments = []): bool {
        $commandClass = $this->commands[$commandName];
        $command = new $commandClass($arguments);
        return $command->execute();
    }
}
```

### CoreCommand (Base)

**Ubicación**: `Core/Commands/CoreCommand.php`

```php
abstract class CoreCommand {
    protected string $name;
    protected string $description;
    protected string $signature;
    protected array $arguments;
    protected array $options;
    
    abstract public function execute(): bool;
    protected function argument(int $index, $default = null);
    protected function option(string $name, $default = null);
    protected function success(string $message): void;
    protected function error(string $message): void;
    protected function info(string $message): void;
    protected function confirm(string $question): bool;
}
```

### Comandos Disponibles

#### 1. **make:component** (MakeComponentCommand)
Crea nuevo componente con estructura completa

```bash
php lego make:component UserCard
php lego make:component UserCard --type=card --path=App
```

Genera:
- `components/App/UserCard/UserCardComponent.php`
- `components/App/UserCard/user-card.css`
- `components/App/UserCard/user-card.js`

#### 2. **migrate** (MigrateCommand)
Ejecuta migraciones de BD

```bash
php lego migrate
```

Procesa:
- `database/sql/base/migrations.sql` - Tabla de migraciones
- `database/sql/base/inital_structure.sql` - Estructura inicial
- Migraciones del proyecto

#### 3. **map:routes** (MapRoutesCommand)
Mapea y guarda rutas en `routeMap.json`

```bash
php lego map:routes
```

#### 4. **init** (InitCommand)
Inicializa proyecto

#### 5. **help** (HelpCommand)
Muestra ayuda de comandos

### Uso del CLI

```bash
php lego help                    # Muestra todos los comandos
php lego help migrate            # Ayuda de comando específico
php lego --version, -v           # Versión
php lego --help, -h              # Ayuda general
```

---

## 6. GESTIÓN DE ASSETS (CSS/JS)

### Estructura de Assets

```
assets/
├── css/
│   └── core/
│       ├── base.css              # Estilos base
│       ├── windows-manager.css   # Manager de ventanas
│       └── sidebar/
│           └── menu-style.css
├── js/
│   ├── core/
│   │   ├── universal-theme-init.js        # Inicialización de tema
│   │   ├── base-lego-framework.js         # Framework principal
│   │   ├── base-lego-login.js             # Login específico
│   │   └── modules/
│   │       ├── sidebar/SidebarScrtipt.js
│   │       ├── windows-manager/
│   │       ├── loading/loadingsScript.js
│   │       ├── storage/storage-manager.js
│   │       └── theme/theme-manager.js
│   └── home/home.js
└── images/
    ├── loading/
    └── ...
```

### Sistema de Carga de Assets

**En Componentes**:
```php
class HomeComponent extends CoreComponent {
    protected $CSS_PATHS = ["./home.css"];
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [
        [new ScriptCoreDTO("./home.js", ["key" => "value"])]
    ];
    
    public function component(): string {
        return "<div>...</div>";
    }
    
    public function render(): string {
        // Genera: <link rel="stylesheet" href="home.css?v=abc123">
        // Genera: <script>window.addEventListener('load', () => window.lego.loadModules([...]))</script>
    }
}
```

### Window/Module Loader (`assets/js/core/modules/windows-manager/loads-scripts.js`)

Carga dinámicamente scripts:
```javascript
window.lego.loadModules([
    'components/Core/Home/home.js',
    'assets/js/custom.js'
]);

window.lego.loadModulesWithArguments({
    context: { url_servidor, id_usuario_actual },
    data: [
        { path: 'script.js', arg: { ... } }
    ]
});
```

### Static Router (`public/router.php`)

Sirve archivos estáticos con MIME correcto:
```
/assets/...                    → files/...
/components/.../*.css|*.js     → components/.../file
```

---

## 7. BASE DE DATOS Y MIGRACIONES

### Configuración Conexión

**`.env`**:
```env
DB_HOST=db
DB_PORT=5432
DB_DATABASE=lego-postgresql-db
DB_USERNAME=lego
DB_PASSWORD=1224
```

**`Core/bootstrap.php`**: Configura Illuminate con estos valores

### Sistema de Migraciones

**Ubicación**: `database/migrate.php`

```php
class Migrations {
    static $BaseSQLs = [
        'migrations' => 'database/sql/base/migrations.sql',
        'auth_users' => 'database/sql/base/inital_structure.sql'
    ];
    
    static function execute() {
        // Verifica tablas base
        // Ejecuta migraciones pendientes
        // Registra en migrations table
    }
}
```

### Flujo de Migraciones

1. **Verificar base**:
   - ¿Existe tabla `migrations`?
   - ¿Existe tabla `auth_users`?
   - Si no, ejecutar `database/sql/base/*.sql`

2. **Ejecutar migraciones**:
   - Leer `database/migrations.json` (lista local)
   - Comparar con `migrations` table (base de datos)
   - Ejecutar diferencias
   - Registrar en BD

3. **Guardar estado**:
   - `migrations.json` - Referencia local
   - `migrations` table - Registro en BD

### Estructura Base

**`database/sql/base/migrations.sql`**:
```sql
CREATE TABLE migrations (
    id serial PRIMARY KEY,
    migration text
);
```

**`database/sql/base/inital_structure.sql`**:
```sql
CREATE TABLE auth_users (
    id SERIAL PRIMARY KEY,
    name character varying,
    email VARCHAR(255),
    password VARCHAR(255),
    status VARCHAR(255),
    auth_group_id VARCHAR(255),
    role_id VARCHAR(255)
);

CREATE TABLE auth_user_sessions (
    id SERIAL PRIMARY KEY,
    auth_user_id INT,
    device_id VARCHAR(255),
    refresh_token TEXT,
    access_token TEXT,
    firebase_token TEXT,
    expires_at TIMESTAMP,
    refresh_expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (auth_user_id, device_id)
);

INSERT INTO auth_users (...) VALUES (
    'admin', 'admin@lego.com', '$2a$12$4QQ...', 'active', 'ADMINS', 'SUPERADMIN'
);
```

### Migraciones Personalizadas

Crear en `database/sql/migrations/*.sql`:
```bash
database/sql/migrations/
├── 2024_10_25_create_products_table.sql
├── 2024_10_25_create_orders_table.sql
└── ...
```

Registrar en `database/migrations.json`:
```json
[
    { "sql": "2024_10_25_create_products_table.sql" },
    { "sql": "2024_10_25_create_orders_table.sql" }
]
```

Ejecutar:
```bash
php lego migrate
```

---

## 8. SISTEMA DE AUTENTICACIÓN

### Estructura Auth

```
App/Controllers/Auth/
├── Controllers/
│   └── AuthGroupsController.php    # Endpoint unificado
├── Contracts/
│   └── AbstractAuthCoreContract    # Interfaz para auth groups
├── DTOs/
│   ├── AuthRequestDTO
│   └── AuthActions
├── Providers/
│   ├── AuthGroupsProvider          # Factory de grupos
│   └── AuthGroups/
│       ├── Admin/
│       │   ├── AdminAuthGroupProvider
│       │   ├── Middlewares/AdminMiddlewares
│       │   ├── Rules/AdminRules
│       │   └── Constants/AdminRoles
│       └── Api/
│           ├── ApiAuthGroupProvider
│           ├── Middlewares/ApiMiddlewares
│           ├── Rules/ApiRules
│           └── Constants/ApiRoles
└── DTOs/
    └── AuthActions (enumeración de acciones)
```

### Flujo de Autenticación

**Request**: `/auth/{group}/{accion}`

```php
Flight::route('POST|GET /auth/@group/@accion', 
    fn ($group, $accion) => new AuthGroupsController($group, $accion));
```

**AuthGroupsController**:
1. Obtiene provider del grupo (Admin, Api, etc.)
2. Valida acción permitida
3. Ejecuta: login, refresh_token, logout, register, getProfile

**Ejemplo Admin**:
```php
class AdminAuthGroupProvider extends AbstractAuthCoreContract {
    public const AUTH_GROUP_NAME = [
        "id" => "ADMINS",
        "route" => "admin",
        "description" => "..."
    ];
    
    public function login(AuthRequestDTO $dto): ResponseDTO {
        $email = $dto->request->request['username'];
        $password = $dto->request->request['password'];
        return (new AuthServicesCore())->coreLogin(
            $email, $password, "ADMINS", $device_id, $firebase_token
        );
    }
}
```

### JWT y Sesiones

- **JWT**: firebase/php-jwt
- **Storage**: Redis
- **Tokens**: access_token + refresh_token
- **Sessions**: auth_user_sessions table
- **Device Management**: device_id para multi-dispositivo

---

## 9. DEPENDENCIAS PRINCIPALES

### Dependencias PHP (composer.json)

```json
{
    "require": {
        "mikecao/flight": "^3.13",           // Framework web
        "illuminate/database": "^11.33",     // Eloquent ORM
        "vlucas/phpdotenv": "^5.6",          // Env vars
        "rakit/validation": "^1.4",          // Validación
        "laravel/serializable-closure": "^2.0",
        "firebase/php-jwt": "^6.11",         // JWT
        "nesbot/carbon": "^3.8",             // Fechas
        "predis/predis": "^2.3"              // Redis
    },
    "autoload": {
        "psr-4": {
            "App\\": "App/",
            "Core\\": "Core/",
            "public\\": "public/",
            "Components\\": "components/"
        }
    }
}
```

### Stack Técnico

| Aspecto | Tecnología |
|--------|-----------|
| Framework Web | Flight (lightweight routing) |
| ORM | Eloquent (Illuminate) + ORM casero |
| Base de Datos | PostgreSQL |
| Cache/Sesiones | Redis |
| Authentication | JWT (firebase/php-jwt) |
| Validación | Rakit/validation |
| Env Config | vlucas/phpdotenv |
| Servidor Web | Nginx (Docker) |
| Contenedor | Docker + Docker Compose |

---

## 10. FLUJO DE REQUEST COMPLETO

### Request HTTP

```
1. Nginx recibe request
   ↓
2. nginx.conf redirige a /public/router.php
   ↓
3. router.php: ¿Es archivo estático?
   ├─ SÍ: Sirve directamente
   └─ NO: Redirige a public/index.php
   ↓
4. index.php:
   - Carga autoload + bootstrap
   - Analiza URI
   ├─ /api/*    → require Routes/Api.php
   ├─ /view/*   → require Routes/Views.php
   └─ otros     → require Routes/Web.php
   ↓
5. Flight::start()
   - Busca ruta coincidente
   ├─ GET /admin/ → Web.php
   ├─ POST /auth/{group}/{accion} → AuthGroupsController
   └─ GET /inicio → ApiRouteDiscovery (si tiene #[ApiComponent])
   ↓
6. Ejecuta handler
   ├─ Crea componente
   ├─ Llama component->render()
   ├─ Retorna HTML/JSON
   └─ Envía respuesta
```

### Ejemplo Web Route

```
GET /admin/
  ↓
Flight::route('GET /admin/', fn() => {
    if(AdminMiddlewares::isAutenticated()) {
        $component = new MainComponent([]);
        return Response::uri($component->render());
    }
})
  ↓
MainComponent::render()
  ├─ Instancia MenuComponent
  ├─ Instancia HeaderComponent
  ├─ Retorna HTML completo
  └─ Response::uri() envía
```

### Ejemplo API Route

```
GET /inicio
  ↓
ApiRouteDiscovery::discover()
  ├─ Lee HomeComponent::class
  ├─ Lee atributo #[ApiComponent('/inicio', methods: ['GET'])]
  └─ Registra Flight::route('GET /inicio', ...)
  ↓
Flight::route('GET /inicio', fn() => {
    $component = new HomeComponent([]);
    return Response::uri($component->render());
})
```

---

## 11. PUNTOS CLAVE DE ARQUITECTURA

### Fortalezas

1. **Componentes Autocontenidos**: PHP + CSS + JS juntos
2. **Routing Flexible**: Web + API + Vistas
3. **CLI Integrado**: Comandos para scaffolding y migraciones
4. **ORM Dual**: Eloquent + ORM casero
5. **Autenticación Modular**: Auth groups (Admin, Api, etc.)
6. **Asset Management**: Carga dinámica de JS y CSS
7. **Migraciones Versioning**: Control de cambios BD
8. **Module Loading**: Sistema de módulos JavaScript

### Puntos a Considerar

1. **ORM Casero**: String interpolation en queries (inyección SQL potencial)
2. **Validación**: Menor validación en migraciones
3. **Testing**: No hay framework de testing visible
4. **Documentación**: Limitada, depende de código
5. **Performance**: Descubrimiento dinámico en cada request (sin caching explícito)

---

## 12. EJEMPLO PRÁCTICO: CREAR UN COMPONENTE

### Paso 1: Generar con CLI
```bash
php lego make:component ProductCard --path=App
```

### Paso 2: Implementar (`components/App/ProductCard/ProductCardComponent.php`)
```php
<?php
namespace Components\App\ProductCard;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

class ProductCardComponent extends CoreComponent {
    protected $CSS_PATHS = ["./product-card.css"];
    
    public function component(): string {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./product-card.js", [
                "productId" => $this->config['id'] ?? null,
                "price" => $this->config['price'] ?? 0
            ])
        ];
        
        return <<<HTML
        <div class="product-card">
            <h3>{$this->config['name']}</h3>
            <p>Price: ${$this->config['price']}</p>
            <button class="add-to-cart">Add to Cart</button>
        </div>
        HTML;
    }
}
```

### Paso 3: Usar en otra vista/componente
```php
$product = new ProductCardComponent([
    'id' => 1,
    'name' => 'Laptop',
    'price' => 999.99
]);

echo $product->render();
```

---

## 13. VARIABLES DE ENTORNO

```env
# App
HOST_NAME=http://localhost:8080

# PostgreSQL
DB_HOST=db
DB_PORT=5432
DB_DATABASE=lego-postgresql-db
DB_USERNAME=lego
DB_PASSWORD=1224

# Admin UI (pgAdmin)
PGADMIN_EMAIL=admin@admin.com
PGADMIN_PASSWORD=admin
PGADMIN_PORT=8081

# MongoDB (no usado en ejemplos)
MONGO_DB_HOST=mongodb
MONGO_DB_PORT=27017
MONGO_DB_DATABASE=lego-mongo-db

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=1224

# JWT
JWT_SECRET=1224

# Docker
UID=1000
GID=1000

# n8n Automation
N8N_ENABLED=true
N8N_LOCAL_INSTANCE=true
N8N_PORT=5678
N8N_DB_TYPE=postgresdb
```

---

## RESUMEN EJECUTIVO

**Lego Framework** es un framework PHP modular basado en componentes reutilizables que combina:

- **Componentes Visuales**: Estructura LEGO (PHP + CSS + JS juntos)
- **Routing Inteligente**: Web, API automática, Vistas
- **CLI Integrado**: Generación de componentes y migraciones
- **Autenticación Modular**: Auth groups con JWT
- **Gestión de Assets**: Carga dinámica de estilos y scripts
- **Base de Datos**: PostgreSQL + Migraciones versionadas
- **Arquitectura Escalable**: De pequeños proyectos a aplicaciones enterprise

**Filosofía**: "Todo encaja perfectamente" - Como bloques LEGO que se ensamblan naturalmente.

