<?php

namespace Components\Core\Home\Dtos;

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

    /**
     * Convierte el MenuItemDto a array para serialización
     * Incluye conversión recursiva de children
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'iconName' => $this->iconName,
            'level' => $this->level,
            'childs' => array_map(fn($child) => $child->toArray(), $this->childs)
        ];
    }
}