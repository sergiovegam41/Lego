# 🧪 Instrucciones de Prueba - Sistema de Componentes Dinámicos

## ✅ Cambios Realizados

### 1. **Fix AG-Grid** ✅
- Cambiado de `ActionButtons::dynamic()` a `ActionButtons::static()` en [ProductsCrudV3Component.php](../components/App/ProductsCrudV3/ProductsCrudV3Component.php:99)
- **Razón:** AG-Grid no soporta cellRenderers async (recibe Promise en lugar de HTMLElement)

### 2. **Registro Automático** ✅
- Creado [RegisterDynamicComponents.php](../Core/Bootstrap/RegisterDynamicComponents.php)
- Agregado al bootstrap en [Core/bootstrap.php](../Core/bootstrap.php:37)
- **Beneficio:** IconButtonComponent se registra automáticamente al iniciar la app

### 3. **Scripts de Prueba** ✅
- Creado [test-dynamic-components.js](./test-dynamic-components.js) - Pruebas automatizadas desde consola
- Creado [DYNAMIC_COMPONENTS_TESTING.md](./DYNAMIC_COMPONENTS_TESTING.md) - Plan de pruebas detallado

---

## 🎯 Pruebas a Realizar

### Prueba 1: Verificar que los botones se muestran en la tabla

**Pasos:**
1. Recargar la página de ProductsCrudV3
2. Verificar que en la columna "Acciones" aparecen 2 botones:
   - ✏️ Botón de editar (ícono de lápiz)
   - 🗑️ Botón de eliminar (ícono de basura)

**Resultado esperado:**
- ✅ Los botones se muestran correctamente
- ✅ No hay errores en consola
- ✅ Los botones tienen hover effects

**Si falla:**
- Revisar consola del navegador
- Ejecutar Prueba 2 (Backend)

---

### Prueba 2: Verificar Backend (Postman o Thunder Client)

#### Test 2.1: Listar Componentes

```
GET http://localhost/api/components/list
```

**Resultado esperado:**
```json
{
  "success": true,
  "components": ["icon-button"],
  "count": 1
}
```

**Si falla:**
- Verificar que `RegisterDynamicComponents::register()` se está ejecutando
- Revisar logs del servidor: `tail -f logs/error.log`

---

#### Test 2.2: Renderizado Único

```
GET http://localhost/api/components/render?id=icon-button&params={"icon":"create-outline","variant":"primary","title":"Test"}
```

**Resultado esperado:**
```json
{
  "success": true,
  "html": "<button class=\"lego-icon-button lego-icon-button--medium lego-icon-button--primary\"...>...</button>",
  "componentId": "icon-button"
}
```

**Si falla:**
- Problema en `ComponentRegistry::render()` o `IconButtonComponent::renderWithParams()`
- Revisar [ComponentsController.php](../App/Controllers/ComponentsController.php:45)

---

#### Test 2.3: Renderizado Batch

```
POST http://localhost/api/components/batch
Content-Type: application/json

{
  "component": "icon-button",
  "renders": [
    { "icon": "create-outline", "variant": "primary", "title": "Editar" },
    { "icon": "trash-outline", "variant": "danger", "title": "Eliminar" }
  ]
}
```

**Resultado esperado:**
```json
{
  "success": true,
  "html": [
    "<button class=\"lego-icon-button...\">...</button>",
    "<button class=\"lego-icon-button...\">...</button>"
  ],
  "count": 2
}
```

**Si falla:**
- Problema en `ComponentRegistry::renderBatch()`
- Revisar [ComponentRegistry.php](../Core/Components/ComponentRegistry.php:161)

---

### Prueba 3: Script Automatizado de JavaScript

**Pasos:**
1. Abrir consola del navegador (F12)
2. Copiar y pegar el contenido de [test-dynamic-components.js](./test-dynamic-components.js)
3. Ejecutar:
   ```javascript
   await testDynamicComponents()
   ```

**Resultado esperado:**
```
===========================================
📊 RESUMEN DE PRUEBAS
===========================================

✅ Pruebas exitosas: 12
❌ Pruebas fallidas: 0
📝 Total: 12

📈 Tasa de éxito: 100.0%
```

**Si falla:**
- Revisar detalles en consola
- Cada test muestra información específica del error

---

## 🐛 Problemas Conocidos y Soluciones

### Problema 1: "Component not found: 'icon-button'"

**Causa:** IconButtonComponent no se registró

**Solución:**
1. Verificar que `Core/bootstrap.php` tiene:
   ```php
   \Core\Bootstrap\RegisterDynamicComponents::register();
   ```
2. Verificar logs:
   ```bash
   tail -f logs/error.log | grep "ComponentRegistry"
   ```
3. Debería aparecer:
   ```
   [ComponentRegistry] ✓ Registered: icon-button → Components\Shared\Buttons\IconButtonComponent\IconButtonComponent
   ```

---

### Problema 2: Botones no se muestran en la tabla

**Causa Posible 1:** Error de sintaxis en `ActionButtons::static()`

**Verificación:**
```bash
php -l Core/Helpers/ActionButtons.php
```

**Causa Posible 2:** CSS no cargado

**Verificación:**
1. Inspeccionar un botón en DevTools
2. Verificar que tiene las clases:
   - `lego-icon-button`
   - `lego-icon-button--medium`
   - `lego-icon-button--ghost` o `lego-icon-button--danger`

---

### Problema 3: TypeError en AG-Grid (appendChild)

**Causa:** Usando `ActionButtons::dynamic()` en lugar de `ActionButtons::static()`

**Solución:**
Verificar que [ProductsCrudV3Component.php:99](../components/App/ProductsCrudV3/ProductsCrudV3Component.php:99) tiene:
```php
cellRenderer: ActionButtons::static(['edit', 'delete'])
```

Y NO:
```php
cellRenderer: ActionButtons::dynamic(['edit', 'delete'])  // ❌ No funciona con AG-Grid
```

---

## 📊 Checklist de Verificación

- [ ] **Prueba 1:** Botones visibles en tabla ProductsCrudV3
- [ ] **Prueba 2.1:** GET /api/components/list retorna icon-button
- [ ] **Prueba 2.2:** GET /api/components/render funciona
- [ ] **Prueba 2.3:** POST /api/components/batch funciona
- [ ] **Prueba 3:** Script JavaScript pasa todos los tests
- [ ] **Visual:** Botones tienen hover effects correctos
- [ ] **Visual:** Colores correcto (ghost: gris, danger: rojo)
- [ ] **Funcional:** Click en editar/eliminar (aunque las funciones no existan aún)

---

## 🎉 Si Todo Funciona

Deberías ver:
1. ✅ Tabla de productos con botones en columna "Acciones"
2. ✅ Sin errores en consola
3. ✅ Hover effects en botones
4. ✅ Todos los endpoints responden correctamente
5. ✅ Script de tests: 100% éxito

---

## 📝 Próximos Pasos (Futuro)

### Opción A: Componentes Dinámicos REALES
Para usar `ActionButtons::dynamic()` necesitamos crear un wrapper síncrono:

```php
// Futuro: ActionButtons::dynamicWithPlaceholder()
cellRenderer: ActionButtons::dynamicWithPlaceholder(['edit', 'delete'])
```

Este método retornaría un placeholder síncrono y haría el batch rendering en background.

### Opción B: Pre-renderizado en PHP
Renderizar los botones directamente en el backend antes de crear la tabla:

```php
$products = Product::get()->map(function($product) {
    $product->actions = (new IconButtonComponent(...))->render() .
                        (new IconButtonComponent(...))->render();
    return $product;
})->toArray();
```

---

## 🆘 Contacto y Soporte

Si encuentras errores:
1. Revisar logs: `logs/error.log`
2. Consola del navegador (F12)
3. Ejecutar script de tests automatizado
4. Documentación completa: [DYNAMIC_COMPONENTS.md](./DYNAMIC_COMPONENTS.md)
