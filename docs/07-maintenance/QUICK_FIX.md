# Quick Fix - Forms Showcase 404

## El Problema
Estás recibiendo un `404 Not Found` al acceder a `http://localhost:8080/component/forms-showcase`

## La Solución Rápida

### Opción 1: Reiniciar el servidor (MÁS PROBABLE)

El auto-discovery de componentes se ejecuta **UNA SOLA VEZ** cuando el servidor inicia. Después de agregar nuevos componentes, debes reiniciar:

```bash
# Si usas Docker:
docker-compose restart

# Si usas PHP built-in server local:
# 1. Encuentra el proceso
ps aux | grep "php -S"

# 2. Mata el proceso
kill <PID>

# 3. Reinicia
php -S localhost:8080 -t . Router.php
```

### Opción 2: Verificar que los archivos existan en el servidor

Si usas Docker, verifica que el volumen esté correctamente montado:

```bash
# Entra al contenedor
docker exec -it <tu-contenedor> bash

# Verifica que los archivos existan
ls -la components/App/FormsShowcase/
ls -la components/shared/Forms/

# Si NO ves los archivos, el volumen no está montado correctamente
# Revisa tu docker-compose.yml y asegúrate de tener:
#   volumes:
#     - .:/var/www/html
```

### Opción 3: Verificar composer autoload

```bash
composer dump-autoload
```

## Verificación

Después de reiniciar, prueba:

```bash
curl http://localhost:8080/component/forms-showcase
```

Si funciona, deberías ver HTML del componente.

## Si aún no funciona

Ejecuta el script de diagnóstico:

```bash
php test-autodiscovery.php
```

Y revisa el archivo [TROUBLESHOOTING.md](TROUBLESHOOTING.md) para pasos detallados de debugging.

## Nota Importante

**El servidor SIEMPRE debe reiniciarse después de:**
- Agregar nuevos componentes con decorador `#[ApiComponent]`
- Modificar rutas en decoradores
- Agregar nuevas clases en directorios escaneados por auto-discovery

Esto es porque Flight PHP cachea las rutas en memoria cuando el servidor inicia.
