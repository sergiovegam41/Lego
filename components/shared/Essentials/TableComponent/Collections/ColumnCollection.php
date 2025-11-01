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
     *
     * SISTEMA DE ANCHOS PORCENTUALES AUTOMÁTICOS:
     * Si hay columnas sin width definido, se calcula automáticamente
     * el porcentaje restante y se distribuye equitativamente.
     */
    public function toAgGridConfig(): array {
        $configs = array_map(
            fn(ColumnDto $column) => $column->toArray(),
            $this->columns
        );

        // Normalizar anchos porcentuales si es necesario
        return $this->normalizePercentageWidths($configs);
    }

    /**
     * Normaliza los anchos porcentuales para que sumen 100%
     *
     * Si hay columnas sin width, calcula el porcentaje restante
     * y lo distribuye equitativamente entre ellas.
     */
    private function normalizePercentageWidths(array $configs): array {
        $totalPercentage = 0;
        $columnsWithoutWidth = 0;

        // Calcular total de porcentajes definidos y contar columnas sin width
        foreach ($configs as $config) {
            if (isset($config['width'])) {
                // Si es un string terminado en '%', extraer el número
                if (is_string($config['width']) && str_ends_with($config['width'], '%')) {
                    $totalPercentage += (float)str_replace('%', '', $config['width']);
                }
            } else if (!isset($config['flex'])) {
                // No tiene width ni flex definido
                $columnsWithoutWidth++;
            }
        }

        // Si hay columnas sin width, calcular el porcentaje restante
        if ($columnsWithoutWidth > 0) {
            $remainingPercentage = max(0, 100 - $totalPercentage);
            $percentagePerColumn = $remainingPercentage / $columnsWithoutWidth;

            // Asignar porcentaje a columnas sin width
            foreach ($configs as &$config) {
                if (!isset($config['width']) && !isset($config['flex'])) {
                    $config['width'] = number_format($percentagePerColumn, 2, '.', '') . '%';
                }
            }
        }

        return $configs;
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
