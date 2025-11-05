<?php
namespace Components\Shared\FragmentComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FragmentComponent - Contenedor invisible para agrupar children sin wrapper HTML
 *
 * FILOSOFÍA LEGO:
 * Similar a React.Fragment o <> en JSX. Permite agrupar múltiples componentes
 * sin agregar un elemento div/span adicional al DOM.
 *
 * CASOS DE USO:
 * 1. Retornar múltiples elementos sin wrapper:
 *    - Listas de items sin contenedor adicional
 *    - Componentes condicionales múltiples
 *    - Composición sin jerarquía extra
 *
 * 2. Organizar código sin afectar HTML:
 *    - Agrupar lógicamente componentes relacionados
 *    - Mantener limpio el árbol de children
 *
 * 3. Loops y mappings:
 *    - Renderizar arrays sin wrappers
 *
 * EJEMPLO 1 - Múltiples elementos:
 * new FragmentComponent(children: [
 *     new TitleComponent(text: "Título"),
 *     new ParagraphComponent(text: "Descripción"),
 *     new ButtonComponent(text: "Acción")
 * ])
 * // Renderiza: <h1>...</h1><p>...</p><button>...</button>
 * // Sin wrapper div/span
 *
 * EJEMPLO 2 - En formularios:
 * children: [
 *     new InputTextComponent(...),
 *     $showOptional ? new FragmentComponent(children: [
 *         new TextAreaComponent(...),
 *         new CheckboxComponent(...)
 *     ]) : null,
 *     new ButtonComponent(...)
 * ]
 *
 * EJEMPLO 3 - Loop sin wrapper:
 * new FragmentComponent(children: [
 *     ...array_map(
 *         fn($item) => new ItemComponent(data: $item),
 *         $items
 *     )
 * ])
 *
 * NOTA:
 * FragmentComponent NO genera ningún HTML propio, solo renderiza sus children.
 * Es completamente transparente en el DOM resultante.
 */
class FragmentComponent extends CoreComponent {

    public function __construct(
        public array $children = []
    ) {}

    /**
     * Fragment no renderiza ningún wrapper, solo sus children
     */
    protected function component(): string {
        return $this->renderChildren();
    }
}
