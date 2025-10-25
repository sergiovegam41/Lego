<?php

namespace Components\Core\Home\Collections;

use Components\Core\Home\Dtos\MenuItemDto;
use InvalidArgumentException;

/**
 * MenuItemCollection - Colección tipada de MenuItemDto
 *
 * PROPÓSITO:
 * Garantiza que solo se puedan agregar MenuItemDto a la colección.
 * Proporciona type-safety estilo Laravel para el sistema de menús.
 *
 * CARACTERÍSTICAS:
 * - Validación en constructor: Solo acepta MenuItemDto
 * - Iterable: Puedes usar foreach directamente
 * - Countable: Puedes usar count($collection)
 * - IDE-friendly: Autocomplete y type hints
 *
 * EJEMPLO:
 * $items = new MenuItemCollection(
 *     new MenuItemDto(id: "1", name: "Home", url: "/", iconName: "home"),
 *     new MenuItemDto(id: "2", name: "About", url: "/about", iconName: "info")
 * );
 *
 * foreach ($items as $item) {
 *     echo $item->name; // Type-safe: IDE sabe que $item es MenuItemDto
 * }
 */
class MenuItemCollection implements \IteratorAggregate, \Countable
{
    /** @var MenuItemDto[] */
    private array $items;

    /**
     * Constructor con validación tipada
     *
     * @param MenuItemDto ...$items Lista de items del menú
     */
    public function __construct(MenuItemDto ...$items)
    {
        $this->items = $items;
    }

    /**
     * Permite iterar sobre la colección con foreach
     *
     * @return \Traversable<MenuItemDto>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Retorna el número de items en la colección
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Convierte la colección a array
     *
     * @return MenuItemDto[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Verifica si la colección está vacía
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Retorna el primer item de la colección
     *
     * @return MenuItemDto|null
     */
    public function first(): ?MenuItemDto
    {
        return $this->items[0] ?? null;
    }

    /**
     * Filtra la colección usando un callable
     *
     * @param callable $callback
     * @return MenuItemCollection
     */
    public function filter(callable $callback): MenuItemCollection
    {
        return new MenuItemCollection(...array_filter($this->items, $callback));
    }
}
