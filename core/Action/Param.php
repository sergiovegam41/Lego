<?php

declare(strict_types=1);

namespace Lego\Action;

use Attribute;

/** Optional label and description for a parameter of an #[Action] method. */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Param
{
    public function __construct(public string $label, public string $description = '')
    {
    }
}
