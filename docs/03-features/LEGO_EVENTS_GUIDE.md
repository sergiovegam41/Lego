# Guía del Sistema de Eventos de Lego

**Fecha:** 2025-01-XX
**Filosofía:** "Las distancias importan" - Comunicación desacoplada entre componentes

---

## 🎯 Problema que Resuelve

En sistemas SPA/modulares como Lego, los componentes se cargan **dinámicamente** después de que el DOM inicial ya está listo. Esto causa que `DOMContentLoaded` **NO funcione** en estos componentes.

### ❌ Patrón que NO Funciona

```javascript
// ❌ NUNCA FUNCIONA en componentes cargados dinámicamente
document.addEventListener('DOMContentLoaded', function() {
    console.log('Este código NUNCA se ejecutará');
    // El DOM ya estaba listo cuando se cargó este script
});
```

### ✅ Solución: Sistema de Eventos de Lego

```javascript
// ✅ SIEMPRE FUNCIONA
lego.events.onComponentInit((detail) => {
    if (detail.componentName === 'MiComponente') {
        console.log('¡Componente inicializado!');
        // Tu código aquí
    }
});
```

---

## 📚 API Completa

### 1. Eventos de Componentes

#### `onComponentInit(callback, componentName?)`

Se dispara **inmediatamente después** de que se carga el script del componente.

```javascript
// Escuchar inicialización de cualquier componente
lego.events.onComponentInit((detail) => {
    console.log('Componente inicializado:', detail.componentName);
});

// Escuchar componente específico
lego.events.onComponentInit((detail) => {
    if (detail.componentName === 'ProductCreateComponent') {
        initializeForm();
    }
});
```

**Detalle del evento:**
```javascript
{
    componentName: 'ProductCreateComponent',
    componentId: '12345',
    scriptPath: '/components/App/ProductsCrudV3/product-create.js',
    timestamp: 1704067200000
}
```

#### `onComponentReady(callback, componentName?)`

Se dispara **después** de que el componente terminó de inicializarse.

```javascript
lego.events.onComponentReady((detail) => {
    console.log('Componente listo:', detail.componentName);
});
```

**Emitir desde tu componente:**
```javascript
// Al final de tu función de inicialización
lego.events.emitComponentReady('MiComponente', 'mi-form-id', {
    customData: 'valor'
});
```

---

### 2. Eventos Genéricos

#### `on(eventName, callback, options?)`

Suscribirse a cualquier evento.

```javascript
// Evento básico
lego.events.on('product:created', (detail) => {
    console.log('Producto creado:', detail.product);
});

// Con opciones
lego.events.on('product:updated', callback, {
    once: true,      // Solo ejecutar una vez
    priority: 10     // Mayor prioridad se ejecuta primero
});
```

#### `once(eventName, callback, options?)`

Suscribirse una sola vez.

```javascript
lego.events.once('user:login', (detail) => {
    console.log('Usuario logueado por primera vez:', detail.user);
});
```

#### `emit(eventName, detail?)`

Emitir un evento personalizado.

```javascript
lego.events.emit('product:created', {
    product: newProduct,
    timestamp: Date.now()
});
```

#### `off(eventName, listenerId)`

Desuscribirse de un evento.

```javascript
const unsubscribe = lego.events.on('product:deleted', callback);

// Más tarde...
unsubscribe();
```

---

### 3. Eventos de Módulos/Pestañas

#### `onModuleOpened(callback)`

```javascript
lego.events.onModuleOpened((detail) => {
    console.log('Módulo abierto:', detail.moduleId);
});
```

#### `onModuleClosed(callback)`

```javascript
lego.events.onModuleClosed((detail) => {
    console.log('Módulo cerrado:', detail.moduleId);
});
```

---

### 4. Eventos de Tablas

#### `onTableReady(callback, tableId?)`

```javascript
// Escuchar cualquier tabla
lego.events.onTableReady((detail) => {
    console.log('Tabla lista:', detail.tableId);
});

// Escuchar tabla específica
lego.events.onTableReady((detail) => {
    console.log('Mi tabla está lista');
}, 'products-table-v3');
```

---

## 🔧 Patrón Recomendado para Componentes

### Estructura Básica

```javascript
/**
 * MiComponente - Descripción
 */

console.log('[MiComponente] Script cargado');

// ═══════════════════════════════════════════════════════════════════
// FUNCIONES DEL COMPONENTE
// ═══════════════════════════════════════════════════════════════════

function inicializar() {
    console.log('[MiComponente] Inicializando...');

    const form = document.getElementById('mi-form');

    if (!form) {
        console.warn('[MiComponente] Formulario no encontrado');
        return;
    }

    // Agregar event listeners
    form.addEventListener('submit', handleSubmit);

    console.log('[MiComponente] Inicializado correctamente');

    // Emitir evento de componente listo
    lego.events.emitComponentReady('MiComponente', 'mi-form');
}

async function handleSubmit(e) {
    e.preventDefault();
    // Tu lógica aquí

    // Emitir evento personalizado
    lego.events.emit('formulario:enviado', { data: formData });
}

// ═══════════════════════════════════════════════════════════════════
// SUSCRIPCIÓN AL EVENTO DE INICIALIZACIÓN
// ═══════════════════════════════════════════════════════════════════

if (window.lego && window.lego.events) {
    lego.events.onComponentInit((detail) => {
        if (detail.componentName === 'MiComponente') {
            console.log('[MiComponente] Evento recibido:', detail);
            setTimeout(inicializar, 50);
        }
    });

    console.log('[MiComponente] Suscrito a lego.events');
} else {
    // Fallback
    console.warn('[MiComponente] Sistema de eventos no disponible');
    setTimeout(inicializar, 100);
}
```

---

## 💡 Casos de Uso

### Caso 1: Inicialización de Formulario

```javascript
lego.events.onComponentInit((detail) => {
    if (detail.componentName === 'ProductCreateComponent') {
        const form = document.getElementById('product-create-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            // Lógica del formulario
        });
    }
});
```

### Caso 2: Comunicación entre Componentes

```javascript
// Componente A: Emitir evento
lego.events.emit('product:created', {
    product: newProduct
});

// Componente B: Escuchar evento
lego.events.on('product:created', (detail) => {
    reloadTable();
    showNotification('Producto creado: ' + detail.product.name);
});
```

### Caso 3: Sincronización de Estado

```javascript
// En tu componente de tabla
lego.events.on('product:created', () => {
    tableManager.reload();
});

lego.events.on('product:updated', () => {
    tableManager.reload();
});

lego.events.on('product:deleted', () => {
    tableManager.reload();
});
```

### Caso 4: Analytics y Logging

```javascript
// Listener global para todos los eventos
lego.events.on('product:created', (detail) => {
    analytics.track('Product Created', {
        productId: detail.product.id,
        timestamp: Date.now()
    });
});
```

---

## 🐛 Debug y Herramientas

### Ver Historial de Eventos

```javascript
// Ver todos los eventos
console.log(lego.events.getHistory());

// Ver eventos específicos
console.log(lego.events.getHistory('component:init'));
```

### Ver Listeners Activos

```javascript
// En la consola del navegador
console.log(lego.events.listeners);
```

### Limpiar Listeners

```javascript
// Limpiar listeners de un evento específico
lego.events.clear('product:created');

// Limpiar todos los listeners
lego.events.clear();
```

---

## ⚠️ Buenas Prácticas

### ✅ DO

- **Usar nombres descriptivos:** `product:created`, `user:login`, `form:submitted`
- **Prefijos por módulo:** `product:*`, `user:*`, `table:*`
- **Incluir timestamp:** Siempre incluir `timestamp: Date.now()` en el detalle
- **Cleanup:** Desuscribirse cuando el componente se destruye
- **Fallback:** Siempre tener un fallback si `lego.events` no está disponible

```javascript
// ✅ CORRECTO
if (window.lego && window.lego.events) {
    lego.events.on('product:created', callback);
} else {
    // Fallback
    setTimeout(callback, 100);
}
```

### ❌ DON'T

- **No usar nombres genéricos:** `data`, `update`, `change`
- **No crear ciclos infinitos:**

```javascript
// ❌ MALO - Ciclo infinito
lego.events.on('product:created', () => {
    lego.events.emit('product:created'); // ¡Recursión infinita!
});
```

- **No bloquear el thread:**

```javascript
// ❌ MALO - Operación síncrona pesada
lego.events.on('data:loaded', (detail) => {
    // Procesamiento pesado síncrono
    for (let i = 0; i < 1000000; i++) { /* ... */ }
});

// ✅ CORRECTO - Operación asíncrona
lego.events.on('data:loaded', async (detail) => {
    await processDataAsync(detail);
});
```

---

## 📊 Comparación: Antes vs Después

### Antes (con DOMContentLoaded)

```javascript
// ❌ NO FUNCIONA en componentes dinámicos
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('my-form');
    // Este código NUNCA se ejecuta
});
```

### Después (con lego.events)

```javascript
// ✅ FUNCIONA en componentes dinámicos
lego.events.onComponentInit((detail) => {
    if (detail.componentName === 'MyComponent') {
        const form = document.getElementById('my-form');
        // Este código SÍ se ejecuta
    }
});
```

---

## 🔗 Integración con el Framework

El sistema de eventos está integrado automáticamente en:

1. **CoreComponent.php** - Emite `component:init` al cargar scripts
2. **base-lego-framework.js** - Inicializa `window.lego.events`
3. **TableComponent** - Puede emitir `table:ready`
4. **ModuleStore** - Puede emitir `module:opened` y `module:closed`

---

## 📝 Ejemplo Completo: ProductsCrudV3

Ver implementación completa en:
- [product-create.js](../components/App/ProductsCrudV3/product-create.js)
- [products-crud-v3.js](../components/App/ProductsCrudV3/products-crud-v3.js)

---

## 🎓 Conclusión

El sistema de eventos de Lego resuelve el problema fundamental de inicialización en componentes dinámicos, proporcionando:

- ✅ **Reemplazo confiable de DOMContentLoaded**
- ✅ **Comunicación desacoplada entre componentes**
- ✅ **API simple y consistente**
- ✅ **Debugging y observabilidad**
- ✅ **Extensible y escalable**

**"Las distancias importan"** - El sistema de eventos mantiene la distancia correcta entre componentes, permitiendo comunicación sin acoplamiento directo.
