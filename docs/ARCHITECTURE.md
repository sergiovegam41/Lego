# 🏗️ Arquitectura Técnica - Lego Framework

> Documentación técnica completa del sistema de arquitectura y decisiones de diseño

---

## 📑 Tabla de Contenidos

1. [Visión General](#-visión-general)
2. [Sistema de Routing en 3 Capas](#-sistema-de-routing-en-3-capas)
3. [Flujo de Request-Response](#-flujo-de-request-response)
4. [Sistema de Componentes](#-sistema-de-componentes)
5. [Sistema de Symlinks](#-sistema-de-symlinks)
6. [Autenticación Modular](#-autenticación-modular)
7. [Auto-Discovery](#-auto-discovery)
8. [Decisiones de Diseño](#-decisiones-de-diseño)
9. [Best Practices](#-best-practices)

---

## 🎯 Visión General

Lego Framework es un **framework PHP minimalista** que implementa una arquitectura **SPA híbrida** con filosofía **"Sin estado en frontend"**.

### Principios Fundamentales

1. **Componentes autocontenidos**: Cada componente incluye su PHP, CSS y JS
2. **Sin estado en frontend**: El backend es la única fuente de verdad
3. **Auto-discovery**: Los componentes se registran automáticamente
4. **Separación clara**: 3 capas de routing independientes
5. **Assets co-ubicados**: CSS/JS junto al componente PHP

### Stack Tecnológico

```
Frontend:
- Vanilla JavaScript (ES6+)
- Variables CSS nativas
- Módulos dinámicos

Backend:
- PHP 8.3+
- Flight PHP (micro-framework)
- Eloquent ORM
- JWT Auth

Infraestructura:
- Nginx
- Docker + Docker Compose
- PostgreSQL
- Redis
- MongoDB
```

---

## 🛤️ Sistema de Routing en 3 Capas

### Arquitectura Visual

```
┌─────────────────────────────────────────┐
│         Usuario en Navegador            │
└──────────────┬──────────────────────────┘
               │
               ↓
        ┌──────────────┐
        │    Nginx     │ (Port 80)
        │ DocumentRoot │
        │  /public     │
        └──────┬───────┘
               │
               ↓
    ┌──────────────────────┐
    │  public/index.php    │
    │  Entry Point         │
    └──────┬───────────────┘
           │
           │ Bootstrap + Delega
           ↓
    ┌──────────────────────┐
    │   Core/Router.php    │
    │   Lógica de Routing  │
    └──────┬───────────────┘
           │
           │ Analiza URI
           ↓
    ┌──────────────────────────┐
    │ Primer Segmento de URI?  │
    └──────┬───────────────────┘
           │
  ┌────────┼──────────┬──────────┐
  │        │          │          │
 /api/*  /component/*  /*          /
  │          │          │          │
  ↓          ↓          ↓          ↓
┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐
│ API  │  │ Comp │  │ Web  │  │ Web  │
│ .php │  │ .php │  │ .php │  │ .php │
└──┬───┘  └──┬───┘  └──┬───┘  └──┬───┘
   │          │          │          │
   ↓          ↓          ↓          ↓
 JSON      HTML +      HTML      301
          Assets    completo  redirect
         (parcial)
```

### Decisiones de Diseño

**¿Por qué 3 capas?**

1. **Separación de responsabilidades**: Cada tipo de respuesta tiene su capa
2. **Escalabilidad**: Fácil agregar nuevas capas si es necesario
3. **Claridad**: Cualquier desarrollador sabe dónde buscar
4. **Mantenibilidad**: Cambios en una capa no afectan las otras

---

## 🔄 Flujo de Request-Response

### Ejemplo 1: Carga Inicial (Web Route)

```
[Browser]
   │
   │ GET http://localhost:8080/admin
   ↓
[Nginx]
   │
   │ DocumentRoot: /public
   │ Busca: /public/admin (no existe)
   │ try_files fallback → index.php
   ↓
[public/index.php]
   │
   │ Bootstrap + Delega
   │ → Core\Router::dispatch()
   ↓
[Core/Router.php]
   │
   │ Parse URI: "admin/"
   │ No empieza con "api/" ni "component/"
   │ → require Routes/Web.php
   ↓
[Routes/Web.php]
   │
   │ Flight::route('GET /admin/')
   │ Verifica autenticación (AdminMiddlewares)
   │ → new MainComponent([])
   ↓
[MainComponent]
   │
   │ Renderiza HTML COMPLETO:
   │ - <!DOCTYPE html>
   │ - <head> con CSS base
   │ - <body> con:
   │    - MenuComponent (sidebar)
   │    - HeaderComponent
   │    - <div id="home-page"></div>
   │ - Scripts: base-lego-framework.js
   ↓
[Response]
   │
   │ HTTP 200
   │ Content-Type: text/html
   │ Body: HTML completo
   ↓
[Browser]
   │
   └→ Usuario ve layout SPA completo
```

### Ejemplo 2: Refresco de Componente (Component Route)

```
[Browser - JavaScript]
   │
   │ User click menú "Inicio"
   │ Window Manager → fetch('/component/inicio')
   ↓
[Nginx]
   │
   │ /component/inicio → index.php
   ↓
[public/index.php]
   │
   │ Bootstrap + Delega
   │ → Core\Router::dispatch()
   ↓
[Core/Router.php]
   │
   │ Parse URI: "component/inicio/"
   │ Empieza con "component/"
   │ → Remueve "component/", queda "/inicio"
   │ → require Routes/Component.php
   ↓
[Routes/Component.php]
   │
   │ ApiRouteDiscovery::discover()
   │ Escanea /components recursivamente
   │ Encuentra HomeComponent.php
   │ Lee atributo #[ApiComponent('/inicio')]
   │ Registra: Flight::route('GET /inicio')
   ↓
[HomeComponent]
   │
   │ Renderiza SOLO contenido:
   │ - <div class="dashboard-container">
   │ - No incluye DOCTYPE/HEAD/BODY
   │ - Solo el HTML del módulo
   ↓
[Response]
   │
   │ HTTP 200
   │ Content-Type: text/html
   │ Body: HTML parcial (solo componente)
   ↓
[Browser - JavaScript]
   │
   │ Recibe HTML
   │ document.getElementById('home-page').innerHTML = html
   │ Inserta CSS/JS del componente
   └→ Usuario ve nuevo contenido SIN recargar página
```

### Ejemplo 3: API Call (API Route)

```
[Browser - JavaScript]
   │
   │ fetch('/api/auth/admin/login', {
   │   method: 'POST',
   │   body: { username, password }
   │ })
   ↓
[Nginx]
   │
   │ /api/auth/admin/login → index.php
   ↓
[public/index.php]
   │
   │ Parse URI: "api/auth/admin/login/"
   │ Empieza con "api/"
   │ → Remueve "api/", queda "/auth/admin/login"
   │ → require Routes/Api.php
   ↓
[Routes/Api.php]
   │
   │ Flight::route('POST|GET /auth/@group/@accion')
   │ Params: group="admin", accion="login"
   │ → new AuthGroupsController('admin', 'login')
   ↓
[AuthGroupsController]
   │
   │ Valida request (username, password required)
   │ → AuthGroupsProvider->handle()
   │ → Busca usuario en DB
   │ → Genera JWT token
   │ → Retorna ResponseDTO
   ↓
[Response]
   │
   │ HTTP 200
   │ Content-Type: application/json
   │ Body: { "token": "...", "user": {...} }
   ↓
[Browser - JavaScript]
   │
   └→ Guarda token, redirige a /admin
```

---

## 🧩 Sistema de Componentes

### Anatomía de un Componente

```
components/App/Dashboard/
├── DashboardComponent.php   ← Lógica + HTML
├── dashboard.css            ← Estilos
└── dashboard.js             ← Comportamiento
```

**DashboardComponent.php:**
```php
<?php
namespace Components\App\Dashboard;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;

#[ApiComponent('/dashboard', methods: ['GET'])]
class DashboardComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./dashboard.css"];

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./dashboard.js", [
                'userId' => $this->config['userId'] ?? null
            ])
        ];

        return <<<HTML
        <div class="dashboard-container">
            <h1>Dashboard</h1>
            <div class="stats">...</div>
        </div>
        HTML;
    }
}
```

### Resolución de Rutas Relativas

El framework implementa un sistema de resolución automática de rutas relativas:

```php
protected $CSS_PATHS = ["./dashboard.css"];
// Se resuelve a: components/App/Dashboard/dashboard.css

protected $CSS_PATHS = ["./styles/main.css"];
// Se resuelve a: components/App/Dashboard/styles/main.css

protected $CSS_PATHS = ["../shared/utils.css"];
// Se resuelve a: components/App/shared/utils.css
```

**Implementación** (`CoreComponent::resolveRelativePath()`):
1. Detecta si la ruta empieza con `./` o `../`
2. Usa Reflection para obtener ubicación del archivo PHP
3. Construye ruta absoluta relativa al componente
4. Retorna ruta pública accesible vía HTTP

---

## 🔗 Sistema de Symlinks

### Problema

```
Nginx sirve desde:     /public/
Componentes están en:  /components/
Assets están en:       /assets/

¿Cómo servir CSS/JS de componentes vía HTTP?
```

### Solución: Enlaces Simbólicos

```
/public/
├── index.php
├── components -> ../components/   (symlink)
└── assets -> ../assets/           (symlink)
```

### Configuración Nginx

```nginx
# Servir assets globales
location /assets/ {
    alias /var/www/html/assets/;
}

# Servir assets de componentes
location ~ ^/components/(.+\.(js|css))$ {
    alias /var/www/html/components/$1;
}
```

### Flujo de Acceso

```
Browser solicita:
http://localhost/components/App/Dashboard/dashboard.css
            ↓
Nginx busca en DocumentRoot:
/public/components/App/Dashboard/dashboard.css
            ↓
Encuentra symlink:
/public/components -> ../components/
            ↓
Resuelve a:
/components/App/Dashboard/dashboard.css
            ↓
Sirve archivo ✅
```

### Ventajas

- ✅ Assets co-ubicados con componentes
- ✅ Sin duplicación de archivos
- ✅ Mejor experiencia de desarrollo
- ✅ Seguridad: código PHP fuera de DocumentRoot

---

## 🔐 Autenticación Modular

### Arquitectura

```
App/Controllers/Auth/
├── Controllers/
│   └── AuthGroupsController.php
├── Providers/
│   ├── AuthGroupsProvider.php
│   └── AuthGroups/
│       ├── Admin/              ← Grupo 1
│       │   ├── Rules/
│       │   ├── Middlewares/
│       │   └── AdminAuthProvider.php
│       ├── Api/                ← Grupo 2
│       │   ├── Rules/
│       │   ├── Middlewares/
│       │   └── ApiAuthProvider.php
│       └── Constants/
└── DTOs/
    ├── AuthRequestDTO.php
    └── AuthActions.php
```

### Grupos de Autenticación

**Admin:** Usuarios del sistema Lego
- Login con email/password
- Sesiones con JWT
- Roles: admin, editor, viewer
- Middleware: AdminMiddlewares

**Api:** Tokens para consumo externo
- Login con API key
- Tokens con refresh
- Rate limiting
- Middleware: ApiMiddlewares

### Extensibilidad

Para agregar un nuevo grupo (ej: "Customer"):

1. Crear carpeta: `AuthGroups/Customer/`
2. Implementar: `CustomerAuthProvider.php`
3. Definir reglas: `Customer/Rules/`
4. Crear middlewares: `Customer/Middlewares/`
5. ¡Listo! Ya funciona con `/api/auth/customer/login`

### Rutas de Autenticación

```
POST /api/auth/{group}/{action}

Ejemplos:
POST /api/auth/admin/login
POST /api/auth/admin/logout
POST /api/auth/admin/refresh_token
POST /api/auth/api/login
POST /api/auth/api/refresh_token
POST /api/auth/customer/register
```

---

## 🔍 Auto-Discovery

### Proceso

```
1. Routes/Component.php carga
   │
   ↓
2. ApiRouteDiscovery::discover() ejecuta
   │
   ↓
3. Escanea recursivamente /components
   │
   ↓
4. Encuentra archivos *Component.php
   │
   ↓
5. Usa Reflection para leer atributos
   │
   ↓
6. Busca #[ApiComponent]
   │
   ↓
7. Si encuentra, registra ruta en Flight
   │
   ↓
8. Ruta disponible automáticamente
```

### Implementación

**Core/Services/ApiRouteDiscovery.php:**
```php
public static function discover(): void
{
    $componentsPath = __DIR__ . '/../../components';
    $componentFiles = self::findComponentFiles($componentsPath);

    foreach ($componentFiles as $file) {
        self::registerApiRoute($file);
    }
}

private static function registerApiRoute(string $filePath): void
{
    $className = self::extractClassName($filePath);
    $reflection = new ReflectionClass($className);
    $attributes = $reflection->getAttributes(ApiComponent::class);

    if (empty($attributes)) {
        return;
    }

    $apiConfig = $attributes[0]->newInstance();

    foreach ($apiConfig->methods as $method) {
        Flight::route("$method {$apiConfig->path}", function() use ($className) {
            $component = new $className([]);
            return Response::uri($component->render());
        });
    }
}
```

### Ventajas

- ✅ Cero configuración manual
- ✅ Solo decorar componente
- ✅ Registro automático
- ✅ Menos código repetitivo

---

## 💡 Decisiones de Diseño

### 1. ¿Por qué "Sin Estado en Frontend"?

**Problema con estado en frontend:**
- Sincronización compleja
- Bugs de estado desincronizado
- Cache invalidation es difícil
- Más código, más bugs

**Solución Lego:**
- Siempre refresca desde servidor
- Backend como única fuente de verdad
- Desarrollo más simple
- Información siempre actualizada

**Trade-off:**
- ⚠️ Más requests HTTP
- ✅ Pero más simple y sin bugs de estado

### 2. ¿Por qué Auto-Discovery?

**Alternativa:** Registro manual de rutas

**Problema:**
```php
// Registro manual repetitivo
Flight::route('GET /dashboard', fn() => new DashboardComponent());
Flight::route('GET /users', fn() => new UsersComponent());
Flight::route('GET /products', fn() => new ProductsComponent());
// ... etc
```

**Solución Lego:**
```php
// Solo decorador
#[ApiComponent('/dashboard')]
class DashboardComponent { ... }
```

**Ventajas:**
- Menos código
- Componente autocontenido
- Declarativo vs imperativo

### 3. ¿Por qué Symlinks para Assets?

**Alternativas consideradas:**

**A) Copiar assets a public/**
- ❌ Duplicación de archivos
- ❌ Build step necesario
- ❌ Sincronización manual

**B) Mover componentes a public/**
- ❌ Código PHP expuesto
- ❌ Problema de seguridad
- ❌ Mala organización

**C) Symlinks (elegida)**
- ✅ Sin duplicación
- ✅ Código seguro
- ✅ Assets co-ubicados
- ✅ Sin build step

### 4. ¿Por qué 3 Capas de Routing?

**Alternativa:** Una sola capa para todo

**Problema:**
- Mezcla de responsabilidades
- Difícil de mantener
- Confuso para nuevos devs

**Solución Lego:**
- API → JSON
- Component → HTML parcial
- Web → HTML completo

**Ventaja:** Cada dev sabe exactamente dónde poner su código

---

## ✅ Best Practices

### Crear Componentes

**✅ DO:**
```php
#[ApiComponent('/users/dashboard')]
class UserDashboardComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./user-dashboard.css"];

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./user-dashboard.js", [
                'userId' => $this->config['userId']
            ])
        ];

        return "<div>...</div>";
    }
}
```

**❌ DON'T:**
```php
// Sin decorador (no se auto-registra)
class UserDashboardComponent extends CoreComponent
{
    // Rutas absolutas largas
    protected $CSS_PATHS = ["components/App/Users/user-dashboard.css"];

    protected function component(): string
    {
        // Retornar HTML completo en componente SPA
        return "<!DOCTYPE html><html>...";  // ❌ MALO
    }
}
```

### Usar Variables CSS

**✅ DO:**
```css
.dashboard {
    padding: var(--space-xl);
    background: var(--bg-surface);
    color: var(--text-primary);
    border-radius: var(--radius-card);
}
```

**❌ DON'T:**
```css
.dashboard {
    padding: 24px;           /* ❌ hardcoded */
    background: #ffffff;     /* ❌ hardcoded */
    color: #333;             /* ❌ hardcoded */
    border-radius: 8px;      /* ❌ hardcoded */
}
```

### Organizar Rutas

**✅ DO:**
- Web Routes → Páginas completas (MainComponent, LoginComponent)
- Component Routes → Módulos SPA (con #[ApiComponent])
- API Routes → Endpoints REST (retornan JSON)

**❌ DON'T:**
- Mezclar tipos de rutas
- Registrar manualmente componentes SPA en Web.php
- Retornar HTML en API routes

---

## 📚 Recursos Adicionales

- [README.md](../README.md) - Guía de inicio rápido
- [AI/Contracts/](../AI/Contracts/) - Contratos de desarrollo
- [Routes/](../Routes/) - Código de routing con comentarios
- [Core/Services/ApiRouteDiscovery.php](../Core/Services/ApiRouteDiscovery.php) - Implementación auto-discovery

---

**Documento actualizado:** 25 de Octubre 2025
**Versión del framework:** 1.0.0-alpha
