# LEGO Framework - Dokploy Deployment

Esta carpeta contiene archivos Docker Compose separados para cada servicio del stack LEGO, diseñados para ser desplegados de forma independiente en Dokploy o cualquier otro orquestador de contenedores.

## Arquitectura

Todos los servicios comparten la red **`lego-network`** como red externa, permitiendo la comunicación entre contenedores desplegados por separado.

## Servicios Disponibles

### 1. App + Webserver (`docker-compose.app.yml`)
- **Servicio principal** de la aplicación LEGO (PHP)
- **Nginx** como servidor web
- Permisos automáticos con `init-permissions`
- **Puerto:** 8080

### 2. PostgreSQL (`docker-compose.postgres.yml`)
- Base de datos **PostgreSQL**
- **pgAdmin** para administración web
- **Puertos:** 5432 (PostgreSQL), 8081 (pgAdmin)

### 3. MongoDB (`docker-compose.mongodb.yml`)
- Base de datos **MongoDB**
- **Puerto:** 27017

### 4. Redis (`docker-compose.redis.yml`)
- Cache y almacenamiento en memoria **Redis**
- **Puerto:** 6379

### 5. MinIO (`docker-compose.minio.yml`)
- Almacenamiento de objetos **S3-compatible**
- **Puertos:** 9000 (API), 9001 (Consola)

### 6. n8n (`docker-compose.n8n.yml`)
- Plataforma de **automatización de workflows**
- **Requiere:** PostgreSQL activo
- **Puerto:** 5678

## Orden de Despliegue

### 1. Crear la red externa (solo una vez)

```bash
docker network create lego-network
```

### 2. Desplegar servicios base (en orden)

```bash
# PostgreSQL (requerido para n8n)
docker compose -f dokploy/docker-compose.postgres.yml up -d

# MongoDB
docker compose -f dokploy/docker-compose.mongodb.yml up -d

# Redis
docker compose -f dokploy/docker-compose.redis.yml up -d

# MinIO (opcional)
docker compose -f dokploy/docker-compose.minio.yml up -d
```

### 3. Desplegar aplicación principal

```bash
# App + Webserver
docker compose -f dokploy/docker-compose.app.yml up -d
```

### 4. Desplegar n8n (opcional)

```bash
# n8n (requiere PostgreSQL activo)
docker compose -f dokploy/docker-compose.n8n.yml up -d
```

## Despliegue en Dokploy

En Dokploy, puedes crear un **servicio independiente** para cada archivo `docker-compose.*.yml`.

### Pasos:

1. **Crear la red externa primero:**
   - En Dokploy, ejecuta el comando: `docker network create lego-network`

2. **Crear servicios en orden:**
   - Servicio 1: PostgreSQL → `dokploy/docker-compose.postgres.yml`
   - Servicio 2: MongoDB → `dokploy/docker-compose.mongodb.yml`
   - Servicio 3: Redis → `dokploy/docker-compose.redis.yml`
   - Servicio 4: MinIO → `dokploy/docker-compose.minio.yml`
   - Servicio 5: App → `dokploy/docker-compose.app.yml`
   - Servicio 6: n8n (opcional) → `dokploy/docker-compose.n8n.yml`

3. **Variables de entorno:**
   - Cada servicio debe tener acceso al archivo `.env` del proyecto
   - O configurar las variables de entorno en Dokploy directamente

### ⚠️ IMPORTANTE: Rutas Relativas

Los archivos `docker-compose` en esta carpeta usan **rutas relativas** (`..`) porque asumen que:
- El repositorio se clona en el servidor
- Los archivos compose están en `dokploy/`
- Los archivos del proyecto (Dockerfile, nginx.conf, etc.) están en la raíz (`../`)

**En Dokploy, esto funciona automáticamente** porque:
1. Dokploy clona el repo completo: `/path/to/repo/`
2. Los compose están en: `/path/to/repo/dokploy/`
3. Las rutas `..` apuntan correctamente a: `/path/to/repo/`

## Variables de Entorno Importantes

### PostgreSQL
```env
DB_DATABASE=lego-postgresql-db
DB_USERNAME=lego
DB_PASSWORD=1224
DB_PORT=5432
N8N_DB_DATABASE=n8n
```

### MongoDB
```env
MONGO_DB_USERNAME=lego
MONGO_DB_PASSWORD=1224
MONGO_DB_PORT=27017
```

### Redis
```env
REDIS_PASSWORD=1224
REDIS_USER=lego
REDIS_PORT=6379
```

### MinIO
```env
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin123
MINIO_PORT=9000
MINIO_CONSOLE_PORT=9001
```

### n8n
```env
DOMAIN_NAME=domain.example.com
N8N_PORT=5678
N8N_PROTOCOL=https
NODE_ENV=production
GENERIC_TIMEZONE=America/Mexico_City
```

### App
```env
UID=1000
GID=1000
```

## Verificar Conectividad

Para verificar que todos los servicios pueden comunicarse:

```bash
# Verificar red
docker network inspect lego-network

# Verificar contenedores en la red
docker network inspect lego-network --format='{{range .Containers}}{{.Name}} {{end}}'

# Ping desde un contenedor a otro
docker exec lego-php ping -c 3 lego-postgres-db
docker exec lego-php ping -c 3 lego-mongo-db
docker exec lego-php ping -c 3 lego-redis
```

## Detener Servicios

```bash
# Detener servicio específico
docker compose -f dokploy/docker-compose.app.yml down

# Detener todos los servicios
docker compose -f dokploy/docker-compose.app.yml down
docker compose -f dokploy/docker-compose.postgres.yml down
docker compose -f dokploy/docker-compose.mongodb.yml down
docker compose -f dokploy/docker-compose.redis.yml down
docker compose -f dokploy/docker-compose.minio.yml down
docker compose -f dokploy/docker-compose.n8n.yml down

# Eliminar la red (solo si no hay servicios corriendo)
docker network rm lego-network
```

## Notas Importantes

1. **Red Externa:** La red `lego-network` debe existir ANTES de desplegar cualquier servicio
2. **Dependencias:** n8n requiere PostgreSQL activo para iniciar
3. **Volúmenes:** Cada servicio crea sus propios volúmenes nombrados
4. **Puertos:** Asegúrate de que los puertos no estén en uso en el host
5. **Contexto de Build:** Para `docker-compose.app.yml`, el contexto es la raíz del proyecto

## Troubleshooting

### Error: "network lego-network not found"
```bash
docker network create lego-network
```

### Error: "port already in use"
Cambia el puerto en el archivo `.env` correspondiente

### App no puede conectar a base de datos
Verifica que ambos contenedores estén en la misma red:
```bash
docker network inspect lego-network
```

### Ver logs de un servicio
```bash
docker compose -f dokploy/docker-compose.app.yml logs -f
```
