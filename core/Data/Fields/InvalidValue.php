<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

use InvalidArgumentException;

/** Raised by a field when a raw value cannot be converted to its type. */
final class InvalidValue extends InvalidArgumentException
{
}
