<?php

declare(strict_types=1);

namespace Lego\Data;

use Illuminate\Database\Eloquent\Model;
use Lego\Auth\Identity;
use Lego\Data\Fields\Field;
use LogicException;

/**
 * Base of every module model: an Eloquent model that declares its fields.
 *
 *     #[Module(label: 'Clientes', icon: 'people-outline')]
 *     final class Client extends Record
 *     {
 *         public static function fields(): array
 *         {
 *             return [Text::make('name', 'Nombre')->required()->searchable()];
 *         }
 *     }
 *
 * Mass assignment is open because only validated, declared fields ever reach
 * the model: the Validator is the whitelist.
 */
abstract class Record extends Model
{
    protected $guarded = [];

    /** @return list<Field> */
    abstract public static function fields(): array;

    /** @return array<string, Field> keyed by field name */
    public static function fieldMap(): array
    {
        $map = [];
        foreach (static::fields() as $field) {
            if (isset($map[$field->name])) {
                throw new LogicException(static::class . " declares the field \"{$field->name}\" twice.");
            }
            $map[$field->name] = $field;
        }

        return $map;
    }

    /**
     * Business rules for the CRUD actions, checked before the change and
     * inside its transaction. Throw BusinessRuleViolation to refuse.
     *
     * @param string               $operation create, update or delete
     * @param array<string, mixed> $clean     validated input (empty for delete)
     * @param Identity             $by        the user acting
     */
    public function beforeChange(string $operation, array $clean, Identity $by): void
    {
    }

    /** Short human name of this record for messages, e.g. «Florería Lima». */
    public function label(): string
    {
        foreach (static::fields() as $field) {
            if (in_array($field->type(), ['text', 'email'], true)) {
                $value = $field->display($this->getAttribute($field->name));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '#' . $this->getKey();
    }

    /** @return array<string, mixed> id plus every declared field, as stored */
    public function toData(): array
    {
        $data = ['id' => $this->getKey()];
        foreach (static::fields() as $field) {
            $data[$field->name] = $this->getAttribute($field->name);
        }

        return $data;
    }
}
