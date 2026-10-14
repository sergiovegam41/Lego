<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Single-line text, trimmed. Maximum length 255 unless changed with max(). */
class Text extends Field
{
    protected ?int $minLength = null;

    protected ?int $maxLength = 255;

    public function type(): string
    {
        return 'text';
    }

    public function min(int $length): static
    {
        $this->minLength = $length;

        return $this;
    }

    public function max(?int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    public function maxLength(): ?int
    {
        return $this->maxLength;
    }

    protected function cast(mixed $raw): mixed
    {
        if (!is_scalar($raw)) {
            throw $this->invalid('Debe ser un texto.');
        }

        return trim((string) $raw);
    }

    public function check(mixed $value): array
    {
        $length = mb_strlen((string) $value);
        $errors = [];
        if ($this->minLength !== null && $length < $this->minLength) {
            $errors[] = ['rule' => 'min', 'params' => ['min' => $this->minLength], 'message' => "Debe tener al menos {$this->minLength} caracteres."];
        }
        if ($this->maxLength !== null && $length > $this->maxLength) {
            $errors[] = ['rule' => 'max', 'params' => ['max' => $this->maxLength], 'message' => "Debe tener como máximo {$this->maxLength} caracteres."];
        }

        return $errors;
    }

    protected function valueSchema(): array
    {
        return array_filter(
            ['type' => 'string', 'minLength' => $this->minLength, 'maxLength' => $this->maxLength],
            static fn (mixed $v): bool => $v !== null,
        );
    }
}
