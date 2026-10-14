<?php

declare(strict_types=1);

namespace Lego\Support;

use DateTimeImmutable;

/** Source of the current time, injectable so expiry rules can be tested. */
interface Clock
{
    public function now(): DateTimeImmutable;
}
