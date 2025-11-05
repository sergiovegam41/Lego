<?php
namespace Components\App\Testimonials\Childs\TestimonialEdit;

use Core\Components\CoreComponent\CoreComponent;
use Core\Attributes\ApiComponent;
use Components\Shared\Forms\InputTextComponent\InputTextComponent;
use Components\Shared\Forms\TextAreaComponent\TextAreaComponent;

#[ApiComponent('/testimonials/edit', methods: ['GET'])]
class TestimonialEditComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./testimonial-form.css"];
    protected $JS_PATHS = ["./testimonial-edit.js"];

    public function __construct(array $params = [])
    {
        $id = $params['id'] ?? $_GET['id'] ?? $_REQUEST['id'] ?? null;
        if ($id !== null) {
            $this->testimonialId = is_numeric($id) ? (int)$id : null;
        }
    }

    private ?int $testimonialId = null;

    protected function component(): string
    {
        $testimonialId = $this->testimonialId
            ?? (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

        if (!$testimonialId) {
            return <<<HTML
            <div class="testimonial-form">
                <div class="testimonial-form__error">
                    <h2>Error</h2>
                    <p>ID de testimonio no especificado</p>
                </div>
            </div>
            HTML;
        }

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
        <div class="testimonial-form" data-testimonial-id="{$testimonialId}">
            <div class="testimonial-form__header">
                <h1 class="testimonial-form__title">Editar Testimonio</h1>
            </div>

            <div class="testimonial-form__loading" id="testimonial-form-loading">
                Cargando testimonio...
            </div>

            <form class="testimonial-form__form" id="testimonial-edit-form" style="display: none;">
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
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        HTML;
    }
}
