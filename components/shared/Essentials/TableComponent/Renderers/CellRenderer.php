<?php
namespace Components\Shared\Essentials\TableComponent\Renderers;

/**
 * CellRenderer - Clase base para todos los renderers de celdas
 *
 * FILOSOFÍA LEGO:
 * Abstrae la creación de cell renderers para AG Grid, permitiendo
 * configuración declarativa desde PHP en lugar de JavaScript manual.
 *
 * PROBLEMA QUE RESUELVE:
 * - Elimina ~75 líneas de JavaScript repetitivo por CRUD
 * - Configuración type-safe desde PHP
 * - Reutilización de renderers comunes
 * - Manejo consistente de temas
 *
 * ARQUITECTURA:
 * Los renderers generan funciones JavaScript que AG Grid ejecuta.
 * Cada renderer devuelve HTML que se inyecta en las celdas.
 *
 * EJEMPLO DE USO:
 * ```php
 * new ColumnDto(
 *     field: "actions",
 *     headerName: "Acciones",
 *     cellRenderer: ActionButtonsRenderer::create(
 *         editFunction: 'editProduct',
 *         deleteFunction: 'deleteProduct'
 *     )
 * )
 * ```
 */
abstract class CellRenderer
{
    /**
     * Genera el código JavaScript del cell renderer
     *
     * @return string Código JavaScript que AG Grid ejecutará
     */
    abstract public function toJavaScript(): string;

    /**
     * Convierte el renderer a string (para inyección en ColumnDto)
     */
    public function __toString(): string
    {
        return $this->toJavaScript();
    }

    /**
     * Escapa comillas para JavaScript
     */
    protected function escapeJs(string $value): string
    {
        return str_replace(["'", '"'], ["\\'", '\\"'], $value);
    }

    /**
     * Genera código JavaScript para detectar tema actual
     *
     * @return string Código JS que retorna 'dark' o 'light'
     */
    protected function getThemeDetectionCode(): string
    {
        return "(document.documentElement.classList.contains('dark') ? 'dark' : 'light')";
    }

    /**
     * Genera código JavaScript para clases responsive a tema
     *
     * @param string $lightClasses Clases para tema claro
     * @param string $darkClasses Clases para tema oscuro
     * @return string Código JS que retorna las clases apropiadas
     */
    protected function getThemeAwareClasses(string $lightClasses, string $darkClasses): string
    {
        $light = $this->escapeJs($lightClasses);
        $dark = $this->escapeJs($darkClasses);
        return "({$this->getThemeDetectionCode()} === 'dark' ? '{$dark}' : '{$light}')";
    }
}
