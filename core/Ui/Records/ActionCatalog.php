<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\Action\ActionDefinition;
use Lego\View\Component;

/**
 * Embeds in the screen's HTML the actions the current user may run here, as
 * inert JSON. Scripts and future AI integrations read this catalog instead of
 * asking the server again (see doc/investigacion-webmcp-2026-10-13.md).
 */
final class ActionCatalog extends Component
{
    /** @param list<ActionDefinition> $actions */
    public function __construct(private readonly string $module, private readonly array $actions)
    {
    }

    protected function render(): string
    {
        $json = json_encode(
            array_map(static fn (ActionDefinition $a): array => $a->toArray(), $this->actions),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return '<script type="application/json" data-lego-actions="' . self::e($this->module) . '">' . $json . '</script>';
    }
}
