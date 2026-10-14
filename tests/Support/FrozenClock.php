<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use DateTimeZone;
use Lego\Support\Clock;

/** Clock that only moves when told to. */
final class FrozenClock implements Clock
{
    private DateTimeImmutable $now;

    public function __construct(string $time = '2026-10-14 12:00:00')
    {
        $this->now = new DateTimeImmutable($time, new DateTimeZone('UTC'));
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function advance(string $modifier): void
    {
        $this->now = $this->now->modify($modifier);
    }
}
