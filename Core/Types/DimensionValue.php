<?php

namespace Core\Types;

/**
 * DimensionValue - Valor de dimensión type-safe con unidad explícita
 *
 * FILOSOFÍA:
 * "Las distancias importan más que los valores absolutos"
 *
 * Este tipo representa un valor con su unidad, asegurando que:
 * 1. No se mezclen unidades (80px vs 80%)
 * 2. Se validen rangos apropiados para cada unidad
 * 3. Se pueda convertir a diferentes formatos (AG Grid, CSS)
 *
 * CONSISTENCIA:
 * - Todos los anchos usan el mismo sistema
 * - Las conversiones mantienen las proporciones
 * - Los errores son claros y tempranos
 *
 * @package Core\Types
 */
readonly class DimensionValue
{
    /**
     * Constructor privado - usar métodos estáticos factory
     *
     * @param float $value Valor numérico
     * @param DimensionUnit $unit Unidad de medida
     * @param array $params Parámetros adicionales (para flex)
     */
    private function __construct(
        public float $value,
        public DimensionUnit $unit,
        public array $params = []
    ) {}

    /**
     * Crear dimensión en pixels
     *
     * @param float $value Valor en pixels (debe ser >= 0)
     * @return self
     * @throws \InvalidArgumentException Si el valor es negativo
     */
    public static function px(float $value): self
    {
        if ($value < 0) {
            throw new \InvalidArgumentException(
                "DimensionValue: El ancho en pixels no puede ser negativo. Recibido: {$value}px"
            );
        }

        return new self($value, DimensionUnit::PIXELS);
    }

    /**
     * Crear dimensión en porcentaje
     *
     * @param float $value Valor en porcentaje (0-100)
     * @return self
     * @throws \InvalidArgumentException Si está fuera del rango 0-100
     */
    public static function percent(float $value): self
    {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException(
                "DimensionValue: El porcentaje debe estar entre 0 y 100. Recibido: {$value}%"
            );
        }

        return new self($value, DimensionUnit::PERCENT);
    }

    /**
     * Crear dimensión flex (flexible)
     *
     * Flex permite que el elemento crezca/encoja proporcionalmente.
     *
     * @param float $grow Factor de crecimiento (típicamente 1, 2, 3...)
     * @param float $shrink Factor de encogimiento (típicamente 1)
     * @param string|int $basis Base inicial ('auto', '0', '100px', etc)
     * @return self
     * @throws \InvalidArgumentException Si grow es negativo
     */
    public static function flex(float $grow, float $shrink = 1, string|int $basis = 'auto'): self
    {
        if ($grow < 0) {
            throw new \InvalidArgumentException(
                "DimensionValue: El factor de crecimiento flex no puede ser negativo. Recibido: {$grow}"
            );
        }

        if ($shrink < 0) {
            throw new \InvalidArgumentException(
                "DimensionValue: El factor de encogimiento flex no puede ser negativo. Recibido: {$shrink}"
            );
        }

        return new self($grow, DimensionUnit::FLEX, [
            'grow' => $grow,
            'shrink' => $shrink,
            'basis' => $basis
        ]);
    }

    /**
     * Crear dimensión automática (basada en contenido)
     *
     * @return self
     */
    public static function auto(): self
    {
        return new self(0, DimensionUnit::AUTO);
    }

    /**
     * Convertir a configuración de AG Grid
     *
     * AG Grid acepta diferentes formatos según el tipo de dimensión.
     * Esta conversión mantiene las proporciones correctas.
     *
     * @return array Configuración para AG Grid
     */
    public function toAgGrid(): array
    {
        return match($this->unit) {
            DimensionUnit::PIXELS => [
                'width' => (int)$this->value
            ],

            DimensionUnit::PERCENT => [
                'width' => "{$this->value}%"
            ],

            DimensionUnit::AUTO => [
                'flex' => 0,
                'minWidth' => 100
            ],

            DimensionUnit::FLEX => [
                'flex' => (int)$this->params['grow']
            ]
        };
    }

    /**
     * Convertir a string CSS
     *
     * Útil para aplicar estilos directamente en CSS.
     *
     * @return string Valor CSS válido
     */
    public function toCss(): string
    {
        return match($this->unit) {
            DimensionUnit::PIXELS => "{$this->value}px",
            DimensionUnit::PERCENT => "{$this->value}%",
            DimensionUnit::AUTO => "auto",
            DimensionUnit::FLEX => "{$this->params['grow']} {$this->params['shrink']} {$this->params['basis']}"
        };
    }

    /**
     * Representación legible para debugging
     *
     * @return string
     */
    public function __toString(): string
    {
        return match($this->unit) {
            DimensionUnit::PIXELS => "{$this->value}px",
            DimensionUnit::PERCENT => "{$this->value}%",
            DimensionUnit::AUTO => "auto",
            DimensionUnit::FLEX => "flex({$this->params['grow']}, {$this->params['shrink']}, {$this->params['basis']})"
        };
    }
}

