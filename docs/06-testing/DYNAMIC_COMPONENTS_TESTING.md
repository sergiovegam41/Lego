# Plan de Pruebas - Sistema de Componentes Dinámicos

## 🔍 Diagnóstico del Error

**Error detectado:**
```
TypeError: Failed to execute 'appendChild' on 'Node': parameter 1 is not of type 'Node'.
```

**Causa probable:** AG-Grid espera un elemento DOM síncrono, pero `ActionButtons::dynamic()` retorna una función `async` que devuelve una Promise.

---

## 📋 Plan de Pruebas Sistemático

### Nivel 1: Backend (PHP) - Validar ComponentRegistry

#### Test 1.1: Verificar que IconButtonComponent se registra

**Postman Request:**
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

**Si falla:** IconButtonComponent no se está instanciando. Necesitamos forzar su registro.

---

#### Test 1.2: Renderizado único

**Postman Request:**
```
GET http://localhost/api/components/render?id=icon-button&params={"icon":"create-outline","variant":"primary","title":"Test"}
```

**Resultado esperado:**
```json
{
  "success": true,
  "html": "<button class=\"lego-icon-button...\">...</button>",
  "componentId": "icon-button"
}
```

**Si falla:** Problema en ComponentRegistry::render() o IconButtonComponent::renderWithParams()

---

#### Test 1.3: Renderizado batch

**Postman Request:**
```
POST http://localhost/api/components/batch
Content-Type: application/json

{
  "component": "icon-button",
  "renders": [
    {
      "icon": "create-outline",
      "variant": "primary",
      "title": "Editar"
    },
    {
      "icon": "trash-outline",
      "variant": "danger",
      "title": "Eliminar"
    }
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

**Si falla:** Problema en ComponentRegistry::renderBatch()

---

### Nivel 2: Frontend (JavaScript) - Validar DynamicComponentsManager

#### Test 2.1: Verificar que window.lego.components existe

**Consola del navegador:**
```javascript
console.log('window.lego.components:', window.lego.components);
console.log('Tipo:', typeof window.lego.components);
```

**Resultado esperado:**
```
window.lego.components: DynamicComponentsManager {...}
Tipo: object
```

**Si falla:** DynamicComponentsManager no se importó correctamente en base-lego-framework.js

---

#### Test 2.2: Listar componentes desde JavaScript

**Consola del navegador:**
```javascript
window.lego.components.listComponents()
  .then(components => console.log('Componentes registrados:', components))
  .catch(err => console.error('Error:', err));
```

**Resultado esperado:**
```
Componentes registrados: ['icon-button']
```

**Si falla:** Problema de conexión o routing.

---

#### Test 2.3: Renderizado batch desde JavaScript

**Consola del navegador:**
```javascript
window.lego.components
  .get('icon-button')
  .params([
    { icon: 'create-outline', variant: 'primary', title: 'Test 1' },
    { icon: 'trash-outline', variant: 'danger', title: 'Test 2' }
  ])
  .then(buttons => {
    console.log('Botones recibidos:', buttons);
    console.log('Cantidad:', buttons.length);
    console.log('Tipo del primer botón:', typeof buttons[0]);
  })
  .catch(err => console.error('Error:', err));
```

**Resultado esperado:**
```
Botones recibidos: ['<button class="lego-icon-button...">...</button>', '<button...>...</button>']
Cantidad: 2
Tipo del primer botón: string
```

**Si falla:** Problema en DynamicComponentsManager o en el endpoint batch.

---

### Nivel 3: Integración con AG-Grid - Problema CRÍTICO

#### Test 3.1: El Problema Real

**AG-Grid espera:** cellRenderer síncrono que retorna `HTMLElement | string`

**Nosotros retornamos:** `async function` que retorna `Promise<HTMLElement>`

**Solución:** AG-Grid NO soporta cellRenderers async directamente.

---

## 🔧 Soluciones Propuestas

### Solución A: Usar cellRenderer estático (RECOMENDADO para ahora)

**Modificar ProductsCrudV3Component.php:**

```php
cellRenderer: ActionButtons::static(['edit', 'delete'])
```

Esto genera botones inline sin requests asíncronos.

**Ventaja:** Funciona inmediatamente
**Desventaja:** No usa componentes dinámicos (pero el HTML sigue definido en PHP)

---

### Solución B: Wrapper síncrono con placeholder

**Crear nuevo método en ActionButtons:**

```php
public static function dynamicWithPlaceholder(array $actions, array $config = []): string
{
    // Similar a dynamic() pero:
    // 1. Retorna contenedor síncrono
    // 2. Renderiza async en background
    // 3. Reemplaza placeholder cuando llega la respuesta
}
```

**Ventaja:** Usa componentes dinámicos
**Desventaja:** Más complejo, posible flash de contenido

---

### Solución C: Pre-renderizar en PHP

**Modificar ProductsCrudV3Component:**

```php
// En lugar de cellRenderer async, pre-renderizar los botones en PHP
$products = Product::orderBy('created_at', 'desc')->get()->map(function($product) {
    $editBtn = new IconButtonComponent(
        icon: 'create-outline',
        variant: 'ghost',
        onClick: "editProduct({$product->id})",
        title: 'Editar'
    );

    $deleteBtn = new IconButtonComponent(
        icon: 'trash-outline',
        variant: 'danger',
        onClick: "deleteProduct({$product->id})",
        title: 'Eliminar'
    );

    $product->actions = $editBtn->render() . $deleteBtn->render();
    return $product;
})->toArray();
```

**Ventaja:** Sin requests async, componentes PHP reutilizables
**Desventaja:** No es batch rendering desde JS

---

## 🎯 Plan de Acción Inmediato

### Paso 1: Validar Backend (Postman)
Ejecutar Tests 1.1, 1.2, 1.3 para confirmar que el backend funciona.

### Paso 2: Validar Frontend (Consola)
Ejecutar Tests 2.1, 2.2, 2.3 para confirmar que JavaScript funciona.

### Paso 3: Fix AG-Grid
Implementar **Solución A** (static) o **Solución C** (pre-render PHP).

---

## 📝 Checklist

- [ ] Test 1.1: GET /api/components/list
- [ ] Test 1.2: GET /api/components/render
- [ ] Test 1.3: POST /api/components/batch
- [ ] Test 2.1: Verificar window.lego.components
- [ ] Test 2.2: listComponents() desde consola
- [ ] Test 2.3: Batch rendering desde consola
- [ ] Decidir solución (A, B o C)
- [ ] Implementar solución elegida
- [ ] Verificar botones en tabla

---

## 🐛 Console Logs para Debugging

### En ActionButtons.php (agregar logs)

```php
public static function dynamic(array $actions, array $config = []): string
{
    error_log('[ActionButtons] Generando cellRenderer dinámico para: ' . implode(', ', $actions));

    // ... código existente ...

    error_log('[ActionButtons] Params JSON: ' . $paramsJson);

    return $js;
}
```

### En DynamicComponentsManager.js (agregar logs)

```javascript
async renderBatch(componentId, paramsList) {
    console.log('[DynamicComponents] Batch request:', {
        componentId,
        count: paramsList.length,
        params: paramsList
    });

    try {
        const response = await fetch(...);
        const data = await response.json();

        console.log('[DynamicComponents] Batch response:', {
            success: data.success,
            count: data.html.length,
            htmlSample: data.html[0]?.substring(0, 100)
        });

        return data.html;
    } catch (error) {
        console.error('[DynamicComponents] Batch error:', error);
        throw error;
    }
}
```

---

## 🚨 Error Actual: AG-Grid + Async

**El error real es:**

AG-Grid llama a `cellRenderer(params)` y espera:
- Un string HTML: `"<button>...</button>"`
- Un HTMLElement: `document.createElement('button')`

Pero recibe:
- Una Promise: `Promise { <pending> }`

**AG-Grid NO PUEDE hacer `appendChild(promise)`**

Por eso el error:
```
Failed to execute 'appendChild' on 'Node': parameter 1 is not of type 'Node'
```

**Fix:** Usar cellRenderer síncrono o wrapper especial.
