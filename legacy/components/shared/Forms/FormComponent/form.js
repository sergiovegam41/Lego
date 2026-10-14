/**
 * FormComponent - Lógica de formulario
 *
 * RESPONSABILIDADES:
 * - Validación del formulario
 * - Prevención de doble submit
 * - Manejo de mensajes de éxito/error
 * - Eventos custom
 * - Integración con APIs de componentes
 */

(function() {
    const forms = document.querySelectorAll('.lego-form');

    forms.forEach((form) => {
        const formId = form.getAttribute('data-form-id');
        let isSubmitting = false;

        // Manejar submit
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Prevenir doble submit
            if (isSubmitting) {
                return false;
            }

            // Validar formulario
            if (!validateForm(form)) {
                showError(form, 'Por favor, corrige los errores en el formulario.');

                // Emit validation failed event
                const validationEvent = new CustomEvent('lego:form-validation-failed', {
                    detail: { id: formId },
                    bubbles: true
                });
                form.dispatchEvent(validationEvent);

                return false;
            }

            // Marcar como submitting
            isSubmitting = true;
            form.classList.add('lego-form--loading', 'submitting');

            // Deshabilitar botones submit
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                if (window.LegoButton) {
                    const btnId = btn.getAttribute('data-button-id');
                    if (btnId) window.LegoButton.setLoading(btnId, true);
                }
            });

            // Emit submit event
            const submitEvent = new CustomEvent('lego:form-submit', {
                detail: {
                    id: formId,
                    data: getFormData(form)
                },
                bubbles: true,
                cancelable: true
            });

            const shouldContinue = form.dispatchEvent(submitEvent);

            if (!shouldContinue) {
                // El evento fue cancelado, resetear estado
                resetSubmitState(form, submitButtons);
                isSubmitting = false;
                return false;
            }

            // Si hay action definido, enviar normalmente
            if (form.hasAttribute('action') && form.getAttribute('action')) {
                // El formulario se enviará normalmente
                return true;
            } else {
                // No hay action, resetear estado después de un tiempo
                setTimeout(() => {
                    resetSubmitState(form, submitButtons);
                    isSubmitting = false;
                }, 1000);
            }
        });

        // Limpiar mensajes al editar campos
        form.addEventListener('input', () => {
            hideMessages(form);
        });
    });

    /**
     * Valida todos los campos del formulario
     */
    function validateForm(form) {
        let isValid = true;

        // Validación nativa HTML5
        if (!form.checkValidity()) {
            isValid = false;
        }

        // Validar inputs personalizados (usando APIs de componentes)
        const inputs = form.querySelectorAll('[data-input-id]');
        inputs.forEach((container) => {
            const inputId = container.getAttribute('data-input-id');
            if (window.LegoInputText && !window.LegoInputText.validate(inputId)) {
                isValid = false;
            }
        });

        // Validar textareas
        const textareas = form.querySelectorAll('[data-textarea-id]');
        textareas.forEach((container) => {
            const textareaId = container.getAttribute('data-textarea-id');
            if (window.LegoTextArea && !window.LegoTextArea.validate(textareaId)) {
                isValid = false;
            }
        });

        // Validar checkboxes requeridos
        const requiredCheckboxes = form.querySelectorAll('.lego-checkbox__input[required]');
        requiredCheckboxes.forEach((checkbox) => {
            if (!checkbox.checked) {
                isValid = false;
                const container = checkbox.closest('.lego-checkbox');
                if (container && window.LegoCheckbox) {
                    const checkboxId = container.getAttribute('data-checkbox-id');
                    window.LegoCheckbox.setError(checkboxId, 'Este campo es requerido');
                }
            }
        });

        // Validar radio groups requeridos
        const radioGroups = new Set();
        form.querySelectorAll('.lego-radio__input[required]').forEach((radio) => {
            radioGroups.add(radio.name);
        });

        radioGroups.forEach((groupName) => {
            const checkedRadio = form.querySelector(`input[name="${groupName}"]:checked`);
            if (!checkedRadio) {
                isValid = false;
                if (window.LegoRadio) {
                    window.LegoRadio.setError(groupName, 'Debes seleccionar una opción');
                }
            }
        });

        return isValid;
    }

    /**
     * Obtiene todos los datos del formulario
     */
    function getFormData(form) {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                // Si ya existe, convertir a array
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }

        return data;
    }

    /**
     * Muestra mensaje de error
     */
    function showError(form, message) {
        const messagesContainer = form.querySelector('.lego-form__messages');
        const errorDiv = form.querySelector('.lego-form__error');

        if (messagesContainer && errorDiv) {
            messagesContainer.style.display = 'block';
            errorDiv.style.display = 'block';
            errorDiv.textContent = message;
        }
    }

    /**
     * Muestra mensaje de éxito
     */
    function showSuccess(form, message) {
        const messagesContainer = form.querySelector('.lego-form__messages');
        const successDiv = form.querySelector('.lego-form__success');

        if (messagesContainer && successDiv) {
            messagesContainer.style.display = 'block';
            successDiv.style.display = 'block';
            successDiv.textContent = message;
        }
    }

    /**
     * Oculta todos los mensajes
     */
    function hideMessages(form) {
        const messagesContainer = form.querySelector('.lego-form__messages');
        const errorDiv = form.querySelector('.lego-form__error');
        const successDiv = form.querySelector('.lego-form__success');

        if (messagesContainer) messagesContainer.style.display = 'none';
        if (errorDiv) errorDiv.style.display = 'none';
        if (successDiv) successDiv.style.display = 'none';
    }

    /**
     * Resetea el estado de submit
     */
    function resetSubmitState(form, submitButtons) {
        form.classList.remove('lego-form--loading', 'submitting');

        submitButtons.forEach(btn => {
            btn.disabled = false;
            if (window.LegoButton) {
                const btnId = btn.getAttribute('data-button-id');
                if (btnId) window.LegoButton.setLoading(btnId, false);
            }
        });
    }

    // Exponer API pública
    window.LegoForm = {
        validate: (formId) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return false;
            return validateForm(form);
        },
        getData: (formId) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return null;
            return getFormData(form);
        },
        reset: (formId) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return;
            form.reset();
            hideMessages(form);
        },
        showError: (formId, message) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return;
            showError(form, message);
        },
        showSuccess: (formId, message) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return;
            showSuccess(form, message);
        },
        hideMessages: (formId) => {
            const form = document.querySelector(`[data-form-id="${formId}"]`);
            if (!form) return;
            hideMessages(form);
        }
    };
})();
