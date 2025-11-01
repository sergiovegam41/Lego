# Sistema de Eventos Lego - Resumen de Implementación

**Fecha:** 2025-01-XX
**Estado:** ✅ Sistema de eventos implementado | ⚠️ SelectComponent necesita refactorización

---

## ✅ Lo que se Implementó

### 1. Sistema de Eventos Centralizado

**Archivo:** [assets/js/core/modules/events/lego-events.js](assets/js/core/modules/events/lego-events.js)

- Clase `LegoEvents` con sistema pub/sub completo
- **SIN ES6 modules** - Usa `window.LegoEvents` y `window.legoEvents`
- Métodos: `on()`, `once()`, `emit()`, `off()`, `clear()`
- Helpers: `onComponentInit()`, `onComponentReady()`, `onTableReady()`
- Historial de eventos para debugging

### 2. Integración en el Framework

**Modificado:**
- [Core/Components/CoreComponent/CoreComponent.php](Core/Components/CoreComponent/CoreComponent.php) - Emite `component:init`
- [assets/js/core/base-lego-framework.js](assets/js/core/base-lego-framework.js) - Expone `window.lego.events`
- [components/Core/Home/Components/MainComponent/MainComponent.php](components/Core/Home/Components/MainComponent/MainComponent.php) - Carga lego-events.js

### 3. ProductsCrudV3 Actualizado

**Archivo:** [components/App/ProductsCrudV3/product-create.js](components/App/ProductsCrudV3/product-create.js)

- Usa `lego.events.onComponentInit()` en lugar de `DOMContentLoaded`
- Emite eventos personalizados (`product:created`)
- Incluye fallback si el sistema no está disponible

---

## 🎯 Cómo Usar el Sistema de Eventos

### Patrón Básico en Componentes

```javascript
// Tu componente JavaScript
console.log('[MiComponente] Script cargado');

function inicializar() {
    console.log('[MiComponente] Inicializando...');

    const form = document.getElementById('mi-form');
    if (!form) return;

    form.addEventListener('submit', handleSubmit);

    // Emitir evento de componente listo
    lego.events.emitComponentReady('MiComponente', 'mi-form');
}

// Suscribirse al evento de inicialización
if (window.lego && window.lego.events) {
    lego.events.onComponentInit((detail) => {
        if (detail.componentName === 'MiComponente') {
            setTimeout(inicializar, 50);
        }
    });
} else {
    // Fallback
    setTimeout(inicializar, 100);
}
```

### API Disponible

```javascript
// Eventos de componente
lego.events.onComponentInit(callback, componentName?)
lego.events.onComponentReady(callback, componentName?)
lego.events.emitComponentInit(componentName, componentId, metadata)
lego.events.emitComponentReady(componentName, componentId, metadata)

// Eventos genéricos
lego.events.on(eventName, callback, options?)
lego.events.once(eventName, callback, options?)
lego.events.emit(eventName, detail?)
lego.events.off(eventName, listenerId)

// Módulos
lego.events.onModuleOpened(callback)
lego.events.onModuleClosed(callback)

// Tablas
lego.events.onTableReady(callback, tableId?)

// Debugging
lego.events.getHistory(eventName?)
lego.events.clear(eventName?)
```

---

## ⚠️ Problema Actual: SelectComponent usa ES6 Modules

### Error en Consola

```
Error executing code: SyntaxError: Unexpected token 'export'
    at executeCode (loads-scripts.js:61:17)
```

### Causa Raíz

El sistema `loads-scripts.js` usa `eval()` o `new Function()` para ejecutar scripts dinámicamente, lo cual **NO soporta ES6 modules**.

Los siguientes archivos usan `export`:
- `components/Shared/Forms/SelectComponent/SelectModel.js`
- `components/Shared/Forms/SelectComponent/SelectView.js`
- `components/Shared/Forms/SelectComponent/SelectController.js`
- `components/Shared/Forms/SelectComponent/select.js`

### Solución Necesaria

Hay **2 opciones**:

#### Opción 1: Refactorizar SelectComponent (Recomendado) ⭐

Cambiar los 4 archivos de SelectComponent para que NO usen `import/export`, sino que expongan todo en `window`:

**Antes:**
```javascript
// SelectModel.js
export class SelectModel {
    // ...
}
```

**Después:**
```javascript
// SelectModel.js
window.SelectModel = class SelectModel {
    // ...
};
```

**Antes:**
```javascript
// select.js
import { SelectModel } from './SelectModel.js';
export { initializeSelects };
```

**Después:**
```javascript
// select.js
// SelectModel ya está en window.SelectModel
window.initializeSelects = function() {
    // ...
};
```

#### Opción 2: Cargar SelectComponent como Módulo ES6

Modificar `CoreComponent.php` para detectar si un componente necesita cargarse como módulo:

```php
<script type="module" src="{$path}?v={$r}"></script>
```

**Pero esto tiene problemas:**
- Los scripts module se ejecutan después (son async/defer por defecto)
- El evento `component:init` se dispararía antes de que el script esté listo
- Rompe la arquitectura actual de `loads-scripts.js`

---

## 🔧 Acción Recomendada

**Refactorizar SelectComponent** para usar el mismo patrón que product-create.js:

1. Eliminar todos los `import` y `export`
2. Exponer clases en `window`:
   - `window.SelectModel`
   - `window.SelectView`
   - `window.SelectController`
   - `window.LegoSelect` (ya existe)
3. Remover la línea `export default legoEvents` de lego-events.js (✅ YA HECHO)

---

## 📊 Estado Actual

| Componente | Estado | Usa ES6 Modules | Funciona |
|------------|--------|-----------------|----------|
| lego-events.js | ✅ Refactorizado | ❌ No | ✅ Sí |
| base-lego-framework.js | ✅ Actualizado | ✅ Sí (type="module") | ✅ Sí |
| product-create.js | ✅ Refactorizado | ❌ No | ✅ Sí |
| products-crud-v3.js | ✅ OK | ❌ No | ✅ Sí |
| **SelectComponent/** | ❌ Necesita refactorización | ✅ Sí | ❌ No |
| TableComponent | ✅ OK | ❌ No | ✅ Sí |

---

## 🎯 Próximos Pasos

### Paso 1: Refactorizar SelectComponent (CRÍTICO)

**Archivos a modificar:**
1. `components/Shared/Forms/SelectComponent/SelectModel.js`
2. `components/Shared/Forms/SelectComponent/SelectView.js`
3. `components/Shared/Forms/SelectComponent/SelectController.js`
4. `components/Shared/Forms/SelectComponent/select.js`

**Patrón a aplicar:**
```javascript
// Eliminar imports
// import { SelectModel } from './SelectModel.js';

// Cambiar exports por window
// export class SelectModel { ... }
window.SelectModel = class SelectModel {
    // código existente sin cambios
};
```

### Paso 2: Verificar que ProductsCrudV3 Funcione

Una vez refactorizado SelectComponent:

1. Refrescar navegador (Cmd+Shift+R)
2. Ir a "Products CRUD V3" → "Crear"
3. Verificar en consola:
   ```
   [LegoEvents] Sistema de eventos inicializado
   [ProductCreate] Evento de inicialización recibido
   [ProductCreate] Formulario inicializado correctamente
   ```
4. Probar crear producto
5. Verificar que SelectComponent funcione (dropdown de categoría)

### Paso 3: Documentar el Patrón

Crear guía para futuros componentes:
- ❌ NO usar ES6 modules (`import/export`)
- ✅ Usar `window.MiComponente` para exponer clases/funciones
- ✅ Usar `lego.events.onComponentInit()` para inicialización
- ✅ Siempre incluir fallback

---

## 📚 Documentación

- [LEGO_EVENTS_GUIDE.md](docs/LEGO_EVENTS_GUIDE.md) - Guía completa del sistema de eventos
- [PRODUCTSCRUDV3_FINAL.md](PRODUCTSCRUDV3_FINAL.md) - Documentación de ProductsCrudV3

---

## 🐛 Debugging

### Ver eventos en consola

```javascript
// Ver historial de eventos
lego.events.getHistory()

// Ver eventos de componentes
lego.events.getHistory('component:init')

// Ver listeners activos
console.log(lego.events.listeners)
```

### Verificar que el sistema esté disponible

```javascript
// En consola del navegador
console.log(window.lego.events)
console.log(window.legoEvents)
```

---

## ✅ Resumen

**Lo que funciona:**
- ✅ Sistema de eventos centralizado
- ✅ Evento `component:init` se emite correctamente
- ✅ product-create.js usa el sistema correctamente
- ✅ Documentación completa

**Lo que falta:**
- ⚠️ Refactorizar SelectComponent para eliminar ES6 modules
- ⚠️ Probar flujo completo de ProductsCrudV3

**Una vez refactorizado SelectComponent, ProductsCrudV3 estará 100% funcional.**
