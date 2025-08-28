# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) cuando trabaja con código en este repositorio.

## Descripción del Proyecto

LegoPHP es un framework PHP basado en componentes que renderiza la interfaz de usuario desde el servidor. Los componentes son clases autocontenidas que incluyen su HTML (render), JavaScript y CSS en una sola unidad encapsulada, inspirado en los patrones de Flutter y React.

## Comandos de Desarrollo

### Entorno Docker
```bash
# Iniciar el entorno de desarrollo
docker-compose up -d

# Instalar dependencias PHP
docker-compose exec app composer install

# Instalar dependencias Node.js  
npm install
```

### Comandos CLI de Lego
```bash
# Inicializar el proyecto (ejecuta migraciones y mapea rutas)
docker-compose exec app php lego init

# Ejecutar migraciones de base de datos
docker-compose exec app php lego migrate

# Mapear rutas de la aplicación
docker-compose exec app php lego map:routes

# Crear un nuevo componente (aún no implementado)
php lego make:component NombreComponente
```

## Arquitectura

### Estructura del Framework Core
- **Core/**: Funcionalidad central del framework
  - `Components/CoreComponent/`: Clase base de componentes que extienden todos los componentes UI
  - `Controller/`: Controladores centrales y controladores de vista
  - `Models/`: Modelos del framework y DTOs
  - `Services/`: Servicios centrales como autenticación

- **App/**: Lógica específica de la aplicación
  - `Controllers/Auth/`: Controladores de autenticación y proveedores
  - `Models/`: Modelos de aplicación (User, UserSession)
  - `Utils/`: Utilidades como RedisClient

- **Views/**: Componentes UI organizados por funcionalidad
  - Cada directorio de componente contiene: `NombreComponente.php`, `componente.css`, `componente.js`

### Sistema de Componentes
Los componentes extienden `Core\Components\CoreComponent\CoreComponent` y deben implementar:
```php
abstract protected function component(): string;
```

Los componentes manejan automáticamente:
- Importaciones CSS vía propiedad `$CSS_PATHS`
- Importaciones JS vía propiedad `$JS_PATHS`
- JS con argumentos vía propiedad `$JS_PATHS_WITH_ARG`
- Método render que combina HTML, CSS y JS

### Arquitectura de Autenticación
Sistema de autenticación multi-proveedor con:
- Proveedor de autenticación Admin con roles y middlewares
- Proveedor de autenticación API con tokens JWT
- Sistema de permisos basado en grupos

### Base de Datos
- PostgreSQL como base de datos principal
- MongoDB para almacenamiento adicional
- Redis para caché y sesiones
- Sistema de migraciones con seguimiento JSON

## Dependencias Principales
- Flight PHP para enrutamiento (`mikecao/flight`)
- Eloquent ORM (`illuminate/database`)
- Autenticación JWT (`firebase/php-jwt`)
- Carbon para fechas (`nesbot/carbon`)
- Cliente Redis (`predis/predis`)
- Validación (`rakit/validation`)

## URLs de Desarrollo
- Aplicación: http://localhost:8080
- PgAdmin: http://localhost:8081
- PostgreSQL: localhost:5432
- MongoDB: localhost:27017
- Redis: localhost:6379
- N8N (automatización): localhost:5678 (perfil: n8n)

## Convenciones de Nomenclatura de Archivos
- Componentes: `NombreComponente.php` con archivos CSS/JS correspondientes
- Autocarga PSR-4: espacios de nombres `App\`, `Core\`, `Views\`
- Archivos de base de datos en `database/` con seguimiento de migraciones en `migrations.json`