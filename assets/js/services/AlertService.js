/**
 * AlertService - Servicio centralizado para alertas, modales y notificaciones
 *
 * FILOSOFÍA LEGO:
 * Wrapper sobre SweetAlert2 que mantiene consistencia visual con el framework.
 * Proporciona métodos simples para todas las interacciones con el usuario.
 *
 * USO:
 * await AlertService.success('Producto creado correctamente');
 * const confirmed = await AlertService.confirm('¿Eliminar este producto?');
 * AlertService.loading('Guardando...');
 */

class AlertService {

    /**
     * Inicializar SweetAlert2 con configuración por defecto
     */
    static async init() {
        // Cargar SweetAlert2 si no está cargado
        if (typeof Swal === 'undefined') {
            await this.loadSweetAlert2();
        }

        // Detectar tema inicial
        this.updateDefaultConfig();

        // Suscribirse a cambios de tema para actualizar modales abiertos
        if (window.themeManager) {
            window.themeManager.subscribe((theme) => {
                this.updateModalTheme(theme);
                this.updateDefaultConfig();
            });
            console.log('[AlertService] Suscrito a cambios de tema');
        }
    }

    /**
     * Detectar si el tema actual es oscuro
     */
    static isDarkTheme() {
        return document.documentElement.classList.contains('dark');
    }

    /**
     * Actualizar configuración por defecto según el tema actual
     */
    static updateDefaultConfig() {
        const isDark = this.isDarkTheme();

        this.defaultConfig = {
            confirmButtonColor: 'var(--accent-primary, #3b82f6)',
            cancelButtonColor: 'var(--text-secondary, #6b7280)',
            background: isDark ? 'var(--bg-surface, #1f2937)' : '#ffffff',
            color: isDark ? 'var(--text-primary, #f3f4f6)' : '#333333',
            customClass: {
                container: isDark ? 'swal2-dark-theme' : '',
                popup: 'lego-alert-popup',
                title: 'lego-alert-title',
                htmlContainer: 'lego-alert-content',
                confirmButton: 'lego-alert-btn lego-alert-btn--confirm',
                cancelButton: 'lego-alert-btn lego-alert-btn--cancel',
                denyButton: 'lego-alert-btn lego-alert-btn--deny'
            },
            didOpen: (popup) => {
                // Aplicar tema al abrir
                const container = document.querySelector('.swal2-container');
                if (container) {
                    if (isDark) {
                        container.classList.add('swal2-dark-theme');
                    } else {
                        container.classList.remove('swal2-dark-theme');
                    }
                }
            }
        };
    }

    /**
     * Actualizar tema de modales abiertos
     */
    static updateModalTheme(theme) {
        const isDark = theme === 'dark' || document.documentElement.classList.contains('dark');
        const modal = document.querySelector('.swal2-container');

        if (modal) {
            if (isDark) {
                modal.classList.add('swal2-dark-theme');
            } else {
                modal.classList.remove('swal2-dark-theme');
            }
            console.log('[AlertService] Tema de modal actualizado:', isDark ? 'dark' : 'light');
        }
    }

    /**
     * Cargar SweetAlert2 desde CDN
     */
    static async loadSweetAlert2() {
        return new Promise((resolve, reject) => {
            // Cargar CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css';
            document.head.appendChild(link);

            // Cargar JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = () => {
                console.log('[AlertService] SweetAlert2 cargado');
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Toast de éxito
     */
    static success(message, title = 'Éxito') {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            showCloseButton: true,
            toast: true,
            position: 'top-end',
            ...this.defaultConfig
        });
    }

    /**
     * Toast de error
     */
    static error(message, title = 'Error') {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: true,
            showCloseButton: true,
            toast: true,
            position: 'top-end',
            ...this.defaultConfig
        });
    }

    /**
     * Toast de advertencia
     */
    static warning(message, title = 'Advertencia') {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: message,
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            showCloseButton: true,
            toast: true,
            position: 'top-end',
            ...this.defaultConfig
        });
    }

    /**
     * Toast de información
     */
    static info(message, title = 'Información') {
        return Swal.fire({
            icon: 'info',
            title: title,
            text: message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            showCloseButton: true,
            toast: true,
            position: 'top-end',
            ...this.defaultConfig
        });
    }

    /**
     * Diálogo de confirmación
     * Retorna: Promise<boolean>
     */
    static async confirm(message, title = '¿Estás seguro?', confirmText = 'Sí, continuar', cancelText = 'Cancelar') {
        const result = await Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            ...this.defaultConfig
        });

        return result.isConfirmed;
    }

    /**
     * Diálogo de confirmación de eliminación
     * Retorna: Promise<boolean>
     */
    static async confirmDelete(itemName = 'este elemento') {
        const result = await Swal.fire({
            title: '¿Eliminar?',
            html: `¿Estás seguro de eliminar <strong>${itemName}</strong>?<br><small>Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            reverseButtons: true,
            ...this.defaultConfig
        });

        return result.isConfirmed;
    }

    /**
     * Mostrar loading/spinner
     * Retorna: función para cerrar el loading
     */
    static loading(message = 'Cargando...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            ...this.defaultConfig
        });

        // Retornar función para cerrar
        return () => Swal.close();
    }

    /**
     * Cerrar cualquier alerta abierta
     */
    static close() {
        Swal.close();
    }

    /**
     * Modal con formulario personalizado
     */
    static async modal(config) {
        const result = await Swal.fire({
            ...this.defaultConfig,
            ...config
        });

        return result;
    }

    /**
     * Input simple
     */
    static async input(title, placeholder = '', inputType = 'text', defaultValue = '') {
        const result = await Swal.fire({
            title: title,
            input: inputType,
            inputPlaceholder: placeholder,
            inputValue: defaultValue,
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Este campo es requerido';
                }
            },
            ...this.defaultConfig
        });

        if (result.isConfirmed) {
            return result.value;
        }
        return null;
    }

    /**
     * Progress bar
     */
    static progress(title = 'Procesando...', message = '') {
        let currentProgress = 0;

        Swal.fire({
            title: title,
            html: `
                <div class="lego-progress">
                    <div class="lego-progress__bar">
                        <div class="lego-progress__fill" style="width: 0%"></div>
                    </div>
                    <div class="lego-progress__text">${message}</div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            ...this.defaultConfig
        });

        const updateProgress = (percent, newMessage) => {
            currentProgress = Math.min(100, Math.max(0, percent));
            const fill = Swal.getHtmlContainer().querySelector('.lego-progress__fill');
            const text = Swal.getHtmlContainer().querySelector('.lego-progress__text');

            if (fill) fill.style.width = `${currentProgress}%`;
            if (text && newMessage) text.textContent = newMessage;
        };

        const complete = () => {
            updateProgress(100);
            setTimeout(() => Swal.close(), 500);
        };

        return { updateProgress, complete, close: () => Swal.close() };
    }

    /**
     * Modal que carga un componente de LEGO vía API
     *
     * @param {string} componentPath - Ruta del componente (ej: '/component/product-form')
     * @param {Object} options - Opciones del modal
     * @returns {Promise} - Promesa que resuelve con los datos del formulario
     *
     * @example
     * const data = await AlertService.componentModal('/component/product-form', {
     *     title: 'Crear Producto',
     *     confirmButtonText: 'Guardar',
     *     params: { id: 123 } // Parámetros opcionales para el componente
     * });
     */
    static async componentModal(componentPath, options = {}) {
        const {
            title = '',
            confirmButtonText = 'Aceptar',
            cancelButtonText = 'Cancelar',
            width = '800px',
            params = {},
            onBeforeOpen = null,
            onOpen = null
        } = options;

        try {
            // Construir URL con parámetros
            const url = new URL(componentPath, window.location.origin);
            Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

            // Cargar el componente HTML vía fetch
            const response = await fetch(url.toString());
            if (!response.ok) {
                throw new Error(`Error al cargar componente: ${response.statusText}`);
            }

            const htmlContent = await response.text();

            // Callback antes de abrir
            if (onBeforeOpen) {
                await onBeforeOpen();
            }

            // Detectar tema actual
            const currentTheme = window.themeManager ? window.themeManager.getCurrentTheme() :
                                (document.documentElement.classList.contains('dark') ? 'dark' : 'light');

            // Mostrar modal con el contenido del componente
            const result = await Swal.fire({
                title: title,
                html: htmlContent,
                width: width,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                focusConfirm: false,
                customClass: {
                    container: `lego-component-modal ${currentTheme === 'dark' ? 'swal2-dark-theme' : ''}`,
                    popup: 'lego-alert-popup',
                    confirmButton: 'lego-alert-btn lego-alert-btn--confirm',
                    cancelButton: 'lego-alert-btn lego-alert-btn--cancel'
                },
                didOpen: (popup) => {
                    // Aplicar tema inicial al modal
                    const container = document.querySelector('.swal2-container');
                    if (container && currentTheme === 'dark') {
                        container.classList.add('swal2-dark-theme');
                    }

                    // Ejecutar scripts del componente si los hay
                    const scripts = popup.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        if (script.src) {
                            newScript.src = script.src;
                        } else {
                            newScript.textContent = script.textContent;
                        }
                        document.body.appendChild(newScript);
                    });

                    // Callback cuando se abre
                    if (onOpen) {
                        onOpen(popup);
                    }
                },
                preConfirm: () => {
                    // Buscar formulario en el modal
                    const form = Swal.getPopup().querySelector('form');
                    if (!form) {
                        console.warn('[AlertService] No se encontró formulario en el componente');
                        return null;
                    }

                    // Validar formulario HTML5
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return false;
                    }

                    // Recopilar datos del formulario
                    const formData = new FormData(form);
                    const data = {};
                    for (let [key, value] of formData.entries()) {
                        // Manejar checkboxes
                        if (form.elements[key].type === 'checkbox') {
                            data[key] = form.elements[key].checked;
                        } else {
                            data[key] = value;
                        }
                    }

                    return data;
                },
                ...this.defaultConfig
            });

            return result;

        } catch (error) {
            console.error('[AlertService] Error en componentModal:', error);
            this.error('Error al cargar el formulario: ' + error.message);
            return { isConfirmed: false, value: null };
        }
    }
}

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AlertService.init());
} else {
    AlertService.init();
}

// Exponer globalmente
window.AlertService = AlertService;

console.log('[LEGO Framework] AlertService cargado');
