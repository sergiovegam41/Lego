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
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';

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
     * Stores a pending confirmation and returns its single-use token.
     *
     * @param array<string, mixed> $input Validated input; only its hash binds the token.
     */
    public function requestConfirmation(
        string $action,
        string $via,
        int $userId,
        ?string $subject,
        array $input,
        int $ttlSeconds,
        ?string $traceId = null,
        ?string $ip = null,
    ): string {
        $token = bin2hex(random_bytes(32));
        $now = $this->clock->now();

        $this->db->connection()->table('action_log')->insert([
            'occurred_at' => $now->format(DATE_ATOM),
            'user_id' => $userId,
            'action' => $action,
            'via' => $via,
            'status' => self::PENDING,
            'subject' => $subject,
            'input' => json_encode(self::redact($input), JSON_UNESCAPED_UNICODE),
            'trace_id' => $traceId,
            'ip' => $ip,
            'confirmation_hash' => hash('sha256', $token),
            'input_hash' => self::hashInput($input),
            'expires_at' => $now->modify("+{$ttlSeconds} seconds")->format(DATE_ATOM),
        ]);

        return $token;
    }

    /**
     * Consumes a pending confirmation atomically. True only once, and only for
     * the same user, action and input, before it expires.
     *
     * @param array<string, mixed> $input
     */
    public function consumeConfirmation(string $token, string $action, int $userId, array $input): bool
    {
        $updated = $this->db->connection()->table('action_log')
            ->where('confirmation_hash', hash('sha256', $token))
            ->where('status', self::PENDING)
            ->where('action', $action)
            ->where('user_id', $userId)
            ->where('input_hash', self::hashInput($input))
            ->where('expires_at', '>', $this->clock->now()->format(DATE_ATOM))
            ->update(['status' => self::CONFIRMED]);

        return $updated === 1;
    }

    /** @param array<string, mixed> $input */
    public static function hashInput(array $input): string
    {
        ksort($input);

        return hash('sha256', json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION) ?: '');
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
