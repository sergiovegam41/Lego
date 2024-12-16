<?php

namespace Views\Pages\Home\Dtos;

readonly class MenuItemDto
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
    ) {
    }
}