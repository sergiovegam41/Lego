# 📂 Estructura de Carpetas para Componentes LEGO

## 🎯 Filosofía

La estructura de carpetas debe reflejar la **jerarquía y relaciones** entre componentes. Cuando un componente tiene sub-componentes derivados (hijos), estos deben estar organizados de forma clara y escalable.

---

## 📐 Reglas de Estructura

### Regla 1: Componente Principal - Archivos en Raíz

**El componente principal contiene SOLO sus propios recursos:**

```
ComponentName/
├── ComponentNameComponent.php    ← Componente principal
├── component-name.css            ← Estilos del componente principal
├── component-name.js             ← Lógica del componente principal
└── README.md                     ← Documentación (opcional)
```

**✅ Correcto**: Archivos relacionados directamente con el componente principal
**❌ Incorrecto**: Mezclar archivos de componentes hijos en la raíz

---

### Regla 2: Sub-componentes en Carpeta `childs/`

**Cuando existen componentes derivados o hijos:**

```
ComponentName/
├── ComponentNameComponent.php    ← Componente principal
├── component-name.css
├── component-name.js
├── README.md
└── childs/                       ← Carpeta para componentes hijos
    ├── ChildOne/
    │   ├── ChildOneComponent.php
    │   ├── child-one.css
    │   └── child-one.js
    └── ChildTwo/
        ├── ChildTwoComponent.php
        ├── child-two.css
        └── child-two.js
```

**Beneficios:**
- ✅ Clara separación de responsabilidades
- ✅ Fácil localizar componentes relacionados
- ✅ Escalable (agregar más hijos sin desorden)
- ✅ Refleja la jerarquía conceptual

---

## 📋 Ejemplo Real: ProductsCrudV3

### Antes (❌ Estructura Plana)

```
ProductsCrudV3/
├── ProductsCrudV3Component.php
├── ProductCreateComponent.php      ← Mezclado con principal
├── ProductEditComponent.php        ← Mezclado con principal
├── products-crud-v3.css
├── products-crud-v3.js
├── product-create.js               ← No se distingue fácilmente
├── product-edit.js                 ← No se distingue fácilmente
├── product-form.css                ← ¿De quién es este archivo?
├── product-create-old.js           ← Archivos obsoletos mezclados
└── product-create-old2.js          ← Difícil de mantener
```

**Problemas:**
- ❌ Difícil identificar qué archivos pertenecen a qué componente
- ❌ No escala bien (más componentes = más desorden)
- ❌ Archivos obsoletos mezclados
- ❌ No refleja la relación padre-hijo

### Después (✅ Estructura Jerárquica)

```
ProductsCrudV3/
├── ProductsCrudV3Component.php     ← Componente principal (tabla)
├── products-crud-v3.css            ← Estilos de la tabla
├── products-crud-v3.js             ← Lógica de la tabla
├── README.md                       ← Documentación
└── childs/                         ← Componentes derivados
    ├── ProductCreate/
    │   ├── ProductCreateComponent.php
    │   ├── product-create.js
    │   └── product-form.css
    └── ProductEdit/
        ├── ProductEditComponent.php
        ├── product-edit.js
        └── product-form.css
```

**Beneficios:**
- ✅ Inmediatamente claro: ProductsCrudV3 es el padre
- ✅ Cada hijo tiene su propia carpeta autocontenida
- ✅ Fácil agregar más hijos (ej: ProductView, ProductDelete)
- ✅ Archivos obsoletos removidos
- ✅ Namespaces reflejan la estructura

---

## 🏗️ Namespaces y Rutas

### Namespaces Reflejan la Estructura

```php
// Componente principal
namespace Components\App\ProductsCrudV3;

class ProductsCrudV3Component extends CoreComponent
{
    // ...
}
```

```php
// Componente hijo - Create
namespace Components\App\ProductsCrudV3\Childs\ProductCreate;

class ProductCreateComponent extends CoreComponent
{
    // ...
}
```

```php
// Componente hijo - Edit
namespace Components\App\ProductsCrudV3\Childs\ProductEdit;

class ProductEditComponent extends CoreComponent
{
    // ...
}
```

### Rutas de API

Las rutas pueden seguir la jerarquía:

```php
// Principal
#[ApiComponent('/products-crud-v3', methods: ['GET'])]

// Hijos
#[ApiComponent('/products-crud-v3/create', methods: ['GET'])]
#[ApiComponent('/products-crud-v3/edit', methods: ['GET'])]
```

**Nota:** Las rutas son lógicas, no necesariamente reflejan la estructura de carpetas físicas, pero ayuda mantenerlas relacionadas.

---

## 📚 Patrones Comunes

### Patrón 1: CRUD Completo

```
EntityCrud/
├── EntityCrudComponent.php          ← Lista/Tabla
├── entity-crud.css
├── entity-crud.js
└── childs/
    ├── EntityCreate/
    │   ├── EntityCreateComponent.php
    │   ├── entity-create.js
    │   └── entity-form.css
    ├── EntityEdit/
    │   ├── EntityEditComponent.php
    │   ├── entity-edit.js
    │   └── entity-form.css
    ├── EntityView/
    │   ├── EntityViewComponent.php
    │   ├── entity-view.js
    │   └── entity-view.css
    └── EntityDelete/
        └── EntityDeleteComponent.php
```

### Patrón 2: Wizard Multi-paso

```
UserRegistration/
├── UserRegistrationComponent.php    ← Contenedor del wizard
├── user-registration.css
├── user-registration.js
└── childs/
    ├── Step1PersonalInfo/
    │   ├── Step1PersonalInfoComponent.php
    │   └── step1.css
    ├── Step2Address/
    │   ├── Step2AddressComponent.php
    │   └── step2.css
    └── Step3Confirmation/
        ├── Step3ConfirmationComponent.php
        └── step3.css
```

### Patrón 3: Dashboard con Widgets

```
Dashboard/
├── DashboardComponent.php           ← Layout principal
├── dashboard.css
├── dashboard.js
└── childs/
    ├── SalesWidget/
    │   ├── SalesWidgetComponent.php
    │   └── sales-widget.css
    ├── AnalyticsWidget/
    │   ├── AnalyticsWidgetComponent.php
    │   └── analytics-widget.css
    └── NotificationsWidget/
        ├── NotificationsWidgetComponent.php
        └── notifications-widget.css
```

---

## ✅ Checklist de Estructura

Al crear un nuevo componente con hijos:

```
[ ] ¿El componente principal está en la raíz?
[ ] ¿Los archivos del principal SOLO contienen lógica del principal?
[ ] ¿Existe la carpeta childs/ si hay sub-componentes?
[ ] ¿Cada hijo tiene su propia carpeta nombrada claramente?
[ ] ¿Los namespaces reflejan la estructura de carpetas?
[ ] ¿No hay archivos obsoletos mezclados?
[ ] ¿La estructura es fácil de entender para otros developers?
[ ] ¿Se puede escalar agregando más hijos sin problemas?
```

---

## 🚫 Anti-Patrones (Evitar)

### ❌ Anti-Patrón 1: Todo Plano

```
MyComponent/
├── MyComponentMain.php
├── MyComponentCreate.php
├── MyComponentEdit.php
├── MyComponentView.php
├── main.css
├── create.css
├── edit.css
├── view.css
├── main.js
├── create.js
├── edit.js
└── view.js
```

**Problema:** Imposible distinguir jerarquías, no escala.

### ❌ Anti-Patrón 2: Sobre-jerarquización

```
MyComponent/
├── MyComponentComponent.php
└── childs/
    └── SubComponents/
        └── Actions/
            └── Create/
                └── Forms/
                    └── MainForm/
                        └── CreateFormComponent.php
```

**Problema:** Excesivamente profundo, difícil de navegar.

### ❌ Anti-Patrón 3: Nombres Inconsistentes

```
MyComponent/
├── MyComponentComponent.php
└── childs/
    ├── createProduct/           ← camelCase
    ├── edit-product/            ← kebab-case
    └── ProductView/             ← PascalCase
```

**Problema:** Inconsistencia confunde y dificulta búsquedas.

---

## 📖 Guía Rápida

### ¿Cuándo usar `childs/`?

**✅ USA `childs/` cuando:**
- El componente principal tiene sub-componentes derivados
- Hay una relación padre-hijo clara
- Los hijos existen en contexto del padre
- Quieres mantener organización escalable

**❌ NO USES `childs/` cuando:**
- Son componentes completamente independientes
- No hay relación padre-hijo
- Los componentes se usan en múltiples contextos

### Ejemplo: ¿CRUD necesita childs/?

**SÍ**, porque:
- ProductsCrudV3 es el padre (tabla principal)
- ProductCreate y ProductEdit son hijos (existen en contexto del CRUD)
- Son específicos de este CRUD de productos
- Refleja la navegación: Tabla → Crear/Editar

### Ejemplo: ¿Button Component necesita childs/?

**NO**, porque:
- Button es genérico y reutilizable
- No tiene sub-componentes derivados
- Se usa en múltiples contextos
- Es un componente atómico

---

## 🎓 Mejores Prácticas

### 1. Nombre de Carpetas

```
✅ Correcto: PascalCase
childs/
├── ProductCreate/
└── ProductEdit/

❌ Incorrecto: Mezcla de estilos
childs/
├── product_create/
└── ProductEdit/
```

### 2. Consistencia de Nombres

```
✅ Correcto: Consistente
ProductCreate/
├── ProductCreateComponent.php
├── product-create.js
└── product-create.css

❌ Incorrecto: Inconsistente
ProductCreate/
├── CreateProductComponent.php
├── product_creation.js
└── form-styles.css
```

### 3. Un Componente, Una Carpeta

```
✅ Correcto:
childs/
├── ProductCreate/
│   ├── ProductCreateComponent.php
│   └── product-create.js
└── ProductEdit/
    ├── ProductEditComponent.php
    └── product-edit.js

❌ Incorrecto:
childs/
└── Forms/
    ├── ProductCreateComponent.php
    └── ProductEditComponent.php
```

### 4. Documentación

```
✅ Agregar README.md al componente principal
ProductsCrudV3/
├── README.md              ← Explica el componente y sus hijos
├── ProductsCrudV3Component.php
└── childs/
    └── ...
```

---

## 🔄 Migración de Estructura Existente

### Proceso en 5 Pasos

1. **Crear carpeta `childs/`**
   ```bash
   mkdir childs
   ```

2. **Crear carpetas individuales por hijo**
   ```bash
   mkdir childs/ChildOne childs/ChildTwo
   ```

3. **Mover archivos**
   ```bash
   mv ChildOneComponent.php childs/ChildOne/
   mv child-one.js childs/ChildOne/
   mv child-one.css childs/ChildOne/
   ```

4. **Actualizar namespaces**
   ```php
   // De:
   namespace Components\App\ParentComponent;

   // A:
   namespace Components\App\ParentComponent\Childs\ChildOne;
   ```

5. **Verificar rutas de archivos**
   ```php
   // Actualizar CSS_PATHS y JS_PATHS si es necesario
   protected $CSS_PATHS = ["./child-one.css"];
   ```

---

## 📊 Diagrama de Decisión

```
┌─────────────────────────────────────────┐
│ ¿Necesito crear un nuevo componente?   │
└────────────────┬────────────────────────┘
                 │
                 ▼
        ┌────────────────────┐
        │ ¿Es independiente? │
        └────────┬───────────┘
                 │
        ┌────────┴────────┐
        │                 │
       SÍ                NO
        │                 │
        ▼                 ▼
┌───────────────┐  ┌─────────────────────┐
│ Crear en      │  │ ¿Hay un componente  │
│ carpeta raíz  │  │ padre existente?    │
│ propia        │  └──────────┬──────────┘
└───────────────┘             │
                     ┌────────┴────────┐
                     │                 │
                    SÍ                NO
                     │                 │
                     ▼                 ▼
         ┌──────────────────┐  ┌──────────────┐
         │ Crear dentro     │  │ Crear padre  │
         │ padre/childs/    │  │ y luego hijo │
         │ NuevoHijo/       │  │ en childs/   │
         └──────────────────┘  └──────────────┘
```

---

## 🎉 Conclusión

Una estructura de carpetas bien organizada:

- ✅ **Refleja la arquitectura** del código
- ✅ **Facilita la navegación** y búsqueda
- ✅ **Escala sin problemas** al agregar componentes
- ✅ **Mejora la colaboración** del equipo
- ✅ **Reduce errores** al mantener código

**Regla de Oro:**
> "Si un componente tiene hijos, usa `childs/`. Si es independiente, carpeta propia."

---

**Última actualización:** 2025-11-02
**Versión:** 1.0.0
**Ejemplos:** ProductsCrudV3
