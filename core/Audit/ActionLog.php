<?php

declare(strict_types=1);

namespace Lego\Audit;

use Lego\Database\Database;
use Lego\Support\Clock;

/**
 * Writes to the append-only action_log table.
 *
 * Sensitive keys (password, token, secret...) are replaced before storing the
 * input, at any depth.
 */
final class ActionLog
{
    public const VIA_UI = 'ui';
    public const VIA_API = 'api';
    public const VIA_CLI = 'cli';
    public const VIA_AGENT = 'agent';

    public const OK = 'ok';
    public const DENIED = 'denied';
    public const ERROR = 'error';

    private const SENSITIVE = '/pass|token|secret|authorization|cookie|csrf/i';

    public function __construct(private readonly Database $db, private readonly Clock $clock)
    {
    }

    /** @param array<string, mixed>|null $input */
    public function record(
        string $action,
        string $via,
        string $status,
        ?int $userId = null,
        ?string $subject = null,
        ?array $input = null,
        ?string $traceId = null,
        ?string $ip = null,
    ): void {
        $this->db->connection()->table('action_log')->insert([
            'occurred_at' => $this->clock->now()->format(DATE_ATOM),
            'user_id' => $userId,
            'action' => $action,
            'via' => $via,
            'status' => $status,
            'subject' => $subject,
            'input' => $input === null ? null : json_encode(self::redact($input), JSON_UNESCAPED_UNICODE),
            'trace_id' => $traceId,
            'ip' => $ip,
        ]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function redact(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE, $key) === 1) {
                $input[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $input[$key] = self::redact($value);
            }
        }

        return $input;
    }
}
