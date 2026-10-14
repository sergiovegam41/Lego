<?php

declare(strict_types=1);

namespace Lego\Ui\Shell;

use Lego\Ui\Records\ActionCatalog;
use Lego\View\Component;

/**
 * Root of every screen, in pages and in fragments alike. Its data attributes
 * are the contract with the client (core/Ui/js/lego.js):
 *
 * - data-lego-key     instance key, the path without the leading slash ("clients/5")
 * - data-lego-url     canonical URL of the instance, with its query
 * - data-lego-title   title for tabs and document.title
 * - data-lego-module  module name, when the screen belongs to one
 * - data-lego-record  record id, when the screen shows one record
 *
 * Module screens also embed the catalog of actions the user may run there
 * (<script type="application/json" data-lego-actions>).
 */
final class Screen extends Component
{
    public function __construct(
        public readonly string $key,
        private readonly string $url,
        private readonly string $title,
        private readonly Component $content,
        private readonly ?string $module = null,
        private readonly ?int $record = null,
        private readonly ?array $actions = null,
    ) {
    }

    /** Instance key of a path: "/clients/5" -> "clients/5", "/" -> "home". */
    public static function keyFor(string $path): string
    {
        $path = trim(preg_replace('#^/component(?=/|$)#', '', $path) ?? $path, '/');

        return $path === '' ? 'home' : $path;
    }

    /** Prefix for HTML ids inside this instance, so two open screens never collide. */
    public static function domPrefix(string $key): string
    {
        return 'lg-' . trim((string) preg_replace('/[^a-z0-9]+/i', '-', $key), '-') . '-';
    }

    protected function render(): string
    {
        $attributes = [
            'class' => 'lego-screen',
            'data-lego-screen' => '',
            'data-lego-key' => $this->key,
            'data-lego-url' => $this->url,
            'data-lego-title' => $this->title,
            'data-lego-module' => $this->module,
            'data-lego-record' => $this->record === null ? null : (string) $this->record,
        ];

        $html = '';
        foreach ($attributes as $name => $value) {
            if ($value !== null) {
                $html .= ' ' . $name . ($value === '' ? '' : '="' . self::e($value) . '"');
            }
        }

        $catalog = $this->actions === null ? '' : (string) new ActionCatalog($this->module ?? $this->key, $this->actions);

        return "<section{$html}>{$this->content}{$catalog}</section>";
    }
}
