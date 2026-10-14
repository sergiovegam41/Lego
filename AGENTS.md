# AGENTS.md

Puerta de entrada única para personas y agentes de IA que trabajan en Lego. Si solo lees un archivo, que sea este.

## Qué es Lego

Framework PHP 8.3 para paneles de administración. Un componente es una clase PHP con parámetros tipados que devuelve HTML. Los módulos se declaran en una carpeta y el framework deduce rutas, menú, permisos y acciones.

Estado: **Lego 2 en construcción** en la rama `lego-2`. El plan está en [doc/plan-lego-2.md](doc/plan-lego-2.md).

## Mapa

| Ruta | Contenido |
|---|---|
| `core/` | El framework, namespace `Lego\`. |
| `core/Http/` | Router, Request, Response, Kernel, errores y archivos estáticos. |
| `core/View/` | `Component`, recolección de assets y documentos. |
| `core/Ui/` | Componentes del framework con su CSS y JS al lado. Se sirven en `/_lego/`. |
| `core/Console/` | La CLI `php lego`, sobre symfony/console. |
| `modules/` | Módulos de la aplicación, una carpeta cada uno, namespace `Modules\`. Se sirven en `/_modules/`. |
| `public/index.php` | Única entrada HTTP, para nginx y para `php -S`. |
| `tests/` | Pest: `Unit`, `Feature`, `Architecture`. |
| `docker/`, `docker-compose.yml` | Imagen y despliegue. Ver [doc/despliegue.md](doc/despliegue.md). |
| `doc/` | Documentación en español. |
| `legacy/` | Lego 1, solo lectura. Se borra al terminar la migración. No lo modifiques ni lo importes. |

## Reglas

Las decisiones vinculantes están en [doc/lego2/decisiones.md](doc/lego2/decisiones.md). Las que más afectan al día a día:

1. **Código en inglés, documentación en español.**
2. **Una sola forma de hacer cada cosa.** Antes de agregar un mecanismo, busca el que ya existe.
3. **La ruta de cada archivo coincide exactamente con su namespace**, mayúsculas incluidas. Un test lo verifica.
4. **El núcleo no lee superglobales** salvo en `Request` y `Environment`, **ni escribe salida** salvo en `Response::send`. Los handlers devuelven valores.
5. **Todo valor que llega al HTML pasa por `self::e()`** dentro de los componentes.
6. **Los assets van junto al componente** y se declaran en `$styles` y `$scripts` con rutas relativas a su carpeta. Nada de symlinks ni copias a `public/`.
7. **Configuración solo por variables de entorno.** `.env` es opcional.
8. **Todo cambio llega con tests** y `composer test` en verde.

## Comandos

```bash
composer install
composer test            # todas las suites
php lego serve           # servidor de desarrollo en http://127.0.0.1:8000
php lego routes          # rutas registradas
docker compose -f docker-compose.yml -f compose.local.yml up -d --build
```

## Capas HTTP

| Prefijo | Responde | Errores |
|---|---|---|
| `/api/*` | JSON | `{"status": "error", "error": {"code", "message"}, "trace_id"}` |
| `/component/*` | Fragmento HTML para el shell | Fragmento `.lego-error` |
| resto | Página HTML completa | Página de error |

Un handler puede devolver `Response`, un arreglo (JSON), un string (HTML) o un `Component`.
