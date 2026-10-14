<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

use Closure;

/**
 * One value out of a list. Fixed options or options loaded when needed:
 *
 *     Select::make('tier', 'Nivel')->options(['basic' => 'Básico', 'gold' => 'Oro'])
 *     Select::make('role_id', 'Rol')->optionsFrom(fn () => Role::query()->pluck('label', 'id')->all())
 */
final class Select extends Field
{
    /** @var array<string, string>|null value => label */
    private ?array $options = [];

    private ?Closure $provider = null;

    public function type(): string
    {
        return 'select';
    }

    /** @param array<string|int, string> $options value => label */
    public function options(array $options): self
    {
        $this->options = self::normalizeOptions($options);
        $this->provider = null;

        return $this;
    }

    /** @param Closure(): array<string|int, string> $provider Called once, the first time the options are needed. */
    public function optionsFrom(Closure $provider): self
    {
        $this->provider = $provider;
        $this->options = null;

        return $this;
    }

    /** @return array<string, string> */
    public function choices(): array
    {
        return $this->options ??= self::normalizeOptions(($this->provider)());
    }

    protected function cast(mixed $raw): mixed
    {
        if (!is_scalar($raw) || is_bool($raw)) {
            throw $this->invalid('Debe ser una de las opciones.');
        }

        return (string) $raw;
    }

    public function check(mixed $value): array
    {
        return array_key_exists((string) $value, $this->choices())
            ? []
            : [['rule' => 'in', 'params' => ['options' => array_keys($this->choices())], 'message' => 'Elige una de las opciones.']];
    }

    public function display(mixed $value): string
    {
        return $value === null ? '' : ($this->choices()[(string) $value] ?? (string) $value);
    }

    protected function valueSchema(): array
    {
        return ['type' => 'string', 'enum' => array_map('strval', array_keys($this->choices()))];
    }

    /**
     * @param array<string|int, string> $options
     * @return array<string, string>
     */
    private static function normalizeOptions(array $options): array
    {
        return $options === [] ? [] : array_combine(array_map('strval', array_keys($options)), array_map('strval', array_values($options)));
    }
}
