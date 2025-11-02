<?php

namespace Components\Shared\Forms\FilePondComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * FilePondComponent - Upload de archivos con FilePond
 *
 * FILOSOFÍA LEGO:
 * Componente reutilizable para upload de imágenes con preview.
 * Integrado con MinIO vía ProductsController endpoints.
 *
 * CARACTERÍSTICAS:
 * ✅ Upload múltiple de imágenes
 * ✅ Preview instantáneo
 * ✅ Drag & drop
 * ✅ Validación client-side (tamaño, tipo)
 * ✅ Integración con MinIO Storage
 * ✅ Reordenamiento de imágenes
 * ✅ Eliminación individual
 *
 * ENDPOINTS BACKEND (ProductsController):
 * - POST /api/products/upload_image    - Subir imagen
 * - POST /api/products/delete_image    - Eliminar imagen
 * - POST /api/products/reorder_images  - Reordenar
 * - POST /api/products/set_primary     - Marcar como principal
 *
 * USO BÁSICO:
 * ```php
 * $filePond = new FilePondComponent(
 *     id: 'product-images',
 *     label: 'Imágenes del Producto',
 *     productId: 123,  // Opcional: para asociar a producto existente
 *     maxFiles: 5
 * );
 * echo $filePond->render();
 * ```
 */
class FilePondComponent extends CoreComponent
{
    protected $CSS_PATHS = ["./FilePondComponent.css"];
    protected $JS_PATHS = ["./FilePondComponent.js"];

    public function __construct(
        public readonly string $id,
        public readonly string $label = 'Imágenes',
        public readonly ?int $productId = null,
        public readonly int $maxFiles = 5,
        public readonly int $maxFileSize = 5242880, // 5MB en bytes
        public readonly array $acceptedFileTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        public readonly bool $allowReorder = true,
        public readonly bool $allowMultiple = true,
        public readonly bool $required = false,
        public readonly array $initialImages = []
    ) {}

    protected function component(): string
    {
        $requiredAttr = $this->required ? 'required' : '';
        $maxFileSizeMB = round($this->maxFileSize / 1048576, 1);

        // Convertir initial images a JSON para JavaScript
        $initialImagesJson = htmlspecialchars(json_encode($this->initialImages), ENT_QUOTES, 'UTF-8');

        // Determinar accept types para input HTML
        $acceptTypes = implode(',', $this->acceptedFileTypes);

        // Preparar valores para data-config JSON
        $productIdValue = $this->productId !== null ? $this->productId : 'null';
        $acceptedTypesJson = json_encode($this->acceptedFileTypes);
        $allowReorderStr = $this->allowReorder ? 'true' : 'false';
        $allowMultipleStr = $this->allowMultiple ? 'true' : 'false';

        return <<<HTML
        <div class="lego-filepond" data-filepond-id="{$this->id}">
            <!-- Label -->
            <label class="lego-filepond__label" for="{$this->id}">
                {$this->label}
                {$requiredAttr}
            </label>

            <!-- Help text -->
            <div class="lego-filepond__help">
                Arrastra imágenes o haz clic para seleccionar • Máx. {$this->maxFiles} archivos • {$maxFileSizeMB}MB por archivo
            </div>

            <!-- FilePond Container -->
            <div
                class="lego-filepond__container"
                data-config='{
                    "id": "{$this->id}",
                    "productId": {$productIdValue},
                    "maxFiles": {$this->maxFiles},
                    "maxFileSize": {$this->maxFileSize},
                    "acceptedFileTypes": {$acceptedTypesJson},
                    "allowReorder": {$allowReorderStr},
                    "allowMultiple": {$allowMultipleStr},
                    "initialImages": {$initialImagesJson}
                }'
            >
                <input
                    type="file"
                    id="{$this->id}"
                    name="file"
                    class="lego-filepond__input"
                    {$requiredAttr}
                    accept="{$acceptTypes}"
                />
            </div>

            <!-- Hidden input para guardar IDs de imágenes -->
            <input
                type="hidden"
                id="{$this->id}-image-ids"
                name="{$this->id}_image_ids"
                value=""
            />

            <!-- Error container -->
            <div class="lego-filepond__error" id="{$this->id}-error" style="display: none;"></div>
        </div>
        HTML;
    }
}
