# LEGO Framework - Documentación

Framework PHP/JS modular. Componentes ensamblables como bloques LEGO.

**[← Volver al proyecto](../README.md)**

## Índice

### Conceptos

| Archivo | Descripción |
|---------|-------------|
| [01-arquitectura.md](01-arquitectura.md) | Estructura del proyecto, flujo de ejecución |
| [02-componentes.md](02-componentes.md) | CoreComponent, CSS/JS, composición |
| [03-screens.md](03-screens.md) | ScreenInterface, ScreenTrait, identidad de ventanas |
| [04-menu.md](04-menu.md) | MenuStructure, items dinámicos, grupos |
| [05-modulos.md](05-modulos.md) | WindowManager, ModuleStore, navegación |
| [06-api.md](06-api.md) | Rutas, controladores, atributos |
| [07-modelos.md](07-modelos.md) | Eloquent, ApiGetResource, ApiCrudResource |
| [08-servicios-js.md](08-servicios-js.md) | AlertService, ConfirmationService, ThemeManager |
| [09-tablas.md](09-tablas.md) | TableComponent, server-side, filtros, acciones |
| [10-formularios.md](10-formularios.md) | InputText, Select, TextArea, FilePond |

### Flujos (Cómo hacer X)

| Flujo | Qué hace |
|-------|----------|
| [crear-componente.md](flows/crear-componente.md) | Crear un componente básico |
| [crear-screen.md](flows/crear-screen.md) | Crear una pantalla/ventana con identidad |
| [crear-crud.md](flows/crear-crud.md) | CRUD completo con tabla + formularios |
| [crear-boton.md](flows/crear-boton.md) | Agregar un botón con acción |
| [crear-migracion.md](flows/crear-migracion.md) | Crear migración de base de datos |
| [crear-comando.md](flows/crear-comando.md) | Crear comando CLI |
| [agregar-menu-item.md](flows/agregar-menu-item.md) | Agregar item al menú lateral |
| [agregar-api-endpoint.md](flows/agregar-api-endpoint.md) | Crear endpoint API |

## Estructura del Proyecto

```
Lego/
├── App/                    # Lógica de aplicación
│   ├── Controllers/        # Controladores API
│   └── Models/             # Modelos Eloquent
├── Core/                   # Framework core
│   ├── Components/         # CoreComponent base
│   ├── Contracts/          # Interfaces (ScreenInterface)
│   ├── Traits/             # Traits (ScreenTrait)
│   ├── Registry/           # ScreenRegistry
│   └── Config/             # MenuStructure
├── components/             # Componentes UI
│   ├── App/                # Componentes de aplicación
│   ├── Core/               # Componentes del framework
│   └── Shared/             # Componentes reutilizables
├── assets/                 # CSS/JS globales
├── database/               # Migraciones
└── Routes/                 # Definición de rutas
```

## Convenciones

- Componentes: `PascalCase` + sufijo `Component` → `ExampleCrudComponent`
- Screens: Constantes `SCREEN_*` en mayúsculas
- CSS: Variables `--nombre-variable`
- JS: `SCREEN_CONFIG` para configuración
- IDs: `kebab-case` → `example-crud-list`

