# Lego Framework - Setup en Termux

## Estado Actual ✅
El proyecto Lego está corriendo exitosamente en Termux usando PHP nativo.

## Servicios Corriendo
- **PHP 8.4.2** - Servidor web en `http://localhost:8080`
- **Composer** - Dependencias PHP instaladas
- **Node.js 24.7.0** - Para assets front-end
- **PM2** - Gestor de procesos

## Comandos Principales

### Iniciar la aplicación
```bash
# Opción 1: Servidor PHP básico
php -S localhost:8080 -t public/

# Opción 2: Con PM2 (recomendado)
pm2 start "php -S localhost:8080 -t public/" --name lego
```

### Gestionar con PM2
```bash
pm2 list            # Ver procesos
pm2 logs lego       # Ver logs
pm2 stop lego       # Detener
pm2 restart lego    # Reiniciar
pm2 delete lego     # Eliminar proceso
```

### Comandos de Lego Framework
```bash
php lego init       # Inicializar (migrar + mapear rutas)
php lego migrate    # Solo migraciones
php lego map:routes # Solo mapear rutas
```

## Base de Datos

### Estado Actual
Las migraciones fallan porque no hay bases de datos configuradas, pero la aplicación web funciona.

### Opciones para Bases de Datos

#### 1. Servicios Gratuitos en la Nube (Recomendado)
- **PostgreSQL**: [Neon.tech](https://neon.tech) - 512MB gratis
- **MongoDB**: [MongoDB Atlas](https://mongodb.com/atlas) - 512MB gratis
- **Redis**: [Upstash](https://upstash.com) - 256MB + 500K comandos/mes

Usa el archivo `.env.cloud` como plantilla.

#### 2. Docker (Alternativo)
Si logras instalar Docker/udocker:
```bash
docker-compose up -d
```

## Archivos de Configuración
- `.env` - Configuración actual (sin BD)
- `.env.cloud` - Plantilla para servicios en la nube
- `.env.example` - Configuración original para Docker

## Acceso a la Aplicación
- **URL**: http://localhost:8080
- **Admin**: http://localhost:8080/admin
- La aplicación redirige automáticamente al login

## Troubleshooting

### Si la aplicación no inicia
1. Verificar que el puerto 8080 esté libre: `netstat -tulpn | grep 8080`
2. Revisar logs de PM2: `pm2 logs lego`
3. Probar sin PM2: `php -S localhost:8080 -t public/`

### Si necesitas reinstalar dependencias
```bash
composer install
npm install
```

### Para desarrollo con Tailwind CSS
```bash
npx tailwindcss build -o public/css/tailwind.css
```

## Próximos Pasos
1. Registrarse en servicios gratuitos de BD
2. Configurar conexiones en `.env`
3. Ejecutar `php lego init` para migraciones
4. Desarrollar funcionalidades personalizadas