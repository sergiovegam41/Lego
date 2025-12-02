/**
 * ConfirmationService - Servicio versátil para confirmaciones
 *
 * FILOSOFÍA LEGO:
 * Sistema abstracto y reutilizable para cualquier tipo de confirmación.
 * Proporciona presets comunes (delete, logout, warning) y total personalización.
 *
 * USO BÁSICO:
 * const confirmed = await ConfirmationService.delete('el registro');
 * const confirmed = await ConfirmationService.logout();
 * const confirmed = await ConfirmationService.warning('¿Continuar con esta acción?');
 *
 * USO PERSONALIZADO:
 * const confirmed = await ConfirmationService.custom({
 *     title: '¿Estás seguro?',
 *     message: 'Esta acción es irreversible',
 *     confirmText: 'Sí, continuar',
 *     cancelText: 'Cancelar',
 *     icon: 'warning',
 *     variant: 'danger'
 * });
 *
 * CARACTERÍSTICAS:
 * ✅ Presets para casos comunes (delete, logout, warning, danger, info)
 * ✅ Totalmente personalizable para casos específicos
 * ✅ Integración con AlertService (respeta dark/light theme)
 * ✅ API simple y consistente
 * ✅ Soporte para HTML en mensajes
 * ✅ Callbacks opcionales (onConfirm, onCancel)
 */

// Evitar redeclaración si ya existe
if (typeof ConfirmationService === 'undefined') {
    class ConfirmationService {

    /**
     * PRESET: Confirmación de eliminación
     *
     * @param {string} itemName - Nombre del elemento a eliminar
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     *
     * @example
     * const confirmed = await ConfirmationService.delete('el producto #123');
     * if (confirmed) {
     *     // Proceder con eliminación
     * }
     */
    static async delete(itemName = 'este elemento', options = {}) {
        return this.custom({
            title: '¿Eliminar?',
            message: `¿Estás seguro de eliminar <strong>${itemName}</strong>?`,
            description: 'Esta acción no se puede deshacer.',
            confirmText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            icon: 'warning',
            variant: 'danger',
            ...options
        });
    }

    /**
     * PRESET: Confirmación de logout
     *
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     *
     * @example
     * const confirmed = await ConfirmationService.logout();
     * if (confirmed) {
     *     // Proceder con logout
     * }
     */
    static async logout(options = {}) {
        return this.custom({
            title: '¿Cerrar sesión?',
            message: '¿Estás seguro de que deseas cerrar sesión?',
            description: 'Tendrás que volver a iniciar sesión para acceder.',
            confirmText: 'Sí, cerrar sesión',
            cancelText: 'Cancelar',
            icon: 'question',
            variant: 'warning',
            ...options
        });
    }

    /**
     * PRESET: Confirmación de advertencia genérica
     *
     * @param {string} message - Mensaje de advertencia
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     *
     * @example
     * const confirmed = await ConfirmationService.warning('¿Continuar con esta acción?');
     */
    static async warning(message, options = {}) {
        return this.custom({
            title: '¿Estás seguro?',
            message: message,
            confirmText: 'Sí, continuar',
            cancelText: 'Cancelar',
            icon: 'warning',
            variant: 'warning',
            ...options
        });
    }

    /**
     * PRESET: Confirmación de acción peligrosa
     *
     * @param {string} message - Mensaje de la acción peligrosa
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     */
    static async danger(message, options = {}) {
        return this.custom({
            title: '¡Atención!',
            message: message,
            description: 'Esta acción puede tener consecuencias importantes.',
            confirmText: 'Sí, continuar',
            cancelText: 'Cancelar',
            icon: 'error',
            variant: 'danger',
            ...options
        });
    }

    /**
     * PRESET: Confirmación informativa
     *
     * @param {string} message - Mensaje informativo
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     */
    static async info(message, options = {}) {
        return this.custom({
            title: 'Información',
            message: message,
            confirmText: 'Aceptar',
            cancelText: 'Cancelar',
            icon: 'info',
            variant: 'primary',
            ...options
        });
    }

    /**
     * PRESET: Confirmación de guardado/cambios
     *
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     */
    static async unsavedChanges(options = {}) {
        return this.custom({
            title: '¿Salir sin guardar?',
            message: 'Tienes cambios sin guardar.',
            description: 'Si sales ahora, perderás los cambios realizados.',
            confirmText: 'Salir sin guardar',
            cancelText: 'Continuar editando',
            icon: 'warning',
            variant: 'warning',
            ...options
        });
    }

    /**
     * MÉTODO PRINCIPAL: Confirmación totalmente personalizable
     *
     * @param {Object} config - Configuración completa de la confirmación
     * @returns {Promise<boolean>}
     *
     * @example
     * const confirmed = await ConfirmationService.custom({
     *     title: '¿Publicar artículo?',
     *     message: 'El artículo será visible para todos los usuarios.',
     *     description: 'Podrás despublicarlo más tarde si lo necesitas.',
     *     confirmText: 'Publicar',
     *     cancelText: 'Cancelar',
     *     icon: 'question',
     *     variant: 'primary',
     *     onConfirm: () => console.log('Confirmado'),
     *     onCancel: () => console.log('Cancelado')
     * });
     */
    static async custom({
        title = '¿Estás seguro?',
        message = '',
        description = '',
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        icon = 'question',
        variant = 'primary', // primary, warning, danger, info
        html = null, // HTML personalizado (sobrescribe message + description)
        showCancelButton = true,
        reverseButtons = true,
        focusCancel = true,
        onConfirm = null,
        onCancel = null,
        width = '500px',
        allowOutsideClick = true,
        allowEscapeKey = true
    }) {
        // Construir HTML del mensaje
        let htmlContent = html;
        if (!htmlContent) {
            htmlContent = `<div class="lego-confirmation">`;

            if (message) {
                htmlContent += `<div class="lego-confirmation__message">${message}</div>`;
            }

            if (description) {
                htmlContent += `<div class="lego-confirmation__description">${description}</div>`;
            }

            htmlContent += `</div>`;
        }

        // Mapear variantes a colores de botón
        const confirmButtonColors = {
            primary: '#3b82f6',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };

        // Verificar que AlertService esté disponible
        if (typeof window.AlertService === 'undefined') {
            console.error('[ConfirmationService] AlertService no está disponible');
            return false;
        }

        try {
            // Mostrar modal usando AlertService
            const result = await Swal.fire({
                title: title,
                html: htmlContent,
                icon: icon,
                showCancelButton: showCancelButton,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                confirmButtonColor: confirmButtonColors[variant] || confirmButtonColors.primary,
                reverseButtons: reverseButtons,
                focusCancel: focusCancel,
                width: width,
                allowOutsideClick: allowOutsideClick,
                allowEscapeKey: allowEscapeKey,
                position: 'top',
                customClass: {
                    popup: 'lego-alert-popup lego-confirmation-popup',
                    confirmButton: `lego-alert-btn lego-alert-btn--confirm lego-alert-btn--${variant}`,
                    cancelButton: 'lego-alert-btn lego-alert-btn--cancel'
                },
                ...window.AlertService.defaultConfig
            });

            // Ejecutar callbacks si están definidos
            if (result.isConfirmed && onConfirm) {
                await onConfirm();
            } else if (result.isDismissed && onCancel) {
                await onCancel();
            }

            return result.isConfirmed;

        } catch (error) {
            console.error('[ConfirmationService] Error al mostrar confirmación:', error);
            return false;
        }
    }

    /**
     * Confirmación con input (útil para verificar nombre antes de eliminar)
     *
     * @param {Object} config - Configuración
     * @returns {Promise<{confirmed: boolean, value: string|null}>}
     *
     * @example
     * const {confirmed, value} = await ConfirmationService.withInput({
     *     title: 'Confirmar eliminación',
     *     message: 'Escribe "ELIMINAR" para confirmar',
     *     placeholder: 'ELIMINAR',
     *     expectedValue: 'ELIMINAR',
     *     caseSensitive: false
     * });
     */
    static async withInput({
        title = 'Confirmación',
        message = '',
        placeholder = '',
        expectedValue = null,
        caseSensitive = true,
        inputType = 'text',
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        variant = 'warning'
    }) {
        try {
            const result = await Swal.fire({
                title: title,
                html: message,
                input: inputType,
                inputPlaceholder: placeholder,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Este campo es requerido';
                    }

                    // Validar valor esperado si está definido
                    if (expectedValue !== null) {
                        const compare1 = caseSensitive ? value : value.toLowerCase();
                        const compare2 = caseSensitive ? expectedValue : expectedValue.toLowerCase();

                        if (compare1 !== compare2) {
                            return `Debes escribir "${expectedValue}" para confirmar`;
                        }
                    }
                },
                customClass: {
                    popup: 'lego-alert-popup lego-confirmation-popup',
                    confirmButton: `lego-alert-btn lego-alert-btn--confirm lego-alert-btn--${variant}`,
                    cancelButton: 'lego-alert-btn lego-alert-btn--cancel'
                },
                ...window.AlertService.defaultConfig
            });

            return {
                confirmed: result.isConfirmed,
                value: result.value || null
            };

        } catch (error) {
            console.error('[ConfirmationService] Error en confirmación con input:', error);
            return { confirmed: false, value: null };
        }
    }
}

    // Auto-inicializar cuando AlertService esté disponible
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.AlertService !== 'undefined') {
                console.log('[LEGO Framework] ConfirmationService cargado');
            }
        });
    } else {
        if (typeof window.AlertService !== 'undefined') {
            console.log('[LEGO Framework] ConfirmationService cargado');
        }
    }

    // Exponer globalmente
    window.ConfirmationService = ConfirmationService;
}
