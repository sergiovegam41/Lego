# Lego Framework

Lego es un framework PHP orientado a componentes para construir aplicaciones web de administración. El servidor es la fuente de verdad. No hay estado en el frontend.

> [!tip] Empezar aquí
> Si eres nuevo, lee primero [[arquitectura/vision-general]] y [[arquitectura/flujo-request]]. Si ya conoces el framework, usa este índice como mapa de navegación.

## Núcleo del Framework

- [[arquitectura/vision-general|Visión General]] — Filosofía, principios y cómo encajan las piezas
- [[arquitectura/flujo-request|Flujo de una Request]] — Qué sucede desde que llega una petición HTTP
- [[arquitectura/capas|Capas del Framework]] — Separación de responsabilidades

## Sistema de Componentes

- [[componentes/core-component|CoreComponent]] — La clase base de todo
- [[componentes/composicion|Composición]] — Cómo los componentes se contienen entre sí
- [[componentes/slots|Slots]] — Layouts complejos con zonas nombradas
- [[componentes/assets|Carga de Assets]] — CSS y JS auto-cargados por componente
- [[componentes/pantallas|Pantallas (Screens)]] — Componentes que representan ventanas del sistema
- [[componentes/contexto-componente|Contexto de Componente]] — API de contexto entre PHP y JavaScript

## Routing

- [[routing/tres-capas|Sistema de Routing]] — Las tres capas: Web, Componente, API
- [[routing/rutas-web|Rutas Web]] — Páginas HTML completas
- [[routing/rutas-componentes|Rutas de Componentes]] — HTML parcial para el SPA
- [[routing/rutas-api|Rutas de API]] — Endpoints JSON

## API Automática

- [[api/atributos|Atributos PHP]] — Decoradores que definen rutas y comportamiento
- [[api/crud-automatico|CRUD Automático]] — 5 endpoints generados desde un modelo
- [[api/get-automatico|GET Automático]] — Endpoints de lectura para tablas
- [[api/controladores|Controladores]] — Lógica de negocio y auto-registro

## Autenticación

- [[autenticacion/sistema-auth|Sistema de Auth]] — Arquitectura de autenticación modular
- [[autenticacion/grupos-auth|Grupos de Auth]] — Múltiples grupos de autenticación aislados
- [[autenticacion/jwt|JWT]] — Tokens, firma y ciclo de vida

## Base de Datos

- [[base-de-datos/postgresql|PostgreSQL]] — Motor principal, configuración y conexión
- [[base-de-datos/modelos|Modelos Eloquent]] — ORM, relaciones y scopes
- [[base-de-datos/migraciones|Migraciones]] — Control de esquema y evolución

## Almacenamiento

- [[almacenamiento/minio|MinIO / S3]] — Almacenamiento de objetos compatible con S3
- [[almacenamiento/archivos|Sistema de Archivos]] — Entidades de archivo y asociaciones

## Menú y Navegación

- [[menu/estructura-menu|Estructura del Menú]] — Configuración central del menú lateral
- [[menu/screens-registry|Screen Registry]] — Registro central de pantallas
- [[menu/items-dinamicos|Items Dinámicos]] — Items de menú activados por contexto

## Frontend JavaScript

- [[frontend/window-manager|Window Manager]] — Gestión de módulos/ventanas abiertas
- [[frontend/api-client|API Client]] — Cliente HTTP del frontend
- [[frontend/eventos|Sistema de Eventos]] — Comunicación entre módulos
- [[frontend/temas|Gestión de Temas]] — Modo claro/oscuro y variables CSS

## Infraestructura

- [[infraestructura/docker|Docker]] — Servicios del entorno de desarrollo
- [[infraestructura/redis|Redis]] — Caché y sesiones

## Tests

- [[tests/arquitectura-tests|Tests de Arquitectura]] — Verificación de convenciones con Pest

## Flujos de Trabajo

- [[flows/crear-componente|Crear un Componente]]
- [[flows/crear-crud|Crear un CRUD completo]]
- [[flows/crear-screen|Crear una Pantalla]]
- [[flows/agregar-menu-item|Agregar un Item al Menú]]
- [[flows/agregar-api-endpoint|Agregar un Endpoint API]]
- [[flows/crear-migracion|Crear una Migración]]
