# Implementación Correcta de CRUDs - Basado en ExampleCrud V3

He identificado que la implementación anterior NO seguía la estructura correcta de ExampleCrud.

## Estructura Correcta (ExampleCrud V3):

```
ExampleCrud/
├── ExampleCrudComponent.php          ← Tabla principal
├── example-crud.css                   ← Estilos tabla
├── example-crud.js                    ← Lógica tabla + navegación
└── childs/
    ├── ExampleCreate/
    │   ├── ExampleCreateComponent.php ← Usa InputTextComponent, SelectComponent, etc.
    │   ├── example-create.js         ← Validación + fetch + closeModule
    │   └── example-form.css          ← Estilos compartidos
    └── ExampleEdit/
        ├── ExampleEditComponent.php  ← Igual estructura que Create
        ├── example-edit.js           ← Carga datos + actualiza
        └── example-form.css          ← Compartido con Create
```

## Componentes que debo usar (NO custom HTML):

- **InputTextComponent** - Para nombre, precio, etc.
- **SelectComponent** - Para categorías
- **TextAreaComponent** - Para descripción
- **FilePondComponent** - Para imágenes (soporta múltiple)

## JavaScript correcto:

- Usar `window.legoWindowManager.openModuleWithMenu()` para navegación
- Usar `window.LegoSelect.getValue()` y `setValue()` para selects
- Usar `window.FilePondComponent.getImageIds()` para imágenes
- Usar `window.legoWindowManager.closeCurrentWindow()` para cerrar
- Usar `TableManager` con callbacks globales `window.handleEditRecord`, etc.

## Voy a REHACER TODO correctamente ahora.

