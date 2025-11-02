# Troubleshooting - Forms Showcase 404

Si estás recibiendo un error 404 al intentar acceder a `/component/forms-showcase`, sigue estos pasos:

## 1. Verificar que los archivos existan en el servidor

```bash
# Si estás usando Docker, entra al contenedor
docker exec -it <container-name> bash

# Verifica que los directorios existan
ls -la /var/www/html/components/App/FormsShowcase/
ls -la /var/www/html/components/shared/Forms/

# Deberías ver:
# - FormsShowcaseComponent.php
# - forms-showcase.css
# - forms-showcase.js
# Y todos los componentes en shared/Forms/
```

## 2. Verificar permisos de archivos

```bash
# Asegúrate de que el servidor web pueda leer los archivos
chmod -R 755 /var/www/html/components/App
chmod -R 755 /var/www/html/components/shared
```

## 3. Ejecutar el script de prueba de auto-discovery

```bash
# Desde el directorio raíz del proyecto
php test-autodiscovery.php
```

Esto te dirá si:
- ✓ El archivo FormsShowcaseComponent.php existe
- ✓ El namespace es correcto
- ✓ El decorador #[ApiComponent] está presente
- ✓ La clase se puede instanciar

## 4. Verificar logs de PHP

```bash
# Ver logs del servidor PHP
tail -f /var/log/php/error.log
# o si usas PHP built-in server
tail -f /tmp/php-server.log
```

Busca errores como:
- `Class not found`
- `Failed to open directory`
- `Parse error`

## 5. Limpiar caché de Composer (si aplica)

```bash
composer dump-autoload
```

## 6. Reiniciar el servidor

```bash
# Si usas PHP built-in server
pkill -f "php -S"
php -S localhost:8080 -t . Router.php

# Si usas Docker
docker-compose restart

# Si usas Apache/Nginx
sudo service apache2 restart
# o
sudo service nginx restart
sudo service php-fpm restart
```

## 7. Verificar que ApiRouteDiscovery funcione

Agrega esto temporalmente en `Routes/Component.php` antes de la línea `ApiRouteDiscovery::discover();`:

```php
// Debug: mostrar componentes descubiertos
error_log("=== Iniciando auto-discovery ===");
```

Y en `Core/Services/ApiRouteDiscovery.php`, agrega logs en el método `registerApiRoute`:

```php
private static function registerApiRoute(string $filePath): void
{
    error_log("Procesando: $filePath");

    $className = self::extractClassName($filePath);
    error_log("Clase encontrada: $className");

    // ... resto del código
}
```

## 8. Verificar el error exacto

Accede directamente a la URL y verifica el error:

```bash
curl -v http://localhost:8080/component/forms-showcase
```

Esto te mostrará:
- Código de respuesta HTTP
- Headers
- Cuerpo de la respuesta

## 9. Problema común: Shared components

Si el error dice `Failed to open directory: components/shared`, es porque el auto-discovery intenta escanear ese directorio pero hay un problema. La solución ya está implementada en `ApiRouteDiscovery.php` con el try-catch.

Verifica que la versión actualizada de `ApiRouteDiscovery.php` tenga:

```php
try {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        ),
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );
    // ...
} catch (\Exception $e) {
    error_log("ApiRouteDiscovery: Error scanning $path - " . $e->getMessage());
    return [];
}
```

## 10. Verificar manualmente la ruta

Como último recurso, puedes registrar la ruta manualmente en `Routes/Component.php`:

```php
use Components\App\FormsShowcase\FormsShowcaseComponent;

Flight::route('GET /forms-showcase', function() {
    $component = new FormsShowcaseComponent();
    return Response::uri($component->render());
});
```

## Solución más probable

El problema más común es que el servidor necesita reiniciarse después de agregar nuevos componentes. El auto-discovery se ejecuta una sola vez cuando el servidor inicia.

**Reinicia el servidor y el problema debería resolverse.**

## Si nada funciona

1. Verifica que todos los archivos estén en el servidor
2. Verifica permisos (755 para directorios, 644 para archivos)
3. Verifica que no haya errores de sintaxis en FormsShowcaseComponent.php
4. Revisa los logs de PHP para ver el error exacto
5. Ejecuta `test-autodiscovery.php` para diagnóstico detallado
