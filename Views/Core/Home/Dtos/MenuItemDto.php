<?php

namespace Views\Core\Home\Dtos;

class MenuItemDto
{
    /**
     * @param MenuItemDto[] $childs
     */
    public function __construct(
        public string $id,
        public string $name,
        public string|null $url,
        public string $iconName,
        public array $childs = [],
        public int $level = 0,
    ) {
    }
}