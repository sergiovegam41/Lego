<?php

declare(strict_types=1);

namespace Lego\Action;

use Attribute;

/**
 * Exposes a method of a module Record as an action: the escape hatch for
 * anything beyond CRUD. Instance methods act on one record (the input carries
 * its id); static methods act on the module as a whole.
 *
 *     #[Action(label: 'Bloquear', description: 'Impide que el cliente compre.', destructive: true, confirm: true)]
 *     public function block(string $reason): void
 *
 * Parameters must be scalar (string, int, float, bool); they become the
 * action's input schema. A parameter typed Lego\Auth\Identity receives the
 * user running the action instead. The permission is
 * "<module>.<method_in_snake_case>".
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Action
{
    public function __construct(
        public string $label,
        public string $description = '',
        public bool $readOnly = false,
        public bool $destructive = false,
        public bool $idempotent = false,
        public bool $confirm = false,
    ) {
    }
}
