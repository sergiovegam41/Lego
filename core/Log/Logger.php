<?php

declare(strict_types=1);

namespace Lego\Log;

use Throwable;

/**
 * Writes one JSON line per event to a stream, stderr by default.
 *
 * Docker and Dokploy collect stderr, so the application never manages log files.
 */
final class Logger
{
    /** @var resource|null */
    private $stream;

    /** @param resource|null $stream */
    public function __construct($stream = null, private readonly string $target = 'php://stderr')
    {
        $this->stream = $stream;
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function exception(Throwable $e, array $context = []): void
    {
        $this->error($e->getMessage(), [
            ...$context,
            'exception' => $e::class,
            'file' => $e->getFile() . ':' . $e->getLine(),
        ]);
    }

    /** @param array<string, mixed> $context */
    private function write(string $level, string $message, array $context): void
    {
        $this->stream ??= fopen($this->target, 'ab') ?: null;
        if ($this->stream === null) {
            return;
        }

        $line = json_encode(
            ['time' => date(DATE_ATOM), 'level' => $level, 'message' => $message, ...$context],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR,
        );
        fwrite($this->stream, $line . PHP_EOL);
    }
}
