#!/bin/bash
set -e

echo "[Entrypoint] Iniciando LEGO Framework..."

# Verificar si estamos corriendo como root o como usuario
CURRENT_USER=$(id -u)
echo "[Entrypoint] Running as UID: $CURRENT_USER"

# Crear directorios necesarios si no existen
echo "[Entrypoint] Creando directorios necesarios..."
mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/bootstrap/cache \
    /var/www/html/vendor

# Verificar si existe .env, si no, crear desde .env.example o variables de entorno
if [ ! -f "/var/www/html/.env" ]; then
    echo "[Entrypoint] .env no encontrado, creando desde .env.example..."
    if [ -f "/var/www/html/.env.example" ]; then
        cp /var/www/html/.env.example /var/www/html/.env
        echo "[Entrypoint] .env creado desde .env.example"
    else
        echo "[Entrypoint] Creando .env vacío..."
        touch /var/www/html/.env
    fi
fi

# Instalar dependencias de Composer si no existen
if [ ! -d "/var/www/html/vendor" ] || [ -z "$(ls -A /var/www/html/vendor 2>/dev/null)" ]; then
    echo "[Entrypoint] Instalando dependencias de Composer..."
    composer install --no-interaction --optimize-autoloader --no-dev
else
    echo "[Entrypoint] Dependencias ya instaladas, ejecutando dump-autoload..."
    composer dump-autoload --optimize --no-dev
fi

# Establecer permisos correctos
echo "[Entrypoint] Estableciendo permisos..."
chmod -R 775 /var/www/html/storage 2>/dev/null || true
chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true

# Ejecutar php lego init si no se ha ejecutado antes
INIT_FLAG="/var/www/html/storage/.lego-initialized"
if [ ! -f "$INIT_FLAG" ]; then
    echo "[Entrypoint] Ejecutando php lego init (primera vez)..."
    if php lego init; then
        echo "[Entrypoint] Lego init ejecutado exitosamente"
        touch "$INIT_FLAG"
    else
        echo "[Entrypoint] ⚠️  Lego init falló, pero continuando..."
    fi
else
    echo "[Entrypoint] Lego ya está inicializado, omitiendo init..."
fi

echo "[Entrypoint] Iniciando PHP-FPM..."
exec php-fpm
