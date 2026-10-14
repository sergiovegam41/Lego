<?php

declare(strict_types=1);

namespace Lego\Action;

/**
 * Outcome of an action, in the response envelope of decision D17:
 *
 * - ok: {"status": "ok", "data": ..., "ui": {...}?}                  200, 201 for create
 * - confirmation_required: {"status": "confirmation_required",
 *   "confirmation": {"token", "expires_at", "message", "preview"}}    428
 *
 * `ui` tells the screen what to do next (focus a record, go back to a list);
 * the CLI and remote agents ignore it.
 */
final readonly class ActionResult
{
    public const OK = 'ok';

    public const CONFIRMATION_REQUIRED = 'confirmation_required';

    /**
     * @param array<string, mixed>|null $ui
     * @param array{token: string, expires_at: string, message: string, preview: array<string, mixed>}|null $confirmation
     */
    private function __construct(
        public string $status,
        public mixed $data = null,
        public ?array $ui = null,
        public ?array $confirmation = null,
        public bool $created = false,
    ) {
    }

    /** @param array<string, mixed>|null $ui */
    public static function ok(mixed $data, ?array $ui = null, bool $created = false): self
    {
        return new self(self::OK, $data, $ui, null, $created);
    }

    /** @param array{token: string, expires_at: string, message: string, preview: array<string, mixed>} $confirmation */
    public static function confirmationRequired(array $confirmation): self
    {
        return new self(self::CONFIRMATION_REQUIRED, confirmation: $confirmation);
    }

    public function needsConfirmation(): bool
    {
        return $this->status === self::CONFIRMATION_REQUIRED;
    }

    public function httpStatus(): int
    {
        return match (true) {
            $this->needsConfirmation() => 428,
            $this->created => 201,
            default => 200,
        };
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->needsConfirmation()
            ? ['status' => $this->status, 'confirmation' => $this->confirmation]
            : array_filter(['status' => $this->status, 'data' => $this->data, 'ui' => $this->ui], static fn (mixed $v): bool => $v !== null);
    }
}
