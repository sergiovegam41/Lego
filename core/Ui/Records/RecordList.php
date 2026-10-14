<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\Action\ActionDefinition;
use Lego\Data\Fields\Field;
use Lego\Module\ModuleDefinition;
use Lego\View\Component;

/**
 * List screen of a Record module: search, sortable columns, pagination and a
 * "Nuevo" button, all as plain links and forms. Columns are the fields not
 * hidden from the table; values are formatted by each field.
 */
final class RecordList extends Component
{
    protected array $styles = ['records.css'];

    /**
     * @param list<Field>                                    $fields
     * @param array{items: list<array<string, mixed>>, meta: array<string, mixed>} $data
     * @param list<ActionDefinition>                         $allowed Actions of this module the user may run.
     */
    public function __construct(
        private readonly ModuleDefinition $module,
        private readonly array $fields,
        private readonly array $data,
        private readonly array $allowed,
    ) {
    }

    protected function render(): string
    {
        $meta = $this->data['meta'];
        $url = $this->module->url();
        $columns = array_values(array_filter($this->fields, static fn (Field $f): bool => $f->isInTable()));
        $can = array_column(array_map(static fn (ActionDefinition $a): array => [$a->operation, true], $this->allowed), 1, 0);

        $new = isset($can['create']) ? '<a class="lego-button lego-button--primary" href="' . self::e($url . '/new') . '">Nuevo</a>' : '';
        $search = $this->searchForm($meta);
        $head = '';
        foreach ($columns as $field) {
            $head .= '<th>' . $this->sortLink($field, $meta) . '</th>';
        }

        $rows = '';
        foreach ($this->data['items'] as $item) {
            $cells = '';
            foreach ($columns as $index => $field) {
                $value = self::e($field->display($item[$field->name] ?? null));
                $cells .= $index === 0 && (isset($can['update']) || isset($can['get']))
                    ? '<td><a href="' . self::e($url . '/' . $item['id']) . '">' . ($value === '' ? '#' . self::e($item['id']) : $value) . '</a></td>'
                    : "<td>{$value}</td>";
            }
            $rows .= '<tr data-id="' . self::e($item['id']) . '">' . $cells . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td class="lego-records__empty" colspan="' . max(1, count($columns)) . '">No hay registros.</td></tr>';
        }

        $title = self::e($this->module->label);
        $total = (int) $meta['total'];
        $catalog = new ActionCatalog($this->module->name, $this->allowed);
        $moduleName = self::e($this->module->name);
        $pagination = $this->pagination($meta);

        return <<<HTML
            <section class="lego-records" data-module="{$moduleName}" data-lego="table">
              <header class="lego-records__header">
                <h1>{$title}</h1>
                <span class="lego-records__count">{$total} registros</span>
                {$new}
              </header>
              {$search}
              <table class="lego-records__table">
                <thead><tr>{$head}</tr></thead>
                <tbody>{$rows}</tbody>
              </table>
              {$pagination}
              {$catalog}
            </section>
            HTML;
    }

    /** @param array<string, mixed> $meta */
    private function searchForm(array $meta): string
    {
        $searchable = array_filter($this->fields, static fn (Field $f): bool => $f->isSearchable());
        if ($searchable === []) {
            return '';
        }

        $q = self::e((string) $meta['q']);
        $sort = self::e((string) $meta['sort']);
        $dir = self::e((string) $meta['dir']);
        $action = self::e($this->module->url());

        return <<<HTML
            <form class="lego-records__search" method="get" action="{$action}" role="search">
              <input type="search" name="q" value="{$q}" placeholder="Buscar" aria-label="Buscar">
              <input type="hidden" name="sort" value="{$sort}">
              <input type="hidden" name="dir" value="{$dir}">
              <button type="submit">Buscar</button>
            </form>
            HTML;
    }

    /** @param array<string, mixed> $meta */
    private function sortLink(Field $field, array $meta): string
    {
        $label = self::e($field->label);
        if (!$field->isSortable()) {
            return $label;
        }

        $active = $meta['sort'] === $field->name;
        $dir = $active && $meta['dir'] === 'asc' ? 'desc' : 'asc';
        $arrow = $active ? ($meta['dir'] === 'asc' ? ' ▲' : ' ▼') : '';
        $query = http_build_query(array_filter(['q' => $meta['q'], 'sort' => $field->name, 'dir' => $dir]));

        return '<a href="' . self::e($this->module->url() . '?' . $query) . '">' . $label . $arrow . '</a>';
    }

    /** @param array<string, mixed> $meta */
    private function pagination(array $meta): string
    {
        if ((int) $meta['pages'] <= 1) {
            return '';
        }

        $link = function (int $page, string $text) use ($meta): string {
            $query = http_build_query(array_filter(['q' => $meta['q'], 'sort' => $meta['sort'], 'dir' => $meta['dir'], 'page' => $page]));

            return '<a href="' . self::e($this->module->url() . '?' . $query) . '">' . $text . '</a>';
        };

        $page = (int) $meta['page'];
        $pages = (int) $meta['pages'];
        $prev = $page > 1 ? $link($page - 1, 'Anterior') : '';
        $next = $page < $pages ? $link($page + 1, 'Siguiente') : '';

        return "<nav class=\"lego-records__pages\" aria-label=\"Páginas\">{$prev}<span>Página {$page} de {$pages}</span>{$next}</nav>";
    }
}
