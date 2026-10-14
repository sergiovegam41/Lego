<?php

namespace Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiComponent
{
    public function __construct(
        public string $path,
        public array $methods = ['GET'],
        public bool $requiresAuth = true
    ) {}
}