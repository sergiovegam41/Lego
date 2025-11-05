# Utiliza la imagen oficial de PHP con FPM
# Build version: 2025-11-05-12 (Fix PSR-4 autoloader for App Components childs/)
FROM php:8.3-fpm

# Instala dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libssl-dev \
    pkg-config \
    openssl \
    libpq-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip pdo_pgsql

# Instalar la extensión de Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Descargar e instalar MongoDB con soporte para SSL
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini \
    && docker-php-ext-enable mongodb

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar configuración personalizada de PHP para uploads
COPY docker-php-uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Crear usuario y grupo con ID 1000
RUN addgroup --gid 1000 appuser && \
    adduser --uid 1000 --gid 1000 --disabled-password --gecos "" appuser

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Crear directorios necesarios
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/bootstrap/cache \
    /var/www/html/public \
    /home/appuser/.composer

# Copiar el código del proyecto al contenedor
COPY --chown=appuser:appuser . .

# Instalar dependencias de Composer ANTES de cambiar a appuser
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Establecer permisos
RUN chown -R appuser:appuser /var/www/html /home/appuser/.composer \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public

# Copiar el entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Cambiar al usuario appuser
USER appuser

# Exponer el puerto que usará PHP-FPM
EXPOSE 9000

# Usar entrypoint para manejar composer install y permisos
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
