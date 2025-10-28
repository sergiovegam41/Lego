<?php
namespace Components\Shared\Essentials\TableComponent\Collections;

use Components\Shared\Essentials\TableComponent\Dtos\ColumnDto;

/**
 * ColumnCollection - Colección tipo-safe de ColumnDto
 *
 * FILOSOFÍA LEGO:
 * Collection inmutable que solo acepta objetos ColumnDto,
 * garantizando type-safety en runtime.
 *
 * EJEMPLO:
 * new ColumnCollection(
 *     new ColumnDto(field: "id", headerName: "ID", width: 80),
 *     new ColumnDto(field: "name", headerName: "Nombre", sortable: true, filter: true),
 *     new ColumnDto(field: "email", headerName: "Email", filter: true)
 * )
 */
class ColumnCollection implements \IteratorAggregate, \Countable {

    /** @var ColumnDto[] */
    private array $columns;

    public function __construct(ColumnDto ...$columns) {
        $this->columns = $columns;
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->columns);
    }

    public function count(): int {
        return count($this->columns);
    }

    public function toArray(): array {
        return $this->columns;
    }

    public function isEmpty(): bool {
        return empty($this->columns);
    }

    /**
     * Convierte todas las columnas a configuración de AG Grid
     */
    public function toAgGridConfig(): array {
        return array_map(
            fn(ColumnDto $column) => $column->toArray(),
            $this->columns
        );
    }

    /**
     * Obtiene una columna por su field
     */
    public function findByField(string $field): ?ColumnDto {
        foreach ($this->columns as $column) {
            if ($column->field === $field) {
                return $column;
            }
        }
        return null;
    }

    /**
     * Filtra columnas visibles
     */
    public function getVisibleColumns(): array {
        return array_filter(
            $this->columns,
            fn(ColumnDto $column) => !$column->hide
        );
    }
}
