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

echo "[Entrypoint] Iniciando PHP-FPM..."
exec php-fpm
