# Lego Framework

Lego es un framework PHP ligero y moderno que proporciona una estructura base para desarrollar aplicaciones web. Está construido con Docker para facilitar el desarrollo y despliegue.

## Características

- Framework PHP ligero y moderno
- Soporte para PostgreSQL y MongoDB
- Redis para caché
- Sistema de migraciones
- Mapeo automático de rutas
- Docker para desarrollo y producción
- PgAdmin para gestión de base de datos
- Validación de datos
- JWT para autenticación
- Carbon para manejo de fechas

## Requisitos

- Docker
- Docker Compose
- PHP 8.1 o superior
- Composer

## Instalación

1. Clonar el repositorio:
```bash
git clone [URL_DEL_REPOSITORIO]
cd lego
```

2. Copiar el archivo de entorno:
```bash
cp .env.example .env
```

3. Construir y levantar los contenedores Docker:
```bash
docker-compose up -d
```

4. Instalar dependencias de Composer:
```bash
docker-compose exec app composer install
```

5. Ejecutar las migraciones iniciales:
```bash
docker-compose exec app php lego migrate
```

6. Mapear las rutas:
```bash
docker-compose exec app php lego map:routes
```

## Estructura del Proyecto

```
lego/
├── App/            # Lógica de la aplicación
├── Core/           # Núcleo del framework
├── database/       # Migraciones y scripts SQL
├── public/         # Archivos públicos
├── Routes/         # Definición de rutas
├── Views/          # Plantillas y vistas
├── assets/         # Recursos estáticos
└── vendor/         # Dependencias
```

## Comandos Disponibles

### Migraciones
```bash
php lego migrate
```
Ejecuta las migraciones pendientes en la base de datos. Las migraciones se dividen en dos tipos:
- Migraciones base: Estructura inicial de la base de datos
- Migraciones de aplicación: Cambios específicos de la aplicación

### Mapeo de Rutas
```bash
php lego map:routes
```
Genera un archivo `routeMap.json` con todas las rutas disponibles en la aplicación.

## Servicios Disponibles

- **Aplicación PHP**: http://localhost:8080
- **PgAdmin**: http://localhost:8081
- **PostgreSQL**: localhost:5432
- **MongoDB**: localhost:27017
- **Redis**: localhost:6379

## Configuración

### Variables de Entorno
El archivo `.env` contiene las siguientes configuraciones principales:

- `DB_DATABASE`: Nombre de la base de datos PostgreSQL
- `DB_USERNAME`: Usuario de PostgreSQL
- `DB_PASSWORD`: Contraseña de PostgreSQL
- `MONGO_DB_USERNAME`: Usuario de MongoDB
- `MONGO_DB_PASSWORD`: Contraseña de MongoDB
- `REDIS_PASSWORD`: Contraseña de Redis
- `PGADMIN_EMAIL`: Email para PgAdmin
- `PGADMIN_PASSWORD`: Contraseña para PgAdmin

## Desarrollo

### Crear una Nueva Migración

1. Crear un archivo SQL en `database/sql/` con el nombre descriptivo de la migración
2. Ejecutar `php lego migrate` para aplicar la migración

### Agregar Nuevas Rutas

1. Crear el controlador en `App/Controllers/`
2. Definir la ruta en `Routes/`
3. Ejecutar `php lego map:routes` para actualizar el mapa de rutas

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

