<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\View\Component;

/**
 * Embeds in the screen's HTML the actions the current user may run here, as
 * inert JSON (each entry is ActionDefinition::toArray() plus "agent": whether
 * AI agents may run it). Scripts and future AI integrations read this catalog
 * instead of asking the server again (see doc/ia.md).
 */
final class ActionCatalog extends Component
{
    /** @param list<array<string, mixed>> $entries */
    public function __construct(private readonly string $module, private readonly array $entries)
    {
    }

    protected function render(): string
    {
        $json = json_encode(
            $this->entries,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        return '<script type="application/json" data-lego-actions="' . self::e($this->module) . '">' . $json . '</script>';
    }
}
