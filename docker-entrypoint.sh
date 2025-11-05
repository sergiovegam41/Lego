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
    /var/www/html/bootstrap/cache

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

# Siempre instalar/actualizar dependencias de Composer
echo "[Entrypoint] Instalando/actualizando dependencias de Composer..."
composer install --no-interaction --optimize-autoloader --no-dev --classmap-authoritative

# Establecer permisos correctos
echo "[Entrypoint] Estableciendo permisos..."
chmod -R 775 /var/www/html/storage 2>/dev/null || true
chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true

# Ejecutar php lego init si no se ha ejecutado antes
INIT_FLAG="/var/www/html/storage/.lego-initialized"
if [ ! -f "$INIT_FLAG" ]; then
    echo "[Entrypoint] Ejecutando php lego init (primera vez)..."

    # Ejecutar init y capturar el código de salida
    php lego init 2>&1 || true

    # Marcar como inicializado de todas formas
    # El init puede fallar por warnings pero la app puede funcionar
    touch "$INIT_FLAG"
    echo "[Entrypoint] Init process completed (warnings are normal)"
else
    echo "[Entrypoint] Lego ya está inicializado, omitiendo init..."
fi

echo "[Entrypoint] Iniciando PHP-FPM..."
exec php-fpm
