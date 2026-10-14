<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Single-line text, trimmed. Maximum length 255 unless changed with max(). */
class Text extends Field
{
    protected ?int $minLength = null;

    protected ?int $maxLength = 255;

    /** @var array{0: string, 1: string}|null regex and message */
    protected ?array $pattern = null;

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

    /** Requires the value to match a regular expression, e.g. ->pattern('/^[a-z_]+$/', 'Solo minúsculas y guion bajo.'). */
    public function pattern(string $regex, string $message): static
    {
        $this->pattern = [$regex, $message];

        return $this;
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
        if ($this->pattern !== null && preg_match($this->pattern[0], (string) $value) !== 1) {
            $errors[] = ['rule' => 'pattern', 'message' => $this->pattern[1]];
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
