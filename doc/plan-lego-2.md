# Plan Lego 2

Fecha: 2026-10-13. Rama base: `cleanup/minimal-core`.
Fuentes: la auditoría por dimensiones en [auditoria-2026-10-13/](auditoria-2026-10-13/), la investigación en [investigacion-webmcp-2026-10-13.md](investigacion-webmcp-2026-10-13.md) y las decisiones de la entrevista del 13 de octubre.

## Punto de partida

La auditoría confirmó 149 hallazgos con refutación independiente y 117 piezas de código muerto con búsqueda de uso. El diagnóstico de fondo cabe en seis frases:

1. **Hay seis formas de registrar una ruta** y una pantalla se declara en cuatro o cinco sitios. Viola P1 y P2.
2. **La seguridad no existe por defecto.** Ningún endpoint de `/api` comprueba sesión ni rol, el JWT se firma pero nunca se verifica y hay XSS almacenado en formularios. Viola P6.
3. **Crear un módulo cuesta 12 a 17 archivos**, tres ediciones del núcleo, 21 constantes y unas 30 cadenas que deben coincidir a mano entre PHP y JS. Viola P1 y P4.
4. **Más de la mitad del código no se usa.** De 40 componentes compartidos, 25 no tienen consumidor; unas 2.500 líneas de JS se cargan en cada página sin uso; 27 % del CSS son copias del mismo formulario. Viola P5.
5. **La documentación contradice al código.** De 25 afirmaciones verificadas, 19 son falsas, y seguir la guía de CRUD tumba toda la API. Viola P3 y P4.
6. **Casi nada tiene test** y el proyecto no arranca en Linux sin parches. Viola P6.

Lo que sobrevive como idea es claro: componente como clase PHP con parámetros tipados que devuelve HTML; las tres capas `/api`, `/component` y `/`; el atributo en la clase como única declaración, descubierto por escaneo de carpetas; el modelo decorado que produce API y tabla; y el `ModuleStore` que mantiene módulos abiertos en el DOM.

## Objetivo medible de Lego 2

| Medida | Hoy | Meta Lego 2 |
|---|---|---|
| Archivos para un CRUD completo | 17 | 2: modelo y migración |
| Ediciones del núcleo para un módulo nuevo | 3 | 0 |
| Formas de registrar una ruta | 6 | 1 |
| Endpoints sin sesión por defecto | todos | ninguno salvo los marcados públicos |
| Líneas PHP del núcleo | ~9.600 | ≤ 3.500 |
| Líneas JS del núcleo | ~20.000 | ≤ 2.000 |
| Líneas CSS | ~10.300 | ≤ 1.800 |
| Servicios en docker-compose | 7 | 3: app, nginx, postgres |
| Dependencias de composer en runtime | 12 | ≤ 5 |
| Sistemas operativos donde arranca y pasa tests | 0 verificados | Linux, Windows, macOS |

## Forma del repositorio

```
lego/
  core/                 framework, namespace Lego\
    Http/               router propio, Request, Response sin die, kernel, middleware
    View/               Component, Document, recolector de assets, escape por defecto
    Module/             escáner de modules/, atributos Module, Screen, Resource, Field
    Action/             atributo Action, registro, ejecución, autorización, confirmación
    Auth/               usuarios, sesiones en Postgres, roles y permisos
    Database/           migrador con transacciones, conexión Eloquent
    Console/            CLI
    ui/                 componentes compartidos: shell, tabla, formulario, campos, diálogo
  modules/              una carpeta por módulo de app con todo dentro
    Users/  Roles/
  public/index.php
  tests/  Unit/  Feature/  Architecture/  Browser/
  doc/                  docs en español
  AGENTS.md             puerta de entrada única para humanos e IA
```

Un módulo declarativo debe verse así:

```php
// modules/Clients/Client.php
#[Module(label: 'Clientes', icon: 'people-outline')]
#[Resource]                       // lista, crear, editar, borrar y sus acciones
final class Client extends Model
{
    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre')->required()->searchable(),
            Email::make('email', 'Correo')->unique(),
            Money::make('credit', 'Crédito')->sortable(),
        ];
    }

    #[Action(description: 'Bloquea al cliente', destructive: true, confirm: true)]
    public function block(string $reason): void { /* escape explícito */ }
}
```

De esa clase se derivan rutas, menú, permisos, endpoints, tabla, formularios, validación y el catálogo de acciones. El escape explícito es un `#[Action]` o una pantalla propia en la misma carpeta.

## Fases

Estado al 2026-10-15: fases 0 a 4 terminadas en la rama `lego-2`. Siguiente: fase 5. Guías: [modulos.md](modulos.md), [frontend.md](frontend.md). Pendientes del dueño: [lego2/pendientes.md](lego2/pendientes.md).

Cada fase termina con tests en verde, revisión de código, commit, despliegue en el mini PC y un checkpoint contigo antes de la siguiente.

### Fase 0. Cerrar el diagnóstico y decidir

Objetivo: no escribir código de Lego 2 sobre supuestos abiertos.

- Consolidar la auditoría en un solo reporte. El paso final no corrió por falta de créditos; las nueve secciones ya están en el repo.
- Correr las tres dimensiones que el crítico de cobertura marcó y no llegaron a ejecutarse: archivos y uploads, contrato de navegación de módulos abiertos, y validación y errores. La segunda es la más importante, porque toca la funcionalidad principal.
- Ronda de decisiones contigo, una por una, con mi recomendación. Lista abajo.
- Registrar cada decisión en `doc/lego2/decisiones.md` con su porqué, en formato corto.

Salida: decisiones cerradas y reporte consolidado. Sin código.

### Fase 1. Esqueleto que camina

Objetivo: una página y un fragmento renderizados por el núcleo nuevo, con tests en tres sistemas operativos.

- Rama `lego-2`. El código actual pasa a `legacy/`, fuera del autoload, como referencia de lectura hasta que se borre en la fase 5.
- Router propio de unas 150 líneas con las tres capas. Reemplaza a Flight.
- `Request` y `Response` como objetos, sin `echo` ni `die`, para que todo sea testeable.
- Manejo de errores uniforme: JSON en `/api`, fragmento en `/component`, página en `/`. Nunca stack traces fuera de desarrollo.
- Servidor de assets en PHP con confinamiento por `realpath`, ETag y versión por fecha de modificación. Funciona igual con nginx y con `php -S`, sin symlinks.
- `Component` base con escape HTML por defecto y un recolector de assets por página que deduplica y versiona.
- Despliegue compatible con Dokploy, portable y replicable: imagen autocontenida con el código y las dependencias dentro, configuración solo por variables de entorno, migraciones idempotentes al arrancar, healthcheck HTTP, datos en volúmenes con nombre, y un compose de tres servicios sin `container_name` ni puertos fijos. El mismo compose se usa en el mini PC y en Dokploy.
- Pest con suites Unit, Feature y Architecture. CI en GitHub Actions con matriz Linux, Windows y macOS.
- `AGENTS.md` y el esqueleto de `doc/`.

Criterio de salida: CI verde en los tres sistemas; tests de router, respuesta, assets y componente.

### Fase 2. Seguridad e identidad por defecto

Objetivo: que sea imposible publicar un endpoint abierto por descuido.

- Migrador nuevo: migraciones solo de esquema, cada una en transacción, con estado y rollback por paso. Seeds aparte.
- `php lego install` crea el admin con contraseña generada que se muestra una vez. Sin hashes sembrados en el código.
- Sesiones en Postgres con token opaco hasheado, cookie HttpOnly y SameSite, renovación deslizante y revocación real en logout. Sin JWT ni Redis.
- CSRF en toda mutación.
- Middleware de autenticación que niega por defecto en `/api` y `/component`. Un fragmento sin sesión responde 401, y el cliente redirige.
- Modelo de permisos `módulo.operación`, roles en base de datos y una única función `authorize(usuario, acción, entrada, registro)` que ya admite permisos por fila en el futuro.
- Tabla `action_log` de solo escritura, compartida por personas, CLI y futuros agentes.

Criterio de salida: tests de feature contra Postgres para login, expiración, logout, CSRF, 401 y 403; un test de arquitectura que falla si existe una ruta sin política de acceso.

### Fase 3. Módulos declarativos y acciones

Objetivo: el corazón de Lego 2. Un módulo nuevo sin tocar el núcleo.

- Escáner de `modules/` que descubre `#[Module]`, `#[Screen]`, `#[Resource]` y `#[Action]`. Caché solo en producción.
- Menú derivado del código. La base de datos guarda solo lo que decidas en la fase 0.
- Definición de campos tipada como única fuente para validación, columnas de tabla, inputs de formulario, filtros y JSON Schema.
- `#[Resource]` genera las acciones `list`, `get`, `create`, `update` y `delete` con sus permisos.
- Registro de acciones y un solo endpoint `POST /api/actions/{nombre}`, más el catálogo filtrado por permisos.
- Confirmación en dos fases para acciones destructivas, con token de un solo uso consumido en `action_log`.
- Cada pantalla embebe en su HTML el catálogo de acciones permitidas para ese usuario.
- `php lego make:module Nombre` y `php lego run accion --as=usuario`.

Criterio de salida: un módulo de prueba con modelo y migración produce lista, crear, editar y borrar con permisos; un test de paridad comprueba que cada acción es alcanzable por botón, endpoint y CLI.

### Fase 4. Shell y módulos abiertos

Objetivo: la experiencia de navegar entre módulos sin recargar y sin perder estado, rehecha pequeña.

- Frontend solo con módulos ES nativos. Sin funciones globales, sin `new Function`, un solo bus de eventos.
- La URL es la fuente de verdad: `pushState`, botón atrás y enlaces directos a cada módulo y registro.
- Los módulos abiertos siguen montados en el DOM para conservar scroll, filtros y formularios a medio escribir mientras no se recargue la página. Tras F5 solo se restaura el módulo activo, indicado por la URL (D8).
- Ciclo de vida por instancia con `AbortController`. Todo se busca dentro de la raíz del módulo, así que crear y editar pueden estar abiertos a la vez.
- Tabla genérica renderizada en servidor con orden, filtro y paginación por query string, mejorada con un módulo JS pequeño.
- Formulario genérico desde los campos, con errores de validación por campo devueltos por el servidor.
- Diálogos y avisos con `<dialog>` nativo.
- Un solo archivo de tokens CSS, con los temas que decidas en la fase 0.
- Las tres acciones de UI fijas para la futura IA: navegar, abrir registro y aplicar filtro, más el bloque `ui` en las respuestas de acciones.

Criterio de salida: test de navegador que abre tres módulos, edita un registro, usa el botón atrás y, tras F5, vuelve al módulo de la URL.

### Fase 5. Traer lo que vale y borrar el resto

Objetivo: Lego 2 completo y sin legado.

- Portar Usuarios y Roles con editor de permisos como módulos de `modules/`.
- Portar lo marcado keep en la auditoría: breadcrumb, botón de icono, los tokens de color que sobreviven.
- Borrar `legacy/`, AI/, la serie de docs vieja y todo lo marcado drop.
- Documentación final en español: README corto, `doc/` por tema, guía "crear un módulo" verificada por un test que la ejecuta paso a paso.
- Desplegar en el mini PC y etiquetar `v2.0.0-alpha`.

Criterio de salida: las metas de la tabla de arriba se cumplen y se miden en CI.

### Fase 6. Terreno listo para la IA

Objetivo: dejar la integración a un adaptador de distancia, sin construir la IA.

- Auditoría de paridad: cada acción tiene descripción para humanos y para IA, esquema de entrada y anotaciones.
- Cabeceras `Permissions-Policy` y CSP estricta por defecto.
- Documento corto que explica cómo se conectaría WebMCP, MCP remoto o un asistente propio, con los pasos de la investigación.
- No se construyen `tools.js`, adaptador WebMCP, endpoint MCP ni chat. Se revisa el estado de WebMCP después de TPAC, a fines de octubre.

### Después, fuera de este plan

Archivos y uploads con disco local como módulo opcional, asistente IA, migración de FloraFresh.

## Decisiones para la fase 0

Cerradas el 2026-10-13. El registro vinculante, con el porqué de cada una y la decisión D16 de despliegue en Dokploy, está en [lego2/decisiones.md](lego2/decisiones.md). La tabla de abajo queda como resumen.

| # | Decisión | Recomendación |
|---|---|---|
| 1 | Fuente de verdad del menú | Solo código. Sin tabla de menú ni MenuConfig en 2.0 |
| 2 | Modelo de permisos | `módulo.operación` por rol, roles en BD, firma que admita permisos por fila |
| 3 | Sesión | Token opaco en Postgres, cookie HttpOnly, sin JWT, varias sesiones por usuario |
| 4 | Grupos de autenticación | Se eliminan; basta usuarios y roles |
| 5 | Estilo de API | Acciones POST por nombre; el CRUD es un conjunto de acciones |
| 6 | Tabla | Propia, renderizada en servidor; AG Grid sale |
| 7 | Crear y editar | Un formulario con modo, abierto como módulo con el id en la URL; varias instancias permitidas |
| 8 | Módulos abiertos tras F5 | Solo se restaura el activo, indicado por la URL |
| 9 | Temas | Oscuro y claro sobre un solo archivo de tokens |
| 10 | ORM y CLI | Mantener Eloquent; `symfony/console` para la CLI |
| 11 | Archivos y uploads | Fuera del núcleo 2.0 |
| 12 | Entornos | Docker con nginx y `php -S` para desarrollo, ambos con tests |
| 13 | Dónde corren los tests | En el mini PC por SSH y en GitHub Actions; requiere publicar la rama en GitHub |
| 14 | Destino del código viejo | `legacy/` en la rama durante la migración, borrado al final de la fase 5 |
| 15 | CSS propio por módulo | Permitido solo con prefijo del módulo y sin definir tokens, verificado por test |

## Cómo se ejecuta

- Un agente principal coordina; las tareas independientes de cada fase se reparten entre subagentes y cada entrega pasa por revisión antes del commit.
- Commits pequeños en la rama `lego-2`. Nada se publica en GitHub ni se despliega fuera del mini PC sin tu visto bueno.
- Al final de cada fase: resumen, métricas contra la tabla de objetivos y checkpoint contigo.
