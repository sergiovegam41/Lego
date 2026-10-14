<?php

namespace Components\Shared\Essentials\ImageGalleryComponent;

use Core\Components\CoreComponent\CoreComponent;

/**
 * ImageGalleryComponent - Gestor completo de galería de imágenes
 *
 * FILOSOFÍA LEGO:
 * Componente todo-en-uno para gestión de imágenes:
 * - Carga múltiple de archivos (drag & drop)
 * - Preview de imágenes
 * - Ordenamiento por drag & drop
 * - Marcado de imagen principal
 * - Eliminación de imágenes
 * - Integración con MinIO
 *
 * CARACTERÍSTICAS:
 * - Responsive y theme-aware
 * - Validación de tipos y tamaños
 * - Progress bars para cargas
 * - Gestión de estado con JavaScript
 * - API REST para operaciones
 *
 * USO:
 * ```php
 * ImageGalleryComponent::create(
 *     id: 'product-gallery',
 *     entityId: $productId,
 *     uploadEndpoint: '/api/products/upload_image',
 *     deleteEndpoint: '/api/products/delete_image',
 *     reorderEndpoint: '/api/products/reorder_images',
 *     setPrimaryEndpoint: '/api/products/set_primary',
 *     maxFiles: 10,
 *     maxFileSize: 5242880, // 5MB
 *     acceptedTypes: ['image/jpeg', 'image/png', 'image/webp']
 * )
 * ```
 */
class ImageGalleryComponent extends CoreComponent
{
    protected $CSS_PATHS = ['./image-gallery.css'];
    protected $JS_PATHS = ['./image-gallery.js'];

    protected string $id;
    protected ?int $entityId;
    protected array $existingImages;
    protected string $uploadEndpoint;
    protected string $deleteEndpoint;
    protected string $reorderEndpoint;
    protected string $setPrimaryEndpoint;
    protected int $maxFiles;
    protected int $maxFileSize;
    protected array $acceptedTypes;
    protected string $height;

    protected function __construct(
        string $id,
        ?int $entityId = null,
        array $existingImages = [],
        string $uploadEndpoint = '',
        string $deleteEndpoint = '',
        string $reorderEndpoint = '',
        string $setPrimaryEndpoint = '',
        int $maxFiles = 10,
        int $maxFileSize = 5242880, // 5MB
        array $acceptedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        string $height = '400px'
    ) {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->existingImages = $existingImages;
        $this->uploadEndpoint = $uploadEndpoint;
        $this->deleteEndpoint = $deleteEndpoint;
        $this->reorderEndpoint = $reorderEndpoint;
        $this->setPrimaryEndpoint = $setPrimaryEndpoint;
        $this->maxFiles = $maxFiles;
        $this->maxFileSize = $maxFileSize;
        $this->acceptedTypes = $acceptedTypes;
        $this->height = $height;
    }

    public static function create(
        string $id,
        ?int $entityId = null,
        array $existingImages = [],
        string $uploadEndpoint = '',
        string $deleteEndpoint = '',
        string $reorderEndpoint = '',
        string $setPrimaryEndpoint = '',
        int $maxFiles = 10,
        int $maxFileSize = 5242880,
        array $acceptedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        string $height = '400px'
    ): self {
        return new self(
            $id,
            $entityId,
            $existingImages,
            $uploadEndpoint,
            $deleteEndpoint,
            $reorderEndpoint,
            $setPrimaryEndpoint,
            $maxFiles,
            $maxFileSize,
            $acceptedTypes,
            $height
        );
    }

    protected function component(): string
    {
        // Preparar datos para JavaScript
        $existingImagesJson = json_encode($this->existingImages);
        $entityIdValue = $this->entityId ?? 'null';
        $maxFileSizeMB = round($this->maxFileSize / 1048576, 2);

        // Si no hay entityId, mostrar mensaje
        if (!$this->entityId) {
            return <<<HTML
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Guarda el producto primero para poder agregar imágenes
                    </span>
                </div>
            </div>
            HTML;
        }

        return <<<HTML
        <div id="{$this->id}" class="image-gallery-filepond" data-entity-id="{$this->entityId}">

            <!-- FilePond Input -->
            <input
                type="file"
                id="{$this->id}-filepond"
                name="{$this->id}-filepond"
                multiple
                data-max-files="{$this->maxFiles}"
            />

        </div>

        <script>
            (function() {
                // Función para inicializar FilePond cuando esté disponible
                const galleryId = '{$this->id}';
                const initFunction = function() {
                    if (typeof FilePond === 'undefined') {
                        setTimeout(initFunction, 100);
                        return;
                    }


                    // Registrar plugins de FilePond
                    FilePond.registerPlugin(
                        FilePondPluginImagePreview,
                        FilePondPluginFileValidateType,
                        FilePondPluginFileValidateSize,
                        FilePondPluginImageExifOrientation
                    );

                    // Obtener el input
                    const inputElement = document.querySelector('#' + galleryId + '-filepond');

                    if (!inputElement) {
                        console.error('[FilePond] No se encontró el input element con id:', galleryId + '-filepond');
                        return;
                    }


                    // Crear instancia de FilePond
                    const pond = FilePond.create(inputElement, {
                        name: 'file',
                        maxFiles: {$this->maxFiles},
                        maxFileSize: '{$maxFileSizeMB}MB',
                        acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        allowReorder: true,
                        allowMultiple: true,
                        instantUpload: true,

                    // Labels en español
                    labelIdle: 'Arrastra tus imágenes o <span class="filepond--label-action">Busca archivos</span>',
                    labelFileProcessing: 'Subiendo',
                    labelFileProcessingComplete: 'Subida completa',
                    labelFileProcessingAborted: 'Subida cancelada',
                    labelFileProcessingError: 'Error en la subida',
                    labelTapToCancel: 'toca para cancelar',
                    labelTapToRetry: 'toca para reintentar',
                    labelTapToUndo: 'toca para deshacer',
                    labelButtonRemoveItem: 'Eliminar',
                    labelButtonAbortItemLoad: 'Abortar',
                    labelButtonRetryItemLoad: 'Reintentar',
                    labelButtonAbortItemProcessing: 'Cancelar',
                    labelButtonUndoItemProcessing: 'Deshacer',
                    labelButtonRetryItemProcessing: 'Reintentar',
                    labelButtonProcessItem: 'Subir',
                    labelMaxFileSizeExceeded: 'Archivo muy grande',
                    labelMaxFileSize: 'El tamaño máximo es {filesize}',
                    labelMaxTotalFileSizeExceeded: 'Tamaño total excedido',
                    labelMaxTotalFileSize: 'Tamaño total máximo: {filesize}',
                    labelFileTypeNotAllowed: 'Tipo de archivo inválido',
                    fileValidateTypeLabelExpectedTypes: 'Esperado: {allButLastType} o {lastType}',

                    // Servidor personalizado
                    server: {
                        process: (fieldName, file, metadata, load, error, progress, abort) => {
                            const formData = new FormData();
                            formData.append('file', file);
                            formData.append('product_id', {$entityIdValue});

                            const request = new XMLHttpRequest();
                            request.open('POST', '{$this->uploadEndpoint}');

                            // Progreso de subida
                            request.upload.onprogress = (e) => {
                                progress(e.lengthComputable, e.loaded, e.total);
                            };

                            // Éxito
                            request.onload = function() {
                                if (request.status >= 200 && request.status < 300) {
                                    // El servidor retorna el ID como texto plano
                                    const fileId = request.responseText.trim();

                                    if (fileId) {
                                        // Retornar el ID de la imagen para poder eliminarla después
                                        load(fileId);
                                    } else {
                                        error('Error al subir imagen: respuesta vacía');
                                    }
                                } else {
                                    error('Error en el servidor');
                                }
                            };

                            // Error
                            request.onerror = () => {
                                error('Error de red');
                            };

                            request.send(formData);

                            // Función para abortar
                            return {
                                abort: () => {
                                    request.abort();
                                    abort();
                                }
                            };
                        },

                        revert: (uniqueFileId, load, error) => {

                            fetch('{$this->deleteEndpoint}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    image_id: uniqueFileId
                                })
                            })
                            .then(response => {
                                return response.json();
                            })
                            .then(data => {
                                // El backend retorna { success: true/false, msj: '...', data: null }
                                if (data.success === true) {
                                    load();
                                } else {
                                    console.error('[FilePond] Error al eliminar:', data.msj);
                                    error(data.msj || 'Error al eliminar');
                                }
                            })
                            .catch((err) => {
                                console.error('[FilePond] Error de red:', err);
                                error('Error de red');
                            });
                        },

                        load: (source, load, error, progress, abort, headers) => {
                            // Para cargar imágenes existentes
                            fetch(source)
                                .then(response => response.blob())
                                .then(load)
                                .catch(() => error('Error al cargar imagen'));

                            return {
                                abort: () => {
                                    abort();
                                }
                            };
                        },

                        remove: (source, load, error) => {
                            // Se llama cuando se elimina un archivo que ya existía (tipo 'local')

                            fetch('{$this->deleteEndpoint}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    image_id: source
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success === true) {
                                    load();
                                } else {
                                    error(data.msj || 'Error al eliminar');
                                }
                            })
                            .catch((err) => {
                                console.error('[FilePond] Error en remove:', err);
                                error('Error de red');
                            });
                        }
                    },

                    // Estilo
                    stylePanelLayout: 'compact',
                    styleLoadIndicatorPosition: 'center bottom',
                    styleProgressIndicatorPosition: 'right bottom',
                    styleButtonRemoveItemPosition: 'left bottom',
                    styleButtonProcessItemPosition: 'right bottom',
                });

                // Cargar imágenes existentes
                const existingImages = {$existingImagesJson};
                if (existingImages && existingImages.length > 0) {
                    existingImages.forEach(image => {
                        // IMPORTANTE: usar el ID como source, no la URL
                        // FilePond pasará este source al método 'remove' cuando se elimine
                        pond.addFile(image.url, {
                            type: 'local',
                            file: {
                                name: image.original_name,
                                size: image.size,
                            },
                            metadata: {
                                id: image.id,
                                is_primary: image.is_primary,
                                url: image.url
                            }
                        }).then(fileItem => {
                            // Establecer serverId = ID para que el método remove lo reciba
                            if (fileItem) {
                                fileItem.serverId = String(image.id);
                            }
                        }).catch(err => {
                            console.error('[FilePond] Error al agregar archivo:', err);
                        });
                    });
                    }

                    // Guardar instancia y configuración para acceso global
                    window.filePondInstances = window.filePondInstances || {};
                    window.filePondInstances[galleryId] = {
                        pond: pond,
                        config: {
                            setPrimaryEndpoint: '{$this->setPrimaryEndpoint}'
                        }
                    };

                    // Agregar botones de imagen principal usando el helper
                    if (typeof window.ImageGalleryFilePondHelpers !== 'undefined') {
                        window.ImageGalleryFilePondHelpers.addPrimaryButtons(
                            pond,
                            '{$this->setPrimaryEndpoint}',
                            galleryId
                        );
                    }

                };

                // Iniciar cuando el DOM esté listo
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initFunction);
                } else {
                    initFunction();
                }
            })();
        </script>
        HTML;
    }

    private function getExtensionsFromMimeTypes(): array
    {
        $extensions = [];
        foreach ($this->acceptedTypes as $mimeType) {
            $extensions[] = match($mimeType) {
                'image/jpeg' => 'jpg,jpeg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => ''
            };
        }
        return array_filter($extensions);
    }
}
