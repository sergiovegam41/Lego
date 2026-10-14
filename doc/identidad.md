# Identidad visual

Sistema visual v1.0 de Lego (dirección 2a). Todo sale de una retícula de 4 px: el símbolo, el espaciado, los radios y los iconos. La idea es "tres módulos ya colocados y uno que agregas tú".

## Marca

- **Símbolo.** Cuatro celdas de 4 unidades con 1 de separación, en un cuadrado de 9 × 9. La celda de acento siempre va abajo a la derecha. Ángulos rectos, sin radios.
- **Nombre.** "Lego" en Instrument Sans 600, a 3 unidades del símbolo.
- **Área de respeto.** Una celda alrededor del conjunto.
- **Favicon.** Fondo de tinta, celdas en papel y acento claro. Por debajo de 24 px se usa solo el símbolo.
- **Restricción.** La marca no evoca a The LEGO Group: nada de ladrillos con tetones, rojo y amarillo ni tipografías de juguete.

En código:

| Pieza | Archivo |
|---|---|
| Logo (símbolo y nombre) | `core/Ui/Brand/Logo.php`, componente; `new Logo(withName: false)` da solo el símbolo |
| Favicon | `core/Ui/Brand/favicon.svg`, enlazado en todas las páginas por `Document` |

## Color

Cada token tiene valor para claro y para oscuro, y todo texto cumple AA (4,5:1) sobre la superficie de su tema.

| Token | Claro | Oscuro | Uso |
|---|---|---|---|
| `--lego-bg` | `#f5f3ef` | `#141311` | Fondo de página |
| `--lego-surface` | `#fdfcfa` | `#1c1a17` | Tarjetas, tablas, diálogos |
| `--lego-surface-2` | `#eeebe5` | `#26231f` | Hover, filas, campos |
| `--lego-border` | `#e4e0d8` | `#2c2925` | Divisores |
| `--lego-border-strong` | `#cfc9be` | `#3d3933` | Bordes de campos y botones |
| `--lego-text` | `#1d1b18` | `#ece8e1` | Texto principal |
| `--lego-text-muted` | `#66615a` | `#a39d93` | Etiquetas y ayudas |
| `--lego-accent` | `#17656b` | `#5fb7b0` | Acción principal, enlaces |
| `--lego-accent-hover` | `#0f4f54` | `#7fcac4` | Hover y pulsado |
| `--lego-accent-soft` | `#dfeae8` | `#1f3432` | Selección, ítem activo |
| `--lego-accent-text` | `#ffffff` | `#0e1c1b` | Texto sobre acento |
| `--lego-danger` / `-soft` | `#b3362f` / `#f6e3e1` | `#ec8a7f` / `#3a201d` | Errores, borrar |
| `--lego-success` / `-soft` | `#2f7327` / `#e6efe1` | `#86c46f` / `#1f2e1a` | Confirmación, activo |
| `--lego-warning` / `-soft` | `#9a5b0c` / `#f6ead9` | `#dca35a` / `#33261a` | Pendiente, aviso |

## Tipografía

- **Instrument Sans** 400, 500 y 600 para la interfaz.
- **IBM Plex Mono** 400 y 500 para código, rutas, cifras e identificadores.

Las dos tienen licencia SIL OFL y están autoalojadas en `core/Ui/Theme/fonts/` (la CSP no admite fuentes de otros orígenes).

| Estilo | Tamaño / interlínea | Peso |
|---|---|---|
| display | 44 / 48 | 600 |
| h1 | 28 / 32 | 600 |
| h2 | 20 / 28 | 600 |
| h3 | 16 / 24 | 600 |
| body | 14 / 20 | 400 |
| small | 12 / 16 | 500 |
| mono | 13 / 20 | 400 |

## Espacio, radios y sombras

- **Espaciado.** Base de 4 px: `--lego-space-1` (4) a `--lego-space-16` (64).
- **Radios.** 2 px para badges, 4 px para controles y 6 px para tarjetas. Nunca más de 6 px ni formas de píldora.
- **Altura de controles.** 36 px (`--lego-control-h`).
- **Elevación.** La separación la marca el borde. La sombra (`--lego-shadow`) es solo para lo que flota: menús, diálogos y avisos. En oscuro se sustituye por un borde fuerte.

## Iconos

Retícula de 20 px, trazo de 1,5 px, terminaciones y uniones rectas, sin rellenos. Para ampliar el set se puede usar Lucide (licencia ISC) con `stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"`. Todavía no están integrados en el shell.

## Reglas verificadas por tests

- Ninguna hoja de estilos fuera de `tokens.css` escribe un color, nombra una fuente ni define un token.
- Cada página enlaza el favicon y lleva el logo, y las cinco fuentes se sirven como `font/woff2`.
- En el navegador, Instrument Sans se carga desde el mismo origen.
