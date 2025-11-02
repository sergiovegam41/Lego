# Plan de Refactoring de Estructura

**Fecha**: 2025-11-02
**Estado**: DOCUMENTADO (No ejecutado - Alto riesgo)
**Tiempo estimado**: 1-2 horas
**Riesgo**: ALTO

---

## Problema Identificado

Existen inconsistencias en la estructura de carpetas del proyecto:

### 1. Controller vs Controllers

**Core/Controller/** (singular - 3 archivos):
- `CoreController.php`
- `CoreViewController.php`
- `RestfulController.php`
- **Namespace**: `Core\Controller`

**Core/Controllers/** (plural - 2 archivos):
- `AbstractCrudController.php`
- `AbstractGetController.php`
- **Namespace**: `Core\Controllers`

**Impacto**: 7 archivos usan `Core\Controller\`

### 2. providers vs Providers

**Core/providers/** (minúscula - 4 archivos):
- `Middleware.php`
- `Request.php`
- `StringMethods.php`
- `TimeSet.php`
- **Namespace**: `Core\providers`

**App/.../Providers/** (PascalCase - múltiples):
- `App/Controllers/Auth/Providers/`
- `App/Controllers/Storage/Providers/`
- **Namespace**: Variado

**Impacto**: Inconsistencia de convención de nombres

---

## Propuesta de Refactoring

### Opción 1: Consolidar a Plural (Recomendado)

**Objetivo**: Unificar todo a nomenclatura plural

#### Paso 1: Consolidar Controllers
```bash
# Mover archivos de Controller/ a Controllers/
mv Core/Controller/CoreController.php Core/Controllers/
mv Core/Controller/CoreViewController.php Core/Controllers/
mv Core/Controller/RestfulController.php Core/Controllers/

# Eliminar carpeta vacía
rmdir Core/Controller/
```

#### Paso 2: Actualizar Namespaces en archivos movidos
```php
// En CoreController.php, CoreViewController.php, RestfulController.php
// Cambiar:
namespace Core\Controller;

// Por:
namespace Core\Controllers;
```

#### Paso 3: Actualizar imports en 7 archivos
Archivos que usan `Core\Controller\`:
1. `Routes/Api.php`
2. `App/Controllers/Files/Controllers/FilesController.php`
3. `App/Controllers/Storage/Controllers/StorageController.php`
4. `App/Controllers/ComponentsController.php`
5. `App/Controllers/Auth/Controllers/AuthGroupsController.php`
6. `Core/Commands/MapRoutesCommand.php`
7. `App/Controllers/Products/Controllers/ProductsController.php`

```php
// Cambiar en cada archivo:
use Core\Controller\CoreController;

// Por:
use Core\Controllers\CoreController;
```

#### Paso 4: Renombrar providers a Providers
```bash
# Renombrar carpeta
mv Core/providers Core/Providers
```

#### Paso 5: Actualizar namespace de Providers
En 4 archivos (Middleware, Request, StringMethods, TimeSet):
```php
// Cambiar:
namespace Core\providers;

// Por:
namespace Core\Providers;
```

#### Paso 6: Actualizar imports de Providers
Buscar y reemplazar en todo el proyecto:
```php
// Buscar:
use Core\providers\

// Reemplazar por:
use Core\Providers\
```

#### Paso 7: Regenerar autoload
```bash
composer dump-autoload -o
```

---

## Comandos Automatizados

### Script Completo de Refactoring

```bash
#!/bin/bash
LEGO_PATH="/Users/serioluisvegamartinez/Documents/GitHub/Lego"

echo "=== REFACTORING DE ESTRUCTURA ==="
echo ""

# Paso 1: Consolidar Controllers
echo "1. Moviendo archivos de Controller/ a Controllers/..."
mv "$LEGO_PATH/Core/Controller/CoreController.php" "$LEGO_PATH/Core/Controllers/"
mv "$LEGO_PATH/Core/Controller/CoreViewController.php" "$LEGO_PATH/Core/Controllers/"
mv "$LEGO_PATH/Core/Controller/RestfulController.php" "$LEGO_PATH/Core/Controllers/"
rmdir "$LEGO_PATH/Core/Controller/"

# Paso 2: Actualizar namespaces en archivos movidos
echo "2. Actualizando namespaces en Controllers..."
sed -i '' 's/namespace Core\\Controller;/namespace Core\\Controllers;/g' \
    "$LEGO_PATH/Core/Controllers/CoreController.php" \
    "$LEGO_PATH/Core/Controllers/CoreViewController.php" \
    "$LEGO_PATH/Core/Controllers/RestfulController.php"

# Paso 3: Actualizar imports en todo el proyecto
echo "3. Actualizando imports de Controller..."
find "$LEGO_PATH" -name "*.php" -not -path "*/vendor/*" -exec \
    sed -i '' 's/use Core\\Controller\\/use Core\\Controllers\\/g' {} \;

# Paso 4: Renombrar providers
echo "4. Renombrando providers a Providers..."
mv "$LEGO_PATH/Core/providers" "$LEGO_PATH/Core/Providers"

# Paso 5: Actualizar namespace de Providers
echo "5. Actualizando namespaces en Providers..."
find "$LEGO_PATH/Core/Providers" -name "*.php" -exec \
    sed -i '' 's/namespace Core\\providers;/namespace Core\\Providers;/g' {} \;

# Paso 6: Actualizar imports de Providers
echo "6. Actualizando imports de providers..."
find "$LEGO_PATH" -name "*.php" -not -path "*/vendor/*" -exec \
    sed -i '' 's/use Core\\providers\\/use Core\\Providers\\/g' {} \;

# Paso 7: Regenerar autoload
echo "7. Regenerando autoload de Composer..."
cd "$LEGO_PATH"
composer dump-autoload -o

echo ""
echo "=== REFACTORING COMPLETADO ==="
echo "Por favor, prueba la aplicación y verifica que todo funcione."
```

---

## Riesgos y Precauciones

### Riesgos Altos:
1. ❌ **Romper namespaces**: Si un archivo no se actualiza, rompe la app
2. ❌ **Imports dinámicos**: Si hay imports construidos dinámicamente
3. ❌ **Cache de autoload**: Composer debe regenerarse correctamente

### Precauciones Necesarias:

#### Antes de Ejecutar:
1. ✅ **Hacer backup completo** del proyecto
2. ✅ **Commit de Git** actual
3. ✅ **Cerrar todos los contenedores Docker**
4. ✅ **Verificar que no hay cambios sin commitear**

#### Durante la Ejecución:
1. ✅ **Ejecutar script paso a paso** (no todo de golpe)
2. ✅ **Verificar cada paso** antes de continuar
3. ✅ **Revisar output** de cada comando

#### Después de Ejecutar:
1. ✅ **Levantar contenedores**: `docker-compose up -d`
2. ✅ **Verificar logs**: Sin errores de autoload
3. ✅ **Probar rutas principales**: /component/inicio, /component/products-crud-v3
4. ✅ **Ejecutar tests** si existen
5. ✅ **Commit si todo funciona**: `git add . && git commit -m "refactor: consolidate structure"`

---

## Verificación Post-Refactoring

### Checklist:
- [ ] No hay carpeta `Core/Controller/`
- [ ] No hay carpeta `Core/providers/`
- [ ] Todos los archivos están en `Core/Controllers/`
- [ ] Todos los archivos están en `Core/Providers/`
- [ ] `composer dump-autoload` ejecutado sin errores
- [ ] Aplicación carga sin errores
- [ ] Rutas funcionan correctamente
- [ ] No hay warnings en logs

### Comandos de Verificación:
```bash
# Verificar que no quedan referencias antiguas
grep -r "Core\\Controller\\" /ruta/proyecto --include="*.php" | grep -v vendor

# Verificar que no quedan referencias antiguas de providers
grep -r "Core\\providers\\" /ruta/proyecto --include="*.php" | grep -v vendor

# Ambos deben devolver 0 resultados
```

---

## Alternativa de Bajo Riesgo

Si prefieres un enfoque más conservador:

### Plan B: Documentar y Mantener Status Quo

**Ventajas**:
- Sin riesgo de romper la aplicación
- La inconsistencia es cosmética, no funcional

**Desventajas**:
- Mantiene deuda técnica
- Confusión para nuevos desarrolladores

**Recomendación**: Solo ejecutar este refactoring cuando:
1. Tengas tiempo para pruebas extensivas
2. Tengas un backup completo
3. Puedas dedicar 2-3 horas sin interrupciones
4. Tengas acceso a revertir cambios rápidamente

---

## Estado Actual

✅ **DOCUMENTADO** - Plan listo para ejecutar
⏳ **PENDIENTE** - Requiere sesión dedicada
🔴 **ALTO RIESGO** - Pruebas extensivas necesarias

**Conclusión**: Este refactoring mejora la calidad del código pero NO es crítico para el funcionamiento. Se recomienda ejecutarlo en una sesión futura dedicada cuando haya tiempo para pruebas completas.
