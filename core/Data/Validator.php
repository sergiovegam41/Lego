<?php

declare(strict_types=1);

namespace Lego\Data;

use Lego\Data\Fields\Field;
use Lego\Data\Fields\InvalidValue;
use Lego\Errors\ValidationFailed;

/**
 * Validates input against a record's fields. It is the whitelist: unknown keys
 * are rejected and only declared fields come out.
 *
 * - create: missing required fields fail; missing optional fields are left
 *   out so the database default applies.
 * - update: only the fields present in the input are considered (partial).
 */
final class Validator
{
    public const CREATE = 'create';

    public const UPDATE = 'update';

    /**
     * @param class-string<Record>  $recordClass
     * @param array<string, mixed>  $input
     * @param list<string>          $ignore Keys that are allowed but not fields (e.g. "id").
     * @return array<string, mixed> Normalized values, keyed by field name.
     *
     * @throws ValidationFailed
     */
    public function validate(string $recordClass, array $input, string $mode, ?Record $record = null, array $ignore = []): array
    {
        $fields = $recordClass::fieldMap();
        $errors = [];
        $clean = [];

        foreach (array_keys($input) as $key) {
            if (!isset($fields[$key]) && !in_array($key, $ignore, true)) {
                $errors[(string) $key][] = ['rule' => 'unknown_field', 'message' => 'Este campo no existe.'];
            }
        }

        foreach ($fields as $name => $field) {
            if (!array_key_exists($name, $input)) {
                // Update is partial. On create, an omitted optional field keeps
                // the database default instead of being forced to null.
                if ($mode === self::CREATE && $field->isRequired()) {
                    $errors[$name] = [['rule' => 'required', 'message' => "{$field->label} es obligatorio."]];
                }
                continue;
            }

            $value = null;
            $fieldErrors = $this->validateField($field, $input[$name], $value);
            if ($fieldErrors === [] && $field->isUnique() && $value !== null && $this->exists($recordClass, $name, $value, $record)) {
                $fieldErrors[] = ['rule' => 'unique', 'message' => "Ya existe un registro con este valor de {$field->label}."];
            }

            if ($fieldErrors !== []) {
                $errors[$name] = $fieldErrors;
            } else {
                $clean[$name] = $value;
            }
        }

        if ($errors !== []) {
            throw new ValidationFailed($errors);
        }

        return $clean;
    }

    /** @return list<array{rule: string, message: string, params?: array<string, mixed>}> */
    private function validateField(Field $field, mixed $raw, mixed &$value): array
    {
        try {
            $value = $field->normalize($raw);
        } catch (InvalidValue $e) {
            return [['rule' => 'type', 'message' => $e->getMessage()]];
        }

        if ($value === null) {
            return $field->isRequired() ? [['rule' => 'required', 'message' => "{$field->label} es obligatorio."]] : [];
        }

        return $field->check($value);
    }

    /** @param class-string<Record> $recordClass */
    private function exists(string $recordClass, string $column, mixed $value, ?Record $ignore): bool
    {
        $query = $recordClass::query()->where($column, $value);
        if ($ignore !== null) {
            $query->whereKeyNot($ignore->getKey());
        }

        return $query->exists();
    }
}
