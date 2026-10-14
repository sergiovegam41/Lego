<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Multi-line text without a default maximum length. */
final class TextArea extends Text
{
    protected ?int $maxLength = null;

    public function type(): string
    {
        return 'textarea';
    }
}
