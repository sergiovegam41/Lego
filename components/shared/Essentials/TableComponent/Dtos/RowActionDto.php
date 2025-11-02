<?php
namespace Components\Shared\Essentials\TableComponent\Dtos;

/**
 * RowActionDto - Define una acción personalizable para filas de tabla
 *
 * FILOSOFÍA LEGO:
 * Acciones configurables que ejecutan callbacks scoped al componente,
 * no funciones globales. Permite personalización total del comportamiento.
 *
 * EJEMPLO BÁSICO:
 * new RowActionDto(
 *     id: "edit",
 *     label: "Editar",
 *     icon: "create-outline",
 *     callback: "handleEdit"  // Función definida en el JS del componente
 * )
 *
 * EJEMPLO CON CONFIRMACIÓN:
 * new RowActionDto(
 *     id: "delete",
 *     label: "Eliminar",
 *     icon: "trash-outline",
 *     callback: "handleDelete",
 *     variant: "danger",
 *     confirm: true,
 *     confirmMessage: "¿Eliminar este registro?"
 * )
 *
 * EJEMPLO ACCIÓN CUSTOM:
 * new RowActionDto(
 *     id: "duplicate",
 *     label: "Duplicar",
 *     icon: "copy-outline",
 *     callback: "handleDuplicate",
 *     variant: "secondary"
 * )
 *
 * CALLBACKS SCOPED:
 * Los callbacks NO son funciones globales (window.callback).
 * Se buscan en el scope del módulo JS del componente.
 * Esto evita contaminación del namespace global.
 */
class RowActionDto {
    public function __construct(
        // Identificación
        public readonly string $id,                          // ID único (edit, delete, custom)
        public readonly string $label = "",                  // Texto del botón
        public readonly string $icon = "",                   // Icono ionicons

        // Comportamiento
        public readonly string $callback = "",               // Nombre de función JS (scoped)
        public readonly bool $confirm = false,               // Mostrar confirmación
        public readonly string $confirmMessage = "¿Confirmar esta acción?",

        // Estilos
        public readonly string $variant = "secondary",       // primary, secondary, danger, success, warning
        public readonly string $tooltip = "",                // Tooltip texto
        public readonly bool $showLabel = false,             // Mostrar label junto al icono

        // Condicionales
        public readonly string $visibleIf = "",              // JS expression: params => params.data.is_active
        public readonly string $disabledIf = ""              // JS expression: params => !params.data.can_edit
    ) {}

    /**
     * Convierte el DTO a array para JavaScript
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'label' => $this->label ?: ucfirst($this->id),
            'icon' => $this->icon,
            'callback' => $this->callback ?: 'handle' . ucfirst($this->id),
            'confirm' => $this->confirm,
            'confirmMessage' => $this->confirmMessage,
            'variant' => $this->variant,
            'tooltip' => $this->tooltip ?: $this->label,
            'showLabel' => $this->showLabel,
            'visibleIf' => $this->visibleIf,
            'disabledIf' => $this->disabledIf
        ];
    }
}
