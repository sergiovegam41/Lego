<?php
namespace Components\Shared\Essentials\TableComponent\Collections;

use Components\Shared\Essentials\TableComponent\Dtos\RowActionDto;

/**
 * RowActionsCollection - Colección inmutable de acciones de fila
 *
 * FILOSOFÍA LEGO:
 * Composición declarativa de acciones. Type-safe y fácil de usar.
 *
 * EJEMPLO:
 * $actions = new RowActionsCollection(
 *     new RowActionDto(id: "edit", label: "Editar", icon: "create-outline", callback: "handleEdit"),
 *     new RowActionDto(id: "delete", label: "Eliminar", icon: "trash-outline", callback: "handleDelete", confirm: true)
 * );
 */
class RowActionsCollection {
    /** @var RowActionDto[] */
    private array $actions;

    public function __construct(RowActionDto ...$actions) {
        $this->actions = $actions;
    }

    /**
     * Obtiene todas las acciones
     * @return RowActionDto[]
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * Convierte la colección a array para AG Grid
     */
    public function toArray(): array {
        return array_map(fn($action) => $action->toArray(), $this->actions);
    }

    /**
     * Obtiene una acción por ID
     */
    public function getById(string $id): ?RowActionDto {
        foreach ($this->actions as $action) {
            if ($action->id === $id) {
                return $action;
            }
        }
        return null;
    }

    /**
     * Verifica si la colección está vacía
     */
    public function isEmpty(): bool {
        return empty($this->actions);
    }

    /**
     * Cuenta las acciones
     */
    public function count(): int {
        return count($this->actions);
    }
}
