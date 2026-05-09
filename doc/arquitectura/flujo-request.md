# Flujo de una Request

Qué sucede exactamente desde que el navegador hace una petición HTTP hasta que el usuario ve una respuesta.

Relacionado: [[arquitectura/vision-general]] · [[routing/tres-capas]] · [[componentes/core-component]]

---

## Mapa Completo

```mermaid
flowchart TD
    Browser[Navegador] -->|HTTP Request| Nginx[Nginx]
    Nginx -->|proxy_pass| PHP[PHP-FPM]
    PHP --> index[public/index.php]
    index -->|autoload + bootstrap| Bootstrap[Core/bootstrap.php\nEloquent · Sesiones · Env]
    Bootstrap --> Router[Core/Router.php]
    
    Router -->|URI comienza con /api/| ApiRoute[Routes/Api.php]
    Router -->|URI comienza con /component/| CompRoute[Routes/Component.php]
    Router -->|cualquier otra ruta| WebRoute[Routes/Web.php]
    
    WebRoute --> FullPage[HTML completo\nMainComponent o LoginComponent]
    
    CompRoute -->|detecta .css o .js| StaticFile[Archivo estático\ncon cache headers]
    CompRoute -->|detecta componente| ComponentRender[Instancia el Component\nRenderiza HTML parcial]
    ComponentRender --> Assets[Inyecta link CSS\ny script JS]
    
    ApiRoute --> Auth[Verifica autenticación]
    Auth -->|token válido| Controller[Controlador]
    Auth -->|token inválido| Error401[401 Unauthorized]
    Controller --> Model[Modelo Eloquent]
    Model --> DB[(PostgreSQL)]
    Model --> Cache[(Redis)]
    Controller --> JSON[Respuesta JSON]
```

## Tres Tipos de Request

### 1. Request Web — Entrada al SPA

```
GET /admin/
  → Routes/Web.php
  → new MainComponent()
  → HTML completo (DOCTYPE, HEAD, BODY)
  → Incluye sidebar, scripts globales, contenedor vacío
```

El usuario solo hace esta request una vez. Todo lo demás son requests de componente o API.

### 2. Request de Componente — Navegación SPA

```
GET /component/usuarios
  → Routes/Component.php
  → Detecta componente con #[ApiComponent('/usuarios')]
  → new UsersComponent()
  → HTML parcial (sin DOCTYPE ni HEAD)
  → JavaScript lo inserta en el contenedor del SPA
```

Los assets (CSS, JS) se inyectan automáticamente como `<link>` y `<script>`.

### 3. Request de API — Datos

```
GET /api/users-config?page=1&sort=name
  → Routes/Api.php
  → Verifica JWT
  → AbstractGetController::list()
  → User::query()->paginate()
  → JSON { data: [...], meta: {...} }
```

## Bootstrap: Lo que sucede antes del Router

`Core/bootstrap.php` ejecuta esto en orden:

1. Carga variables de entorno (`.env`)
2. Conecta Eloquent a PostgreSQL
3. Inicia sesión PHP
4. Registra componentes dinámicos
5. Configura paginación de Illuminate

## Carga de una Página Nueva (primera vez)

```mermaid
sequenceDiagram
    participant B as Navegador
    participant S as Servidor
    participant JS as JavaScript
    
    B->>S: GET /admin/
    S->>B: HTML completo (MainComponent)
    B->>JS: Ejecuta base-lego-framework.js
    JS->>S: GET /component/inicio
    S->>JS: HTML del HomeComponent + link CSS + script JS
    JS->>B: Inserta en #home-page
    JS->>S: GET /api/menu/structure
    S->>JS: JSON con items del menú
    JS->>B: Renderiza sidebar
```

## Visión

> En el futuro, el flujo incluirá pre-carga inteligente: cuando el usuario hace hover sobre un item del menú, el componente ya se descarga en segundo plano. La percepción de velocidad mejora sin cambiar la arquitectura de "servidor como fuente de verdad".
