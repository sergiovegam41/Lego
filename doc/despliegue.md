# Despliegue

Lego se despliega igual en Dokploy, en el servidor de casa y en CI. Todo sale del mismo `docker-compose.yml` y de variables de entorno.

## Qué levanta

| Servicio | Imagen | Función |
|---|---|---|
| `app` | `docker/app/Dockerfile` | PHP-FPM con el código y las dependencias dentro. Corre migraciones pendientes al arrancar. |
| `web` | `docker/web/Dockerfile` | nginx sin código. Envía todo a `public/index.php`, que también sirve los assets. Tiene healthcheck en `/health`. |
| `db` | `postgres:17-alpine` | Base de datos. Sus datos viven en el volumen con nombre `lego-db`. |

El compose no fija nombres de contenedor ni puertos del host, para que varias instancias convivan en el mismo servidor.

## Variables de entorno

| Variable | Obligatoria | Valor por defecto |
|---|---|---|
| `DB_PASSWORD` | sí | ninguno; el compose no arranca sin ella |
| `APP_URL` | no | `http://localhost` |
| `APP_DEBUG` | no | `false` |
| `DB_DATABASE`, `DB_USERNAME` | no | `lego` |
| `LEGO_MIGRATE_ON_START` | no | `true` |

## Dokploy

1. Crear un servicio de tipo **Compose** apuntando a este repositorio y a la rama deseada.
2. Ruta del compose: `docker-compose.yml`.
3. En **Environment**, definir al menos `DB_PASSWORD` y `APP_URL`.
4. En **Domains**, asignar el dominio al servicio `web`, puerto `80`.
5. Desplegar. Los redeploys conservan la base de datos porque vive en un volumen con nombre.

Si el proyecto de Dokploy no usa despliegues aislados, el servicio `web` debe estar también en la red `dokploy-network` para que Traefik lo alcance. Ver la documentación de Dokploy sobre Docker Compose.

## Servidor de casa o cualquier máquina con Docker

```bash
cp .env.example .env    # definir DB_PASSWORD
docker compose -f docker-compose.yml -f compose.local.yml up -d --build
```

La aplicación queda en `http://<host>:8080`. El puerto se cambia con `LEGO_HTTP_PORT`.

## Desarrollo sin Docker

Requiere PHP 8.3 y Composer.

```bash
composer install
php lego serve           # http://127.0.0.1:8000
composer test
```
