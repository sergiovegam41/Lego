<?php
namespace Components\App\FormsShowcase;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\SelectComponent\SelectComponent;
use Components\Shared\Forms\ButtonComponent\ButtonComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;
use Components\Shared\Forms\CheckboxComponent\CheckboxComponent;
use Components\Shared\Forms\RadioComponent\RadioComponent;
use Components\Shared\Forms\FormComponent\FormComponent;
use Components\Shared\Forms\FormGroupComponent\FormGroupComponent;
use Components\Shared\Forms\FormActionsComponent\FormActionsComponent;
use Components\Shared\Forms\FormRowComponent\FormRowComponent;

/**
 * FormsShowcaseComponent - Página de demostración de componentes de formulario
 *
 * FILOSOFÍA LEGO:
 * Página completa que demuestra todos los componentes de formulario
 * disponibles en Lego, con ejemplos de uso y casos comunes.
 */
#[ApiComponent('/forms-showcase', methods: ['GET'])]
class FormsShowcaseComponent extends CoreComponent {

    protected $CSS_PATHS = ["./forms-showcase.css"];
    protected $JS_PATHS = ["./forms-showcase.js"];

    public function __construct() {}

    protected function component(): string {
        // Ejemplo de formulario de contacto
        $contactFormContent = $this->renderContactForm();

        // Ejemplo de formulario de registro
        $registerFormContent = $this->renderRegisterForm();

        // Ejemplos individuales de componentes
        $componentsShowcase = $this->renderComponentsShowcase();

        return <<<HTML
        <div class="forms-showcase">
            <div class="forms-showcase__header">
                <h1 class="forms-showcase__title">Componentes de Formulario Lego</h1>
                
            </div>

            <div class="forms-showcase__content">
                <!-- Formulario de Contacto -->
                <section class="forms-showcase__section">
                    <h2 class="forms-showcase__section-title">Formulario de Contacto</h2>
                    <p class="forms-showcase__section-description">
                        Ejemplo completo de un formulario funcional con validación.
                    </p>
                    {$contactFormContent}
                </section>

                <!-- Formulario de Registro -->
                <section class="forms-showcase__section">
                    <h2 class="forms-showcase__section-title">Formulario de Registro</h2>
                    <p class="forms-showcase__section-description">
                        Formulario con múltiples tipos de campos y validación avanzada.
                    </p>
                    {$registerFormContent}
                </section>

                <!-- Showcase de Componentes -->
                <section class="forms-showcase__section">
                    <h2 class="forms-showcase__section-title">Todos los Componentes</h2>
                    <p class="forms-showcase__section-description">
                        Ejemplos individuales de cada componente con diferentes variantes y estados.
                    </p>
                    {$componentsShowcase}
                </section>
            </div>
        </div>
        HTML;
    }

    private function renderContactForm(): string {
        return (new FormComponent(
            id: "contact-form",
            title: "Contáctanos",
            description: "Completa este formulario y te responderemos pronto.",
            children: [
                new InputTextComponent(
                    id: "contact-name",
                    label: "Nombre completo",
                    placeholder: "Juan Pérez",
                    required: true,
                    icon: "person-outline"
                ),

                new InputTextComponent(
                    id: "contact-email",
                    label: "Correo electrónico",
                    placeholder: "juan@ejemplo.com",
                    type: "email",
                    required: true,
                    icon: "mail-outline"
                ),

                new SelectComponent(
                    id: "contact-subject",
                    label: "Asunto",
                    placeholder: "Selecciona un asunto",
                    required: true,
                    options: [
                        ["value" => "general", "label" => "Consulta general"],
                        ["value" => "support", "label" => "Soporte técnico"],
                        ["value" => "sales", "label" => "Ventas"],
                        ["value" => "other", "label" => "Otro"]
                    ]
                ),

                new TextAreaComponent(
                    id: "contact-message",
                    label: "Mensaje",
                    placeholder: "Escribe tu mensaje aquí...",
                    required: true,
                    maxLength: 500,
                    showCounter: true,
                    autoResize: true,
                    rows: 4
                ),

                new CheckboxComponent(
                    id: "contact-subscribe",
                    label: "Suscribirme al newsletter",
                    description: "Recibe noticias y actualizaciones por correo"
                ),

                new FormActionsComponent(
                    layout: "end",
                    children: [
                        new ButtonComponent(
                            text: "Enviar mensaje",
                            type: "submit",
                            variant: "primary",
                            icon: "send-outline",
                            fullWidth: true
                        )
                    ]
                )
            ]
        ))->render();
    }

    private function renderRegisterForm(): string {
        return (new FormComponent(
            id: "register-form",
            title: "Registro de Usuario",
            description: "Crea tu cuenta para acceder a todas las funcionalidades.",
            children: [
                new InputTextComponent(
                    id: "reg-username",
                    label: "Nombre de usuario",
                    placeholder: "usuario123",
                    required: true,
                    minLength: 3,
                    maxLength: 20,
                    showCounter: true,
                    helpText: "Entre 3 y 20 caracteres"
                ),

                new InputTextComponent(
                    id: "reg-password",
                    label: "Contraseña",
                    type: "password",
                    required: true,
                    minLength: 8,
                    helpText: "Mínimo 8 caracteres"
                ),

                new SelectComponent(
                    id: "reg-country",
                    label: "País",
                    required: true,
                    searchable: true,
                    options: [
                        ["value" => "mx", "label" => "México"],
                        ["value" => "us", "label" => "Estados Unidos"],
                        ["value" => "es", "label" => "España"],
                        ["value" => "ar", "label" => "Argentina"],
                        ["value" => "co", "label" => "Colombia"]
                    ]
                ),

                new FormGroupComponent(
                    title: "Género",
                    children: [
                        new RadioComponent(
                            id: "gender-male",
                            name: "gender",
                            label: "Masculino",
                            value: "male"
                        ),
                        new RadioComponent(
                            id: "gender-female",
                            name: "gender",
                            label: "Femenino",
                            value: "female"
                        ),
                        new RadioComponent(
                            id: "gender-other",
                            name: "gender",
                            label: "Otro",
                            value: "other"
                        )
                    ]
                ),

                new CheckboxComponent(
                    id: "reg-terms",
                    label: "Acepto los términos y condiciones",
                    required: true
                ),

                new FormActionsComponent(
                    layout: "between",
                    children: [
                        new ButtonComponent(
                            text: "Cancelar",
                            type: "button",
                            variant: "ghost",
                            fullWidth: true
                        ),
                        new ButtonComponent(
                            text: "Crear cuenta",
                            type: "submit",
                            variant: "success",
                            icon: "checkmark-circle-outline",
                            fullWidth: true
                        )
                    ]
                )
            ]
        ))->render();
    }

    private function renderComponentsShowcase(): string {
        // Inputs
        $input1 = new InputTextComponent(
            id: "showcase-input-1",
            label: "Input básico",
            placeholder: "Escribe algo..."
        );

        $input2 = new InputTextComponent(
            id: "showcase-input-2",
            label: "Input con icono y contador",
            placeholder: "usuario",
            icon: "person-outline",
            maxLength: 20,
            showCounter: true
        );

        $input3 = new InputTextComponent(
            id: "showcase-input-3",
            label: "Input con error",
            value: "valor inválido",
            errorMessage: "Este campo contiene errores"
        );

        // TextAreas
        $textarea1 = new TextAreaComponent(
            id: "showcase-textarea-1",
            label: "TextArea básico",
            placeholder: "Escribe un texto largo..."
        );

        $textarea2 = new TextAreaComponent(
            id: "showcase-textarea-2",
            label: "TextArea con auto-resize y contador",
            placeholder: "Se expande automáticamente...",
            maxLength: 200,
            showCounter: true,
            autoResize: true
        );

        // Selects
        $select1 = new SelectComponent(
            id: "showcase-select-1",
            label: "Select básico",
            options: [
                ["value" => "1", "label" => "Opción 1"],
                ["value" => "2", "label" => "Opción 2"],
                ["value" => "3", "label" => "Opción 3"]
            ]
        );

        $select2 = new SelectComponent(
            id: "showcase-select-2",
            label: "Select con búsqueda",
            searchable: true,
            options: [
                ["value" => "apple", "label" => "Manzana"],
                ["value" => "banana", "label" => "Plátano"],
                ["value" => "orange", "label" => "Naranja"],
                ["value" => "grape", "label" => "Uva"]
            ]
        );

        // Checkboxes
        $checkbox1 = new CheckboxComponent(
            id: "showcase-checkbox-1",
            label: "Checkbox simple"
        );

        $checkbox2 = new CheckboxComponent(
            id: "showcase-checkbox-2",
            label: "Checkbox con descripción",
            description: "Esta es una descripción detallada del checkbox",
            checked: true
        );

        // Radios
        $radio1 = new RadioComponent(
            id: "showcase-radio-1",
            name: "showcase-radio-group",
            label: "Opción A",
            value: "a",
            checked: true
        );

        $radio2 = new RadioComponent(
            id: "showcase-radio-2",
            name: "showcase-radio-group",
            label: "Opción B",
            value: "b"
        );

        // Buttons
        $btnPrimary = new ButtonComponent(text: "Primary", variant: "primary");
        $btnSecondary = new ButtonComponent(text: "Secondary", variant: "secondary");
        $btnSuccess = new ButtonComponent(text: "Success", variant: "success", icon: "checkmark-outline");
        $btnDanger = new ButtonComponent(text: "Danger", variant: "danger", icon: "trash-outline");
        $btnGhost = new ButtonComponent(text: "Ghost", variant: "ghost");

        return <<<HTML
        <div class="components-grid">
            <div class="component-card">
                <h3>Input Text</h3>
                {$input1->render()}
                {$input2->render()}
                {$input3->render()}
            </div>

            <div class="component-card">
                <h3>TextArea</h3>
                {$textarea1->render()}
                {$textarea2->render()}
            </div>

            <div class="component-card">
                <h3>Select</h3>
                {$select1->render()}
                {$select2->render()}
            </div>

            <div class="component-card">
                <h3>Checkbox</h3>
                {$checkbox1->render()}
                {$checkbox2->render()}
            </div>

            <div class="component-card">
                <h3>Radio</h3>
                {$radio1->render()}
                {$radio2->render()}
            </div>

            <div class="component-card">
                <h3>Buttons</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    {$btnPrimary->render()}
                    {$btnSecondary->render()}
                    {$btnSuccess->render()}
                    {$btnDanger->render()}
                    {$btnGhost->render()}
                </div>
            </div>
        </div>
        HTML;
    }
}
