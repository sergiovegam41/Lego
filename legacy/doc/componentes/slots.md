# Slots

Los slots son zonas nombradas dentro de un componente contenedor. Permiten layouts complejos donde el contenido de cada zona se define desde afuera.

Relacionado: [[componentes/core-component]] · [[componentes/composicion]]

---

## Para Qué Sirven

Cuando un componente tiene múltiples zonas con contenido variable: una tarjeta con encabezado y cuerpo diferentes, un modal con título, cuerpo y botones de acción, un layout con sidebar y contenido principal.

## Definir Slots

```php
class CardComponent extends CoreComponent
{
    public function __construct(
        public array $headerSlot = [],
        public array $bodySlot   = [],
        public array $footerSlot = []
    ) {}

    protected function component(): string
    {
        $header = $this->renderSlot($this->headerSlot);
        $body   = $this->renderSlot($this->bodySlot);
        $footer = $this->renderSlot($this->footerSlot);

        return <<<HTML
        <div class="card">
            <div class="card__header">{$header}</div>
            <div class="card__body">{$body}</div>
            <div class="card__footer">{$footer}</div>
        </div>
        HTML;
    }
}
```

## Usar Slots

```php
new CardComponent(
    headerSlot: [
        new TitleComponent(text: "Configuración"),
        new BadgeComponent(label: "Beta"),
    ],
    bodySlot: [
        new InputTextComponent(id: "nombre"),
        new InputTextComponent(id: "email"),
    ],
    footerSlot: [
        new ButtonComponent(label: "Guardar"),
        new ButtonComponent(label: "Cancelar", variant: "ghost"),
    ]
);
```

## `renderSlot()` vs `renderChildren()`

| Método | Cuándo usar |
|--------|-------------|
| `renderChildren()` | El componente tiene una sola zona de contenido (`$children`) |
| `renderSlot($slot)` | El componente tiene múltiples zonas nombradas |

## Slots Opcionales

Los slots son arrays vacíos por defecto, así el componente funciona aunque no se pase contenido:

```php
// Slot vacío → no renderiza nada
new CardComponent(bodySlot: [new TextComponent(text: "Solo el cuerpo")]);
// headerSlot y footerSlot son [] → no aparecen
```

## Visión

> A futuro, el sistema de slots tendrá soporte para slots con valores por defecto: si no se pasa contenido para un slot, el componente muestra un contenido predeterminado. Esto permite componentes más autónomos sin perder flexibilidad.
