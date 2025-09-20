# Lego Framework - Setup en Termux

## Estado Actual ✅
El proyecto Lego está corriendo exitosamente en Termux usando PHP nativo.

## Servicios Corriendo
- **PHP 8.4.2** - Servidor web en `http://localhost:8080`
- **Composer** - Dependencias PHP instaladas
- **Node.js 24.7.0** - Para assets front-end
- **PM2** - Gestor de procesos

## Comandos Principales

### Cómo correr el proyecto correctamente

#### 1. Preparación inicial
```bash
# Navegar al directorio del proyecto
cd /data/data/com.termux/files/home/Lego

# Verificar que las dependencias estén instaladas
composer install
npm install
```

#### 2. Cambiar de rama (si es necesario)
```bash
# Ver ramas disponibles
git branch -a

# Cambiar a una rama específica (ej: test)
git checkout test

# O mantener la rama actual (main)
git checkout main
```

#### 3. Iniciar la aplicación

**Opción A: Servidor PHP básico (para pruebas rápidas)**
```bash
php -S localhost:8080 -t public/
```

**Opción B: Con PM2 (recomendado para desarrollo)**
```bash
# Iniciar con PM2
pm2 start "php -S localhost:8080 -t public/" --name lego

# Verificar que esté corriendo
pm2 list
```

#### 4. Gestión completa del proceso con PM2

**Comandos básicos:**
```bash
pm2 list            # Ver todos los procesos
pm2 logs lego       # Ver logs en tiempo real
pm2 stop lego       # Detener el proceso
pm2 restart lego    # Reiniciar el proceso
pm2 delete lego     # Eliminar el proceso completamente
```

**Workflow completo para cambiar de rama:**
```bash
# 1. Detener el proceso actual
pm2 stop lego

# 2. Cambiar de rama
git checkout nombre-de-rama

# 3. Reinstalar dependencias (si hay cambios)
composer install

# 4. Reiniciar el proceso
pm2 start "php -S localhost:8080 -t public/" --name lego

# 5. Verificar que esté funcionando
curl -I http://localhost:8080
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
1. Verificar que el proceso PM2 no esté ya corriendo: `pm2 list`
2. Si hay un proceso parado, eliminarlo: `pm2 delete lego`
3. Revisar logs de PM2: `pm2 logs lego`
4. Probar sin PM2: `php -S localhost:8080 -t public/`
5. Verificar respuesta del servidor: `curl -I http://localhost:8080`

### Errores comunes
- **Puerto ocupado**: Si el puerto 8080 está ocupado, detén todos los procesos PM2 con `pm2 stop all`
- **Proceso duplicado**: Si aparecen múltiples procesos "lego", elimínalos todos: `pm2 delete all`
- **Cambios no reflejados**: Después de cambiar de rama, siempre ejecuta `pm2 restart lego`

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