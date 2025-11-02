# 🎯 TODO: Table Component Refinement - Model-Driven CRUD

## 📋 Objetivo General

Transformar TableComponent en un sistema de alto nivel donde:
- Los modelos se decoran con `#[ApiCrudResource]`
- Las rutas API se generan automáticamente
- El TableComponent detecta todo mágicamente
- Callbacks scoped al componente (no globals)

---

## 🚀 FASE 1: Model-Driven API (ApiCrudResource)

**Objetivo:** Crear sistema de API REST automático basado en decoradores.

### ✅ Tareas

- [ ] 1.1 Crear `Core/Attributes/ApiCrudResource.php`
  - Atributo PHP 8 para marcar modelos
  - Configuración: paginación (cursor/offset/page), filtros, ordenamiento
  - Tiempo: 30 min

- [ ] 1.2 Crear `Core/Controllers/AbstractCrudController.php`
  - Controlador genérico que funciona con cualquier modelo
  - Métodos: list(), get(), create(), update(), delete()
  - Validación automática
  - Tiempo: 2 horas

- [ ] 1.3 Crear `Core/Pagination/CursorPaginator.php`
  - Paginación cursor-based
  - Compatible con Eloquent
  - Tiempo: 1 hora

- [ ] 1.4 Crear `Core/Pagination/OffsetPaginator.php`
  - Paginación offset-based (page/limit)
  - Compatible con Eloquent
  - Tiempo: 45 min

- [ ] 1.5 Crear `Core/Routing/ApiCrudRouter.php`
  - Escanea modelos con `#[ApiCrudResource]`
  - Registra rutas automáticamente
  - Se ejecuta en bootstrap
  - Tiempo: 1.5 horas

- [ ] 1.6 Integrar en `Core/bootstrap.php`
  - Llamar `ApiCrudRouter::registerRoutes()` después de Eloquent
  - Tiempo: 15 min

- [ ] 1.7 Aplicar a `App/Models/Product.php`
  - Agregar `#[ApiCrudResource]`
  - Probar endpoints automáticos
  - Tiempo: 30 min

- [ ] 1.8 Actualizar `Routes/Api.php`
  - Remover rutas manuales de products
  - Documentar sistema automático
  - Tiempo: 15 min

**Tiempo total estimado:** 6.5 horas

**Resultado esperado:**
```php
// App/Models/Product.php
#[ApiCrudResource(pagination: 'offset')]
class Product extends Model {}

// Automáticamente expone:
// GET    /api/products
// GET    /api/products/{id}
// POST   /api/products
// PUT    /api/products/{id}
// DELETE /api/products/{id}
```

---

## 🎨 FASE 2: TableComponent Alto Nivel

**Objetivo:** Simplificar uso de TableComponent con detección automática.

### ✅ Tareas

- [ ] 2.1 Crear `Core/Components/Table/TableConfig.php`
  - DTO para configuración simplificada
  - Detección automática de API desde modelo
  - Tiempo: 1 hora

- [ ] 2.2 Refactorizar `TableComponent` para soportar `model` param
  - Detectar `#[ApiCrudResource]` del modelo
  - Generar endpoint automáticamente
  - Configurar paginación según modelo
  - Tiempo: 2 horas

- [ ] 2.3 Implementar sistema de callbacks scoped
  - Callbacks buscan funciones en JS del componente
  - NO en window global
  - Tiempo: 1.5 horas

- [ ] 2.4 Integrar ActionButtons automáticamente
  - Si `actions` está definido, agregar columna
  - Renderizar con ActionButtons::static() internamente
  - Tiempo: 1 hora

- [ ] 2.5 Actualizar `table.js` para client-side pagination
  - Fetch a API automático
  - Manejo de paginación cursor/offset
  - Tiempo: 2 horas

- [ ] 2.6 Refactorizar `ProductsCrudV3Component`
  - Usar nueva API simplificada
  - Validar que funciona correctamente
  - Tiempo: 45 min

**Tiempo total estimado:** 8.25 horas

**Resultado esperado:**
```php
// ProductsCrudV3Component.php
$table = new TableComponent(
    id: "products-table",
    model: Product::class,  // ✨ Magia!
    columns: ['id', 'name', 'price', 'stock'],
    actions: ['edit', 'delete'],
    callbacks: [
        'onEdit' => 'handleEdit',    // ← Busca en products-crud-v3.js
        'onDelete' => 'handleDelete'
    ]
);
```

---

## ⚡ FASE 3: Refinamiento y Features

**Objetivo:** Pulir sistema y agregar features avanzadas.

### ✅ Tareas

- [ ] 3.1 Sistema de eventos `lego:table:{id}:{action}`
  - Disparar eventos para acciones
  - Documentar API de eventos
  - Tiempo: 1 hora

- [ ] 3.2 Validación y mensajes de error claros
  - Validar que modelo tenga `#[ApiCrudResource]`
  - Mensajes útiles si falta config
  - Tiempo: 45 min

- [ ] 3.3 Soporte para filtros avanzados
  - Filtros desde TableComponent
  - Query params automáticos
  - Tiempo: 2 horas

- [ ] 3.4 Soporte para búsqueda global
  - Search box integrado
  - Backend soporta `?search=query`
  - Tiempo: 1.5 horas

- [ ] 3.5 Exportación de datos (CSV, Excel)
  - Botón de exportar
  - Endpoint `/api/products/export`
  - Tiempo: 2 horas

- [ ] 3.6 Documentación completa
  - Crear `docs/TABLE_COMPONENT_GUIDE.md`
  - Ejemplos de uso
  - API reference completa
  - Tiempo: 2 horas

**Tiempo total estimado:** 9.25 horas

---

## 📊 Resumen de Tiempo

| Fase | Tiempo Estimado | Estado |
|------|-----------------|--------|
| FASE 1: Model-Driven API | 6.5 horas | ⏳ Pendiente |
| FASE 2: TableComponent Alto Nivel | 8.25 horas | ⏳ Pendiente |
| FASE 3: Refinamiento | 9.25 horas | ⏳ Pendiente |
| **TOTAL** | **24 horas** | |

---

## 🎯 Estado Actual

**Última actualización:** 2025-01-11

**Fase actual:** FASE 1 - Model-Driven API

**Próxima tarea:** 1.1 Crear `Core/Attributes/ApiCrudResource.php`

---

## 📝 Notas de Implementación

### Decisiones de Diseño

1. **Paginación por defecto:** Offset (page/limit) por ser más intuitivo
2. **Callbacks scoped:** Usar módulos ES6 del componente, no window globals
3. **Acciones por defecto:** edit, delete, view
4. **Validación:** Strict mode - error si falta configuración requerida

### Compatibilidad

- ✅ PHP 8.0+ (atributos)
- ✅ PostgreSQL (Eloquent)
- ✅ AG-Grid Community
- ✅ ES6 Modules

### Breaking Changes

- ⚠️ `ProductsCrudV3Component` necesitará actualización en FASE 2
- ⚠️ Rutas manuales en `Routes/Api.php` se reemplazarán por automáticas

---

## 🐛 Issues Conocidos

Ninguno por ahora.

---

## 🚀 Cómo Continuar

1. Revisar y aprobar este plan
2. Comenzar con FASE 1, tarea 1.1
3. Ir marcando tareas completadas con `[x]`
4. Actualizar "Estado Actual" después de cada sesión

---

**Última edición:** 2025-01-11 por Claude
