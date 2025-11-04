/**
 * ImageCarousel - Modal con carrusel Glide.js para mostrar imágenes
 *
 * Abre un modal de SweetAlert2 con un carrusel Glide.js
 * para mostrar todas las imágenes de un ítem
 */

console.log('[ImageCarousel] Script cargado');

// CDN de Glide.js
const GLIDE_CDN = {
    css: 'https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css',
    theme: 'https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css',
    js: 'https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js'
};

// Cargar Glide.js dinámicamente
let glideLoaded = false;
let glideLoadPromise = null;

async function loadGlide() {
    if (glideLoaded) return;
    if (glideLoadPromise) return glideLoadPromise;

    console.log('[ImageCarousel] Cargando Glide.js desde CDN...');

    glideLoadPromise = (async () => {
        // Cargar CSS
        const cssLink1 = document.createElement('link');
        cssLink1.rel = 'stylesheet';
        cssLink1.href = GLIDE_CDN.css;
        document.head.appendChild(cssLink1);

        const cssLink2 = document.createElement('link');
        cssLink2.rel = 'stylesheet';
        cssLink2.href = GLIDE_CDN.theme;
        document.head.appendChild(cssLink2);

        // Cargar JavaScript
        await loadScript(GLIDE_CDN.js);

        glideLoaded = true;
        console.log('[ImageCarousel] Glide.js cargado exitosamente');
    })();

    return glideLoadPromise;
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

/**
 * Abrir modal con carrusel de imágenes
 * @param {Array} images - Array de objetos imagen [{url, original_name, is_primary}]
 * @param {string} title - Título del modal
 */
window.openImageCarousel = async function(images, title = 'Imágenes') {
    console.log('[ImageCarousel] Abriendo carrusel:', { images, title });

    if (!images || images.length === 0) {
        if (window.AlertService) {
            window.AlertService.warning('Sin imágenes', 'No hay imágenes disponibles para mostrar');
        } else {
            alert('No hay imágenes disponibles para mostrar');
        }
        return;
    }

    // Cargar Glide.js si no está cargado
    await loadGlide();

    // Generar HTML de las slides del carrusel
    const slidesHtml = images.map((img, index) => `
        <li class="glide__slide">
            <div style="width:100%;height:400px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;">
                <img
                    src="${img.url}"
                    alt="${img.original_name || 'Imagen ' + (index + 1)}"
                    style="max-width:100%;max-height:400px;object-fit:contain;border-radius:8px;"
                />
            </div>
            <p style="text-align:center;margin-top:12px;font-size:14px;color:#666;">
                ${img.original_name || 'Imagen ' + (index + 1)}
                ${img.is_primary ? '<span style="color:#10b981;font-weight:500;margin-left:8px;">● Principal</span>' : ''}
            </p>
        </li>
    `).join('');

    // Bullets HTML
    const bulletsHtml = images.map((_, index) => `
        <button class="glide__bullet" data-glide-dir="=${index}"></button>
    `).join('');

    // HTML completo del carrusel
    const carouselHtml = `
        <div class="glide" id="image-carousel-glide">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    ${slidesHtml}
                </ul>
            </div>

            <!-- Controles de navegación -->
            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<" style="left:20px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">" style="right:20px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Bullets para navegación -->
            <div class="glide__bullets" data-glide-el="controls[nav]" style="margin-top:20px;">
                ${bulletsHtml}
            </div>
        </div>

        <style>
            /* Estilos personalizados para el carrusel */
            .glide__arrow {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
                background: rgba(255, 255, 255, 0.9);
                border: none;
                border-radius: 50%;
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                transition: all 0.2s;
            }
            .glide__arrow:hover {
                background: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
            .glide__arrow svg {
                width: 24px;
                height: 24px;
                color: #333;
            }
            .glide__bullet {
                background: #ccc;
                border: none;
                border-radius: 50%;
                width: 12px;
                height: 12px;
                margin: 0 6px;
                cursor: pointer;
                transition: all 0.2s;
            }
            .glide__bullet--active {
                background: #10b981;
                width: 14px;
                height: 14px;
            }
            .glide__bullets {
                display: flex;
                justify-content: center;
                align-items: center;
            }
        </style>
    `;

    // Abrir modal de SweetAlert2
    Swal.fire({
        title: title,
        html: carouselHtml,
        width: '800px',
        showCloseButton: true,
        showConfirmButton: false,
        didOpen: () => {
            // Inicializar Glide después de que el modal se abra
            const glideElement = document.getElementById('image-carousel-glide');
            if (glideElement && window.Glide) {
                new Glide('#image-carousel-glide', {
                    type: 'carousel',
                    startAt: 0,
                    perView: 1,
                    gap: 0,
                    autoplay: false,
                    hoverpause: true,
                    keyboard: true
                }).mount();

                console.log('[ImageCarousel] Glide inicializado');
            } else {
                console.error('[ImageCarousel] No se pudo inicializar Glide');
            }
        }
    });
};

console.log('[ImageCarousel] Función global window.openImageCarousel lista');
