<?php
namespace Components\App\Testimonials\Childs\TestimonialCreate;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;

#[ApiComponent('/testimonials/create', methods: ['GET'])]
class TestimonialCreateComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./testimonial-form.css"];
    protected $JS_PATHS = ["./testimonial-create.js"];

    protected function component(): string
    {
        $authorInput = new InputTextComponent(
            id: "testimonial-author",
            label: "Nombre del Autor",
            placeholder: "Ej: Juan Pérez",
            required: true
        );

        $messageTextarea = new TextAreaComponent(
            id: "testimonial-message",
            label: "Testimonio",
            placeholder: "Escribe el testimonio aquí...",
            rows: 6,
            required: true
        );

        return <<<HTML
        <div class="testimonial-form">
            <div class="testimonial-form__header">
                <h1 class="testimonial-form__title">Nuevo Testimonio</h1>
            </div>

            <form class="testimonial-form__form" id="testimonial-create-form">
                <div class="testimonial-form__grid">
                    <div class="testimonial-form__field testimonial-form__field--full">
                        {$authorInput->render()}
                    </div>

                    <div class="testimonial-form__field testimonial-form__field--full">
                        {$messageTextarea->render()}
                    </div>
                </div>

                <div class="testimonial-form__actions">
                    <button type="button" class="testimonial-form__button testimonial-form__button--secondary" id="testimonial-form-cancel-btn">
                        Cancelar
                    </button>
                    <button type="submit" class="testimonial-form__button testimonial-form__button--primary" id="testimonial-form-submit-btn">
                        Crear Testimonio
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
