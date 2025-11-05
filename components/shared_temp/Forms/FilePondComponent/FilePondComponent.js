/**
 * FilePondComponent - Lógica de upload con FilePond
 *
 * FILOSOFÍA LEGO:
 * Integración con MinIO vía ProductsController endpoints.
 * Upload asíncrono con preview instantáneo.
 *
 * ENDPOINTS BACKEND:
 * - POST /api/products/upload_image   - Subir (retorna ID como text/plain)
 * - POST /api/products/delete_image   - Eliminar
 *
 * FUNCIONALIDADES:
 * ✅ Upload con validación (tipo, tamaño)
 * ✅ Preview de imágenes
 * ✅ Drag & drop
 * ✅ Reordenamiento
 * ✅ Eliminación individual
 * ✅ Asociación automática a producto (si productId existe)
 */

console.log('[FilePondComponent] Script cargado');

// CDN de FilePond
const FILEPOND_CDN = {
    css: 'https://unpkg.com/filepond@^4/dist/filepond.css',
    cssPreview: 'https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.css',
    core: 'https://unpkg.com/filepond@^4/dist/filepond.js',
    imagePreview: 'https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.js',
    fileValidateType: 'https://unpkg.com/filepond-plugin-file-validate-type@^1/dist/filepond-plugin-file-validate-type.js',
    fileValidateSize: 'https://unpkg.com/filepond-plugin-file-validate-size@^2/dist/filepond-plugin-file-validate-size.js',
    imageExifOrientation: 'https://unpkg.com/filepond-plugin-image-exif-orientation@^1/dist/filepond-plugin-image-exif-orientation.js'
};

// Cargar FilePond dinámicamente
async function loadFilePond() {
    // Si ya está cargado, no hacer nada
    if (window.FilePond) {
        return;
    }

    console.log('[FilePondComponent] Cargando FilePond desde CDN...');

    // Cargar CSS
    const cssLink1 = document.createElement('link');
    cssLink1.rel = 'stylesheet';
    cssLink1.href = FILEPOND_CDN.css;
    document.head.appendChild(cssLink1);

    const cssLink2 = document.createElement('link');
    cssLink2.rel = 'stylesheet';
    cssLink2.href = FILEPOND_CDN.cssPreview;
    document.head.appendChild(cssLink2);

    // Cargar JavaScript (en orden)
    await loadScript(FILEPOND_CDN.imagePreview);
    await loadScript(FILEPOND_CDN.fileValidateType);
    await loadScript(FILEPOND_CDN.fileValidateSize);
    await loadScript(FILEPOND_CDN.imageExifOrientation);
    await loadScript(FILEPOND_CDN.core);

    console.log('[FilePondComponent] FilePond cargado exitosamente');
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// Inicializar instancias de FilePond
async function initializeFilePond() {
    console.log('[FilePondComponent] Inicializando instancias...');

    // Cargar FilePond si no está disponible
    await loadFilePond();

    // Registrar plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginImageExifOrientation
    );

    // IMPORTANTE: Buscar containers SOLO dentro del módulo activo
    const activeModuleId = window.moduleStore?.getActiveModule();
    let searchContext = document;

    if (activeModuleId) {
        const activeModuleContainer = document.getElementById(`module-${activeModuleId}`);
        if (activeModuleContainer) {
            searchContext = activeModuleContainer;
            console.log('[FilePondComponent] Buscando solo en módulo activo:', activeModuleId);
        }
    }

    // Buscar contenedores FilePond dentro del contexto (módulo activo o documento completo)
    const containers = searchContext.querySelectorAll('.lego-filepond__container');

    containers.forEach(container => {
        const config = JSON.parse(container.getAttribute('data-config') || '{}');
        const inputElement = container.querySelector('input[type="file"]');

        if (!inputElement) {
            console.error('[FilePondComponent] Input no encontrado en container');
            return;
        }

        console.log('[FilePondComponent] Configurando FilePond:', config.id);

        // Crear instancia de FilePond
        const pond = FilePond.create(inputElement, {
            // Configuración básica
            allowMultiple: config.allowMultiple !== false,
            maxFiles: config.maxFiles || 5,
            maxFileSize: config.maxFileSize || '5MB',
            acceptedFileTypes: config.acceptedFileTypes || ['image/*'],

            // Reordenamiento
            allowReorder: config.allowReorder !== false,

            // Labels en español
            labelIdle: 'Arrastra tus imágenes o <span class="filepond--label-action">Explora</span>',
            labelInvalidField: 'El campo contiene archivos inválidos',
            labelFileWaitingForSize: 'Esperando tamaño',
            labelFileSizeNotAvailable: 'Tamaño no disponible',
            labelFileLoading: 'Cargando',
            labelFileLoadError: 'Error durante la carga',
            labelFileProcessing: 'Subiendo',
            labelFileProcessingComplete: 'Subida completa',
            labelFileProcessingAborted: 'Subida cancelada',
            labelFileProcessingError: 'Error durante la subida',
            labelFileProcessingRevertError: 'Error al revertir',
            labelFileRemoveError: 'Error al eliminar',
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
            labelMaxFileSizeExceeded: 'El archivo es demasiado grande',
            labelMaxFileSize: 'El tamaño máximo es {filesize}',
            labelMaxTotalFileSizeExceeded: 'Tamaño total máximo excedido',
            labelMaxTotalFileSize: 'El tamaño total máximo es {filesize}',
            labelFileTypeNotAllowed: 'Tipo de archivo no válido',
            fileValidateTypeLabelExpectedTypes: 'Esperaba {allButLastType} o {lastType}',

            // Configuración de servidor
            server: {
                // Upload (UNIVERSAL)
                process: {
                    url: '/api/files/upload',
                    method: 'POST',
                    withCredentials: false,
                    headers: {},
                    timeout: 7000,
                    ondata: (formData) => {
                        console.log('[FilePondComponent] Preparando upload, FormData:', formData);

                        // Agregar path (ej: 'products/images/', 'documents/pdf/')
                        if (config.path) {
                            formData.append('path', config.path);
                            console.log('[FilePondComponent] Path agregado:', config.path);
                        }

                        // Debug: ver todos los campos del FormData
                        for (let pair of formData.entries()) {
                            console.log('[FilePondComponent] FormData field:', pair[0], pair[1]);
                        }

                        return formData;
                    },
                    onload: (response) => {
                        console.log('[FilePondComponent] Upload exitoso, response:', response);
                        // FilesController retorna el ID del archivo como text/plain
                        return response;
                    },
                    onerror: (response) => {
                        console.error('[FilePondComponent] Upload error:', response);
                        return response;
                    }
                },

                // Revert (cancelar upload antes de guardar)
                revert: null,

                // Load (cargar archivos existentes)
                load: (source, load, error, progress, abort, headers) => {
                    // source puede ser una URL o un ID de imagen
                    console.log('[FilePondComponent] Cargando imagen:', source);

                    fetch(source)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error cargando imagen');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            load(blob);
                        })
                        .catch(err => {
                            console.error('[FilePondComponent] Error cargando imagen:', err);
                            error(err.message);
                        });

                    return {
                        abort: () => {
                            abort();
                        }
                    };
                },

                // Remove (eliminar archivo ya subido - UNIVERSAL)
                remove: (source, load, error) => {
                    fetch('/api/files/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            file_id: source
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            load();
                        } else {
                            error(result.msj || 'Error al eliminar');
                        }
                    })
                    .catch(err => {
                        console.error('[FilePondComponent] Error eliminando:', err);
                        error('Error al eliminar archivo');
                    });
                }
            },

            // Cargar imágenes iniciales (si existen)
            files: config.initialImages.map(img => ({
                source: img.id || img.url,
                options: {
                    type: 'local',
                    file: {
                        name: img.original_name || img.name,
                        size: img.size,
                        type: img.mime_type
                    },
                    metadata: {
                        poster: img.url
                    }
                }
            }))
        });

        // Guardar instancia en el container para acceso posterior
        container._filePondInstance = pond;

        // Actualizar hidden input con IDs cuando cambien los archivos
        pond.on('processfile', (error, file) => {
            if (!error) {
                updateImageIds(config.id);
            }
        });

        pond.on('removefile', () => {
            updateImageIds(config.id);
        });

        console.log('[FilePondComponent] FilePond inicializado:', config.id);
    });
}

// Actualizar input hidden con IDs de imágenes
function updateImageIds(componentId) {
    const container = document.querySelector(`[data-filepond-id="${componentId}"] .lego-filepond__container`);
    const hiddenInput = document.getElementById(`${componentId}-image-ids`);

    if (!container || !container._filePondInstance) {
        return;
    }

    const pond = container._filePondInstance;
    const files = pond.getFiles();

    // Extraer los IDs de TODAS las fuentes posibles
    const imageIds = files
        .map(file => {
            // Intentar obtener el ID de múltiples fuentes:

            // 1. Primero intentar serverId (imágenes recién subidas)
            if (file.serverId && !file.serverId.startsWith('http')) {
                return file.serverId;
            }

            // 2. Intentar source si es un número
            if (file.source && !file.source.startsWith('http')) {
                return file.source;
            }

            // 3. Intentar metadata.imageId (imágenes cargadas de BD)
            const metadata = file.getMetadata();
            if (metadata && metadata.imageId) {
                return metadata.imageId.toString();
            }

            // 4. Si serverId es una URL, intentar extraer el ID del filename
            // URLs como: http://localhost:9000/lego-uploads/products/images/product_ID.jpg
            // NO USAR ESTO - es muy frágil

            console.warn('[FilePondComponent] No se pudo extraer ID de archivo:', file);
            return null;
        })
        .filter(id => id !== null); // Filtrar nulls

    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(imageIds);
    }

    console.log('[FilePondComponent] IDs actualizados:', imageIds);
}

// API pública para obtener IDs de imágenes
window.FilePondComponent = {
    getImageIds: function(componentId) {
        const hiddenInput = document.getElementById(`${componentId}-image-ids`);
        if (!hiddenInput || !hiddenInput.value) {
            return [];
        }
        try {
            return JSON.parse(hiddenInput.value);
        } catch (e) {
            return [];
        }
    },

    getInstance: function(componentId) {
        const container = document.querySelector(`[data-filepond-id="${componentId}"] .lego-filepond__container`);
        return container ? container._filePondInstance : null;
    }
};

// Inicializar cuando el DOM esté listo
let attempts = 0;
const maxAttempts = 40;

function tryInitialize() {
    const containers = document.querySelectorAll('.lego-filepond__container');

    if (containers.length > 0) {
        console.log('[FilePondComponent] Containers encontrados, inicializando...');
        initializeFilePond();
    } else if (attempts < maxAttempts) {
        attempts++;
        setTimeout(tryInitialize, 50);
    } else {
        console.log('[FilePondComponent] No se encontraron containers FilePond');
    }
}

// Iniciar
tryInitialize();
