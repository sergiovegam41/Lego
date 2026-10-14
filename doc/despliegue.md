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
| `LEGO_ADMIN_EMAIL` | no | vacío; si se define, crea el primer administrador al arrancar cuando no hay usuarios |
| `LEGO_ADMIN_PASSWORD` | no | vacío; si falta se genera y se imprime una vez en el log del contenedor `app` |
| `LEGO_SESSION_IDLE_MINUTES` | no | `480` |
| `LEGO_SESSION_ABSOLUTE_DAYS` | no | `30` |

## Dokploy

1. Crear un servicio de tipo **Compose** apuntando a este repositorio y a la rama deseada.
2. Ruta del compose: `docker-compose.yml`.
3. En **Environment**, definir al menos `DB_PASSWORD`, `APP_URL` y `LEGO_ADMIN_EMAIL`. Con `APP_URL` en https las cookies son `Secure` y usan el prefijo `__Host-`.
4. En **Domains**, asignar el dominio al servicio `web`, puerto `80`.
5. Desplegar. Los redeploys conservan la base de datos porque vive en un volumen con nombre.
6. Si no se definió `LEGO_ADMIN_PASSWORD`, la contraseña generada aparece una sola vez en el log del servicio `app`. Para cambiarla después: `php lego user:password <correo>` desde la terminal del contenedor.

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

Los tests de base de datos se omiten si no hay Postgres. Para correr la suite completa con Docker:

```bash
docker compose -f compose.test.yml run --rm tests
docker compose -f compose.test.yml down -v
```
