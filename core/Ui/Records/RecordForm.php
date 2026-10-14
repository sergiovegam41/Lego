<?php

declare(strict_types=1);

namespace Lego\Ui\Records;

use Lego\Action\ActionDefinition;
use Lego\Data\Fields\Checklist;
use Lego\Data\Fields\Field;
use Lego\Data\Fields\Number;
use Lego\Data\Fields\Select;
use Lego\Data\Fields\Text;
use Lego\Http\Csrf;
use Lego\Module\ModuleDefinition;
use Lego\View\Component;

/**
 * Create and edit screen of a Record module (decision D7: one form, two modes).
 *
 * Inputs come from the fields; server-side errors appear under each field.
 * In edit mode it also shows the delete button and a small form per custom
 * record action the user may run. Without update permission it is read-only.
 */
final class RecordForm extends Component
{
    public const CREATE = 'create';

    public const UPDATE = 'update';

    protected array $styles = ['records.css'];

    /**
     * @param list<Field>                                    $fields
     * @param array<string, string|list<string>>             $values      Field name => form value.
     * @param array<string, list<array{message: string}>>    $errors      Field name => errors.
     * @param list<ActionDefinition>                         $allowed     Actions of this module the user may run.
     * @param array<string, array<string, list<array{message: string}>>> $actionErrors Action name => field errors.
     */
    public function __construct(
        private readonly ModuleDefinition $module,
        private readonly array $fields,
        private readonly string $mode,
        private readonly string $csrfToken,
        private readonly array $values = [],
        private readonly array $errors = [],
        private readonly array $allowed = [],
        private readonly ?int $id = null,
        private readonly array $actionErrors = [],
        private readonly string $domPrefix = 'lg-',
    ) {
    }

    protected function render(): string
    {
        $operations = array_map(static fn (ActionDefinition $a): string => $a->operation, $this->allowed);
        $editable = in_array($this->mode === self::CREATE ? 'create' : 'update', $operations, true);
        $base = $this->module->url();
        $action = self::e($this->mode === self::CREATE ? $base . '/new' : $base . '/' . $this->id);
        $title = self::e($this->mode === self::CREATE ? 'Nuevo · ' . $this->module->label : $this->module->label . ' #' . $this->id);
        $csrf = $this->csrfField();

        $inputs = '';
        foreach ($this->fields as $field) {
            $inputs .= $this->fieldRow($field, $editable);
        }

        $summary = $this->errors === []
            ? ''
            : '<p class="lego-records__alert" role="alert">Revisa los campos marcados.</p>';
        $save = $editable ? '<button class="lego-button lego-button--primary" type="submit">Guardar</button>' : '';
        $back = self::e($base);
        $extra = $this->mode === self::UPDATE ? $this->recordActions($operations) : '';
        $moduleName = self::e($this->module->name);
        $behavior = $this->behavior($this->mode === self::CREATE ? 'create' : 'update', 'list');

        return <<<HTML
            <section class="lego-records" data-module="{$moduleName}">
              <header class="lego-records__header"><h1>{$title}</h1></header>
              {$summary}
              <form class="lego-records__form" method="post" action="{$action}" novalidate {$behavior} data-lego-dirty>
                {$csrf}
                {$inputs}
                <div class="lego-records__buttons">{$save}<a class="lego-button" href="{$back}" data-lego-close>Volver</a></div>
              </form>
              {$extra}
            </section>
            HTML;
    }

    private function fieldRow(Field $field, bool $editable): string
    {
        $name = self::e($field->name);
        $id = $this->domPrefix . $name;
        $errors = $this->errors[$field->name] ?? [];
        $describedBy = $errors === [] ? '' : " aria-invalid=\"true\" aria-describedby=\"{$id}-error\"";
        $disabled = $editable ? '' : ' disabled';
        $required = $field->isRequired() && $editable ? ' required' : '';
        $value = $this->values[$field->name] ?? '';
        if ($field instanceof Checklist) {
            return $this->checklistRow($field, is_array($value) ? $value : [], $id, $editable, $errors);
        }
        $value = is_array($value) ? '' : $value;
        $attributes = "id=\"{$id}\" name=\"{$name}\"{$required}{$disabled}{$describedBy}";

        $control = match ($field->type()) {
            'textarea' => "<textarea {$attributes} rows=\"4\">" . self::e($value) . '</textarea>',
            'boolean' => "<input type=\"hidden\" name=\"{$name}\" value=\"0\"{$disabled}>"
                . "<input type=\"checkbox\" {$attributes} value=\"1\"" . ($value === '1' ? ' checked' : '') . '>',
            'select' => "<select {$attributes}>" . $this->options($field, $value) . '</select>',
            'number' => "<input type=\"number\" {$attributes} value=\"" . self::e($value) . '"'
                . ($field instanceof Number && $field->isInteger() ? ' step="1"' : ' step="any"') . '>',
            'money' => "<input type=\"text\" inputmode=\"decimal\" {$attributes} value=\"" . self::e($value) . '">',
            'date' => "<input type=\"date\" {$attributes} value=\"" . self::e($value) . '">',
            'email' => "<input type=\"email\" {$attributes} value=\"" . self::e($value) . '"' . $this->maxLength($field) . '>',
            default => "<input type=\"text\" {$attributes} value=\"" . self::e($value) . '"' . $this->maxLength($field) . '>',
        };

        $help = $field->helpText() === null ? '' : '<small>' . self::e($field->helpText()) . '</small>';
        $messages = '';
        foreach ($errors as $error) {
            $messages .= self::e($error['message']) . ' ';
        }
        $errorLine = $messages === '' ? '' : "<p class=\"lego-field__error\" id=\"{$id}-error\">" . trim($messages) . '</p>';
        $label = self::e($field->label) . ($field->isRequired() ? ' <span aria-hidden="true">*</span>' : '');

        return "<div class=\"lego-field lego-field--{$field->type()}\" data-field=\"{$name}\"><label for=\"{$id}\">{$label}</label>{$control}{$help}{$errorLine}</div>";
    }

    /**
     * Checkboxes for a Checklist, sent as name[]. The leading empty value makes
     * "nothing checked" reach the server as an empty list.
     *
     * @param list<string>                     $checked
     * @param list<array{message: string}>     $errors
     */
    private function checklistRow(Checklist $field, array $checked, string $id, bool $editable, array $errors): string
    {
        $name = self::e($field->name);
        $disabled = $editable ? '' : ' disabled';
        $boxes = "<input type=\"hidden\" name=\"{$name}[]\" value=\"\"{$disabled}>";
        $index = 0;
        foreach ($field->choices() as $value => $label) {
            $boxId = $id . '-' . $index++;
            $on = in_array((string) $value, $checked, true) ? ' checked' : '';
            $boxes .= "<label class=\"lego-checklist__item\" for=\"{$boxId}\"><input type=\"checkbox\" id=\"{$boxId}\" name=\"{$name}[]\" value=\""
                . self::e($value) . "\"{$on}{$disabled}> " . self::e($label) . '</label>';
        }

        $error = $errors === []
            ? ''
            : "<p class=\"lego-field__error\" id=\"{$id}-error\">" . self::e(implode(' ', array_column($errors, 'message'))) . '</p>';

        return "<fieldset class=\"lego-field lego-field--checklist\" data-field=\"{$name}\"><legend>" . self::e($field->label)
            . "</legend><div class=\"lego-checklist\">{$boxes}</div>{$error}</fieldset>";
    }

    private function options(Field $field, string $value): string
    {
        $html = $field->isRequired() ? '' : '<option value=""></option>';
        if ($field instanceof Select) {
            foreach ($field->choices() as $option => $label) {
                $selected = (string) $option === $value ? ' selected' : '';
                $html .= '<option value="' . self::e($option) . '"' . $selected . '>' . self::e($label) . '</option>';
            }
        }

        return $html;
    }

    private function maxLength(Field $field): string
    {
        return $field instanceof Text && $field->maxLength() !== null ? ' maxlength="' . $field->maxLength() . '"' : '';
    }

    /** @param list<string> $operations */
    private function recordActions(array $operations): string
    {
        $base = $this->module->url() . '/' . $this->id;
        $html = '';

        foreach ($this->allowed as $action) {
            if ($action->scope !== ActionDefinition::RECORD || in_array($action->operation, ['get', 'update', 'delete'], true)) {
                continue;
            }
            $html .= $this->customActionForm($action, $base . '/actions/' . $action->operation);
        }

        if (in_array('delete', $operations, true)) {
            $html .= '<form class="lego-records__action" method="post" action="' . self::e($base . '/delete') . '" ' . $this->behavior('delete', 'list') . '>'
                . $this->csrfField()
                . '<button class="lego-button lego-button--danger" type="submit">Eliminar</button></form>';
        }

        return $html === '' ? '' : "<div class=\"lego-records__actions\">{$html}</div>";
    }

    private function customActionForm(ActionDefinition $action, string $url): string
    {
        $errors = $this->actionErrors[$action->name] ?? [];
        $inputs = '';
        foreach ($action->inputSchema()['properties'] ?? [] as $name => $schema) {
            if ($name === 'id') {
                continue;
            }
            $type = is_array($schema['type']) ? $schema['type'][0] : $schema['type'];
            $id = $this->domPrefix . self::e($action->operation) . '-' . self::e($name);
            $label = self::e($schema['title'] ?? str_replace('_', ' ', (string) $name));
            $required = in_array($name, $action->inputSchema()['required'] ?? [], true) ? ' required' : '';
            $control = match ($type) {
                'boolean' => "<input type=\"hidden\" name=\"{$name}\" value=\"0\"><input type=\"checkbox\" id=\"{$id}\" name=\"{$name}\" value=\"1\">",
                'integer', 'number' => "<input type=\"number\" id=\"{$id}\" name=\"{$name}\"" . ($type === 'integer' ? ' step="1"' : ' step="any"') . "{$required}>",
                default => "<input type=\"text\" id=\"{$id}\" name=\"{$name}\"{$required}>",
            };
            $error = isset($errors[$name])
                ? '<p class="lego-field__error">' . self::e(implode(' ', array_column($errors[$name], 'message'))) . '</p>'
                : '';
            $inputs .= "<div class=\"lego-field\" data-field=\"" . self::e($name) . "\"><label for=\"{$id}\">{$label}</label>{$control}{$error}</div>";
        }

        $class = $action->destructive ? 'lego-button lego-button--danger' : 'lego-button';

        return '<form class="lego-records__action" method="post" action="' . self::e($url) . '" data-action="' . self::e($action->name) . '" '
            . $this->behavior($action->operation, 'reload') . '>'
            . $this->csrfField() . $inputs
            . '<button class="' . $class . '" type="submit" title="' . self::e($action->description) . '">' . self::e($action->label) . '</button></form>';
    }

    /**
     * Attributes read by lego/ui/form.js: run the action through the JSON API
     * and then go back to the list ("list") or reload this screen ("reload").
     */
    private function behavior(string $operation, string $after): string
    {
        return 'data-lego="form" data-lego-action="' . self::e($this->module->name . '_' . $operation) . '"'
            . ' data-lego-module="' . self::e($this->module->name) . '"'
            . ($this->id === null ? '' : ' data-lego-record="' . $this->id . '"')
            . ' data-lego-after="' . $after . '" data-lego-list="' . self::e($this->module->url()) . '"';
    }

    private function csrfField(): string
    {
        return '<input type="hidden" name="' . Csrf::FIELD . '" value="' . self::e($this->csrfToken) . '">';
    }
}
