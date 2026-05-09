# Rutas Web

Las rutas web retornan páginas HTML completas. Son los puntos de entrada al SPA — el usuario solo las visita una vez por sesión.

Relacionado: [[routing/tres-capas]] · [[routing/rutas-componentes]]

Código: `Routes/Web.php`

---

## Rutas Actuales

| Ruta | Componente | Descripción |
|------|-----------|-------------|
| `GET /` | Redirect → `/admin/` | Entrada principal |
| `GET /admin/` | `MainComponent` | Shell del SPA (requiere auth) |
| `GET /login` | `LoginComponent` | Página de autenticación |
| `GET /forms-showcase` | `FormsShowcaseComponent` | Demo de formularios |

## Lo que Retorna Cada Ruta

### `/admin/` — MainComponent

Entrega el shell completo de la aplicación:

```html
<!DOCTYPE html>
<html>
<head>
    <!-- CSS globales: theme-variables, base, sidebar -->
    <!-- JS globales: base-lego-framework.js, ApiClient.js, etc. -->
</head>
<body>
    <div id="sidebar"><!-- menú lateral --></div>
    <div id="home-page"><!-- aquí se insertan los componentes SPA --></div>
</body>
</html>
```

Desde este momento, toda la navegación ocurre via requests a `/component/*` sin recargar la página.

### `/login` — LoginComponent

Página independiente, no usa el shell del SPA. Tiene su propio layout y assets mínimos.

## Autenticación en Rutas Web

Las rutas web verifican sesión PHP (no JWT). Si el usuario no está autenticado, redirige a `/login`.

## Registro Manual

A diferencia de las rutas de componente y API, las rutas web se registran **manualmente** en `Routes/Web.php`:

```php
Flight::route('GET /admin/', fn() => (new MainComponent())->render());
Flight::route('GET /login', fn() => (new LoginComponent())->render());
```

No hay auto-descubrimiento para rutas web porque son puntos de entrada del sistema, no componentes reutilizables.

## Visión

> En el futuro, las rutas web soportarán múltiples shells: un shell de administración, un shell de portal público, y un shell embebible para iframes. Cada shell tendrá su propio layout, assets y sistema de autenticación.
