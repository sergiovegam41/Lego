<?php

declare(strict_types=1);

namespace Lego\Action;

use InvalidArgumentException;
use Lego\Module\ModuleRegistry;

/**
 * Which actions an AI agent may run on behalf of a user (kill switch).
 *
 * Set with LEGO_AGENT_ACCESS:
 * - off   (default) agents cannot run anything,
 * - read  agents can run read-only actions,
 * - write also changes, but only in modules that opt in with
 *         #[Module(agentWrites: true)].
 *
 * It only narrows what the user could already do: the Gate still checks the
 * user's permissions on every call. Requests mark themselves as coming from
 * an agent with the header X-Lego-Channel: agent; the mark is used to deny
 * and to audit, never to grant.
 */
final class AgentPolicy
{
    public const OFF = 'off';

    public const READ = 'read';

    public const WRITE = 'write';

    public function __construct(private readonly string $access, private readonly ModuleRegistry $modules)
    {
        if (!in_array($access, [self::OFF, self::READ, self::WRITE], true)) {
            throw new InvalidArgumentException("LEGO_AGENT_ACCESS must be off, read or write; got \"{$access}\".");
        }
    }

    public function access(): string
    {
        return $this->access;
    }

    public function enabled(): bool
    {
        return $this->access !== self::OFF;
    }

    public function allows(ActionDefinition $action): bool
    {
        return match ($this->access) {
            self::OFF => false,
            self::READ => $action->readOnly,
            self::WRITE => $action->readOnly || ($this->modules->get($action->module)?->agentWrites ?? false),
        };
    }
}
