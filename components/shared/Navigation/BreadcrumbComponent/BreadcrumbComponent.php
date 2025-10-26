<?php

namespace Components\Shared\Navigation\BreadcrumbComponent;

use Core\Components\CoreComponent\CoreComponent;

class BreadcrumbComponent extends CoreComponent
{
    public function __construct(
        public array $items = [],              // [['label' => 'Home', 'href' => '#'], ...]
        public string $separator = "/",         // Separator character
        public string $className = "",          // Additional CSS classes
        public string $id = "lego-breadcrumb",  // Component ID
    ) {
        $this->CSS_PATHS = ["./breadcrumb.css"];
        $this->JS_PATHS = ["./breadcrumb.js"];
    }

    protected function component(): string
    {
        if (empty($this->items)) {
            return '';
        }

        $classes = ["lego-breadcrumb"];
        if ($this->className) {
            $classes[] = $this->className;
        }
        $classStr = implode(" ", $classes);

        $breadcrumbHTML = '';
        $totalItems = count($this->items);

        foreach ($this->items as $index => $item) {
            $label = $item['label'] ?? '';
            $href = $item['href'] ?? '#';
            $isLast = ($index === $totalItems - 1);

            if ($isLast) {
                // Last item - no link, just text
                $breadcrumbHTML .= <<<HTML
                <span class="lego-breadcrumb__item lego-breadcrumb__item--active">
                    {$label}
                </span>
HTML;
            } else {
                // Regular item with link
                $breadcrumbHTML .= <<<HTML
                <a href="{$href}" class="lego-breadcrumb__item lego-breadcrumb__item--link">
                    {$label}
                </a>
                <span class="lego-breadcrumb__separator">{$this->separator}</span>
HTML;
            }
        }

        return <<<HTML
<nav class="{$classStr}" id="{$this->id}" aria-label="Breadcrumb">
    <ol class="lego-breadcrumb__list">
        {$breadcrumbHTML}
    </ol>
</nav>
HTML;
    }
}
