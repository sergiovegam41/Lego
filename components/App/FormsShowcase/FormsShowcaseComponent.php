<?php
namespace Components\App\FormsShowcase;

use Core\Attributes\ApiComponent;
use Core\Components\CoreComponent\CoreComponent;

// ✨ Barrel Imports - Componentes de formulario
use Components\Shared\Forms\Forms\{
    Form,
    FormGroup,
    FormActions,
    InputText,
    TextArea,
    Select,
    Checkbox,
    Radio,
    Button
};

// ✨ Barrel Imports - Componentes esenciales
use Components\Shared\Essentials\Essentials\{
    Column,
    Row,
    Div,
    Grid,
    Fragment
};

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
        return (new Form(
            id: "contact-form",
            title: "Contáctanos",
            description: "Completa este formulario y te responderemos pronto.",
            children: [
                // ✨ Layout: Row con gap para nombre y email lado a lado
                new Row(
                    gap: "1rem",
                    children: [
                        new InputText(
                            id: "contact-name",
                            label: "Nombre completo",
                            placeholder: "Juan Pérez",
                            required: true,
                            icon: "person-outline"
                        ),

                        new InputText(
                            id: "contact-email",
                            label: "Correo electrónico",
                            placeholder: "juan@ejemplo.com",
                            type: "email",
                            required: true,
                            icon: "mail-outline"
                        )
                    ]
                ),

                new Select(
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

                new TextArea(
                    id: "contact-message",
                    label: "Mensaje",
                    placeholder: "Escribe tu mensaje aquí...",
                    required: true,
                    maxLength: 500,
                    showCounter: true,
                    autoResize: true,
                    rows: 4
                ),

                new Checkbox(
                    id: "contact-subscribe",
                    label: "Suscribirme al newsletter",
                    description: "Recibe noticias y actualizaciones por correo"
                ),

                new FormActions(
                    layout: "end",
                    children: [
                        new Button(
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
        return (new Form(
            id: "register-form",
            title: "Registro de Usuario",
            description: "Crea tu cuenta para acceder a todas las funcionalidades.",
            children: [
                // ✨ Layout: Column con gap para organizar secciones verticalmente
                new Column(
                    gap: "1.5rem",
                    children: [
                        // ✨ Layout: Row para credenciales lado a lado
                        new Row(
                            gap: "1rem",
                            children: [
                                new InputText(
                                    id: "reg-username",
                                    label: "Nombre de usuario",
                                    placeholder: "usuario123",
                                    required: true,
                                    minLength: 3,
                                    maxLength: 20,
                                    showCounter: true,
                                    helpText: "Entre 3 y 20 caracteres"
                                ),

                                new InputText(
                                    id: "reg-password",
                                    label: "Contraseña",
                                    type: "password",
                                    required: true,
                                    minLength: 8,
                                    helpText: "Mínimo 8 caracteres"
                                )
                            ]
                        ),

                        new Select(
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

                        new Div(
                            padding: "1rem",
                            borderRadius: "0.5rem",
                            border: "1px solid var(--border-light)",
                            
                            children: [
                                new FormGroup(
                                    title: "Género",
                                    children: [
                                        new Row(
                                            gap: "1.5rem",
                                            wrap: "wrap",
                                            children: [
                                                new Radio(
                                                    id: "gender-male",
                                                    name: "gender",
                                                    label: "Masculino",
                                                    value: "male"
                                                ),
                                                new Radio(
                                                    id: "gender-female",
                                                    name: "gender",
                                                    label: "Femenino",
                                                    value: "female"
                                                ),
                                                new Radio(
                                                    id: "gender-other",
                                                    name: "gender",
                                                    label: "Otro",
                                                    value: "other"
                                                )
                                            ]
                                        )
                                    ]
                                )
                            ]
                        ),

                        // ✨ DEMO: Componentes condicionales con null/false (se filtran automáticamente)
                        // Si esta condición es false, estos componentes NO se renderizan
                        false ? new InputText(
                            id: "hidden-field",
                            label: "Este campo no se muestra"
                        ) : null,

                        // ✨ DEMO: Fragment para agrupar múltiples elementos sin wrapper
                        // Los checkboxes se renderizan directamente, sin div adicional
                        new Fragment(children: [
                            new Checkbox(
                                id: "reg-terms",
                                label: "Acepto los términos y condiciones",
                                required: true
                            ),
                            new Checkbox(
                                id: "reg-newsletter",
                                label: "Quiero recibir el newsletter"
                            )
                        ])
                    ]
                ),

                new FormActions(
                    layout: "between",
                    children: [
                        new Button(
                            text: "Cancelar",
                            type: "button",
                            variant: "ghost",
                            fullWidth: true
                        ),
                        new Button(
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
        // ✨ Layout: Grid responsivo con auto-fill para cards de componentes
        return (new Grid(
            columns: "repeat(auto-fill, minmax(300px, 1fr))",
            gap: "2rem",
            children: [
                // Card 1: Input Text
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: [
                                        "<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>Input Text</h3>"
                                    ]
                                ),
                                new InputText(
                                    id: "showcase-input-1",
                                    label: "Input básico",
                                    placeholder: "Escribe algo..."
                                ),
                                new InputText(
                                    id: "showcase-input-2",
                                    label: "Input con icono y contador",
                                    placeholder: "usuario",
                                    icon: "person-outline",
                                    maxLength: 20,
                                    showCounter: true
                                ),
                                new InputText(
                                    id: "showcase-input-3",
                                    label: "Input con error",
                                    value: "valor inválido",
                                    errorMessage: "Este campo contiene errores"
                                )
                            ]
                        )
                    ]
                ),

                // Card 2: TextArea
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
        
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: ["<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>TextArea</h3>"]
                                ),
                                new TextArea(
                                    id: "showcase-textarea-1",
                                    label: "TextArea básico",
                                    placeholder: "Escribe un texto largo..."
                                ),
                                new TextArea(
                                    id: "showcase-textarea-2",
                                    label: "TextArea con auto-resize y contador",
                                    placeholder: "Se expande automáticamente...",
                                    maxLength: 200,
                                    showCounter: true,
                                    autoResize: true
                                )
                            ]
                        )
                    ]
                ),

                // Card 3: Select
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: ["<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>Select</h3>"]
                                ),
                                new Select(
                                    id: "showcase-select-1",
                                    label: "Select básico",
                                    options: [
                                        ["value" => "1", "label" => "Opción 1"],
                                        ["value" => "2", "label" => "Opción 2"],
                                        ["value" => "3", "label" => "Opción 3"]
                                    ]
                                ),
                                new Select(
                                    id: "showcase-select-2",
                                    label: "Select con búsqueda",
                                    searchable: true,
                                    options: [
                                        ["value" => "apple", "label" => "Manzana"],
                                        ["value" => "banana", "label" => "Plátano"],
                                        ["value" => "orange", "label" => "Naranja"],
                                        ["value" => "grape", "label" => "Uva"]
                                    ]
                                )
                            ]
                        )
                    ]
                ),

                // Card 4: Checkbox
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: ["<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>Checkbox</h3>"]
                                ),
                                new Checkbox(
                                    id: "showcase-checkbox-1",
                                    label: "Checkbox simple"
                                ),
                                new Checkbox(
                                    id: "showcase-checkbox-2",
                                    label: "Checkbox con descripción",
                                    description: "Esta es una descripción detallada del checkbox",
                                    checked: true
                                )
                            ]
                        )
                    ]
                ),

                // Card 5: Radio
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: ["<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>Radio</h3>"]
                                ),
                                new Radio(
                                    id: "showcase-radio-1",
                                    name: "showcase-radio-group",
                                    label: "Opción A",
                                    value: "a",
                                    checked: true
                                ),
                                new Radio(
                                    id: "showcase-radio-2",
                                    name: "showcase-radio-group",
                                    label: "Opción B",
                                    value: "b"
                                )
                            ]
                        )
                    ]
                ),

                // Card 6: Buttons
                new Div(
                    className: "component-card",
                    padding: "1.5rem",
                    borderRadius: "0.75rem",
                    border: "0.5px solid var(--border-light)",
                    children: [
                        new Column(
                            gap: "1rem",
                            children: [
                                new Div(
                                    children: ["<h3 style='margin: 0; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-light);'>Buttons</h3>"]
                                ),
                                // ✨ Layout: Row con wrap para botones
                                new Row(
                                    gap: "0.5rem",
                                    wrap: "wrap",
                                    children: [
                                        new Button(text: "Primary", variant: "primary"),
                                        new Button(text: "Secondary", variant: "secondary"),
                                        new Button(text: "Success", variant: "success", icon: "checkmark-outline"),
                                        new Button(text: "Danger", variant: "danger", icon: "trash-outline"),
                                        new Button(text: "Ghost", variant: "ghost")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ]
        ))->render();
    }
}
