# Lego

Framework PHP para paneles de administración, construido con componentes, módulos declarativos y acciones.

> **Lego 2 está en construcción** en la rama `lego-2`. Lego 1 vive en `legacy/` hasta terminar la migración.

```php
final class Card extends Component
{
    protected array $styles = ['card.css'];

    public function __construct(private string $title) {}

    protected function render(): string
    {
        return '<article class="card">' . self::e($this->title) . '</article>';
    }
}
```

## Empezar

```bash
composer install
php lego serve      # http://127.0.0.1:8000
composer test
```

Con Docker, en local o en Dokploy: [doc/despliegue.md](doc/despliegue.md).

## Documentación

- [AGENTS.md](AGENTS.md): mapa del repositorio y reglas, para personas y agentes de IA.
- [doc/plan-lego-2.md](doc/plan-lego-2.md): plan por fases.
- [doc/lego2/decisiones.md](doc/lego2/decisiones.md): decisiones de diseño.

## Licencia

MIT.
