/**
 * FormBuilder - Constructor de formularios agnóstico basado en esquema
 *
 * FILOSOFÍA LEGO:
 * Define formularios mediante esquema simple sin HTML manual.
 * Renderiza campos de forma consistente reutilizando componentes LEGO.
 *
 * USO:
 * const form = new FormBuilder({
 *     id: 'product-form',
 *     fields: {
 *         name: { type: 'text', label: 'Nombre', required: true },
 *         price: { type: 'number', label: 'Precio', min: 0 },
 *         description: { type: 'textarea', label: 'Descripción' }
 *     }
 * });
 *
 * const data = form.getData(); // Obtener valores
 * form.setData(product); // Cargar valores
 * form.setErrors(validationErrors); // Mostrar errores
 */

class FormBuilder {
    constructor(config) {
        this.config = {
            id: config.id || 'form-' + Date.now(),
            fields: config.fields || {},
            layout: config.layout || 'vertical', // vertical, grid, flex
            ...config
        };

        this.fieldElements = {};
        this.validators = {};
        this.errors = {};
        this.eventListeners = {};

        this._initializeFields();
    }

    /**
     * Inicializar referencias a campos
     */
    _initializeFields() {
        Object.keys(this.config.fields).forEach(fieldName => {
            const fieldConfig = this.config.fields[fieldName];

            // Buscar elemento del campo en el DOM
            const fieldElement = document.querySelector(
                `[name="${fieldName}"]` // input, textarea, select
            );

            if (fieldElement) {
                this.fieldElements[fieldName] = fieldElement;

                // Escuchar cambios para validación en tiempo real si está configurado
                if (fieldConfig.validateOnChange !== false) {
                    fieldElement.addEventListener('change', () => {
                        this._validateField(fieldName);
                    });
                    fieldElement.addEventListener('blur', () => {
                        this._validateField(fieldName);
                    });
                }
            } else {
                console.warn(`[FormBuilder] Campo no encontrado: ${fieldName}`);
            }
        });
    }

    /**
     * Obtener valor de un campo
     */
    getFieldValue(fieldName) {
        const element = this.fieldElements[fieldName];
        if (!element) return null;

        if (element.type === 'checkbox') {
            return element.checked;
        } else if (element.type === 'radio') {
            return element.value;
        } else if (element.tagName === 'SELECT') {
            return element.value;
        } else {
            return element.value;
        }
    }

    /**
     * Establecer valor de un campo
     */
    setFieldValue(fieldName, value) {
        const element = this.fieldElements[fieldName];
        if (!element) return false;

        try {
            if (element.type === 'checkbox') {
                element.checked = value === true;
            } else if (element.type === 'radio') {
                const radioGroup = document.querySelectorAll(`[name="${fieldName}"]`);
                radioGroup.forEach(radio => {
                    radio.checked = radio.value === value;
                });
            } else if (element.tagName === 'SELECT') {
                element.value = value;
            } else {
                element.value = value;
            }
            return true;
        } catch (error) {
            console.error(`[FormBuilder] Error al establecer valor de ${fieldName}:`, error);
            return false;
        }
    }

    /**
     * Obtener todos los datos del formulario
     */
    getData() {
        const data = {};
        Object.keys(this.fieldElements).forEach(fieldName => {
            data[fieldName] = this.getFieldValue(fieldName);
        });
        return data;
    }

    /**
     * Cargar datos en el formulario
     */
    setData(data) {
        Object.keys(data).forEach(fieldName => {
            if (this.fieldElements[fieldName]) {
                this.setFieldValue(fieldName, data[fieldName]);
            }
        });
        this.clearErrors();
        return true;
    }

    /**
     * Limpiar formulario
     */
    clear() {
        Object.keys(this.fieldElements).forEach(fieldName => {
            const element = this.fieldElements[fieldName];
            if (element.type === 'checkbox' || element.type === 'radio') {
                element.checked = false;
            } else {
                element.value = '';
            }
        });
        this.clearErrors();
        return true;
    }

    /**
     * Validar un campo individual
     */
    _validateField(fieldName) {
        const fieldConfig = this.config.fields[fieldName];
        if (!fieldConfig) return true;

        const value = this.getFieldValue(fieldName);
        const validator = this.validators[fieldName];

        if (!validator) return true;

        const errors = validator(value, fieldConfig);
        if (errors.length > 0) {
            this.setFieldError(fieldName, errors[0]);
            return false;
        } else {
            this.clearFieldError(fieldName);
            return true;
        }
    }

    /**
     * Establecer errores en campos
     */
    setErrors(errors) {
        // Limpiar errores previos
        Object.keys(this.fieldElements).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });

        // Aplicar nuevos errores
        Object.keys(errors).forEach(fieldName => {
            const errorMessages = errors[fieldName];
            const errorMessage = Array.isArray(errorMessages)
                ? errorMessages[0]
                : errorMessages;
            this.setFieldError(fieldName, errorMessage);
        });
    }

    /**
     * Establecer error en un campo
     */
    setFieldError(fieldName, errorMessage) {
        const element = this.fieldElements[fieldName];
        if (!element) return false;

        const container = element.closest('[class*="lego-"]') || element.parentElement;
        if (!container) return false;

        // Marcar contenedor como error
        container.classList.add('lego-field--error');

        // Buscar o crear elemento de error
        let errorElement = container.querySelector('[class*="error"]');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'lego-field-error';
            container.appendChild(errorElement);
        }

        errorElement.textContent = errorMessage;
        this.errors[fieldName] = errorMessage;

        return true;
    }

    /**
     * Limpiar error de un campo
     */
    clearFieldError(fieldName) {
        const element = this.fieldElements[fieldName];
        if (!element) return false;

        const container = element.closest('[class*="lego-"]') || element.parentElement;
        if (!container) return false;

        container.classList.remove('lego-field--error');

        const errorElement = container.querySelector('[class*="error"]');
        if (errorElement) {
            errorElement.textContent = '';
        }

        delete this.errors[fieldName];
        return true;
    }

    /**
     * Limpiar todos los errores
     */
    clearErrors() {
        Object.keys(this.fieldElements).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });
        this.errors = {};
    }

    /**
     * Registrar validador personalizado para un campo
     */
    addValidator(fieldName, validatorFn) {
        this.validators[fieldName] = validatorFn;
    }

    /**
     * Sistema de eventos personalizado
     */
    on(eventName, callback) {
        if (!this.eventListeners[eventName]) {
            this.eventListeners[eventName] = [];
        }
        this.eventListeners[eventName].push(callback);
    }

    /**
     * Desuscribirse de evento
     */
    off(eventName, callback) {
        if (!this.eventListeners[eventName]) return;
        const index = this.eventListeners[eventName].indexOf(callback);
        if (index > -1) {
            this.eventListeners[eventName].splice(index, 1);
        }
    }

    /**
     * Disparar evento
     */
    emit(eventName, data) {
        if (!this.eventListeners[eventName]) return;
        this.eventListeners[eventName].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error(`[FormBuilder] Error en listener ${eventName}:`, error);
            }
        });
    }

    /**
     * Habilitar/deshabilitar formulario
     */
    setDisabled(disabled = true) {
        Object.keys(this.fieldElements).forEach(fieldName => {
            const element = this.fieldElements[fieldName];
            element.disabled = disabled;
        });

        const form = document.getElementById(this.config.id);
        if (form) {
            if (disabled) {
                form.classList.add('lego-form--disabled');
            } else {
                form.classList.remove('lego-form--disabled');
            }
        }

        return true;
    }

    /**
     * Validar si hay errores
     */
    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    /**
     * Obtener todos los errores
     */
    getErrors() {
        return { ...this.errors };
    }

    /**
     * Desplazarse al primer campo con error
     */
    focusFirstError() {
        const firstErrorField = Object.keys(this.fieldElements).find(
            fieldName => this.errors[fieldName]
        );

        if (firstErrorField) {
            const element = this.fieldElements[firstErrorField];
            element.focus();
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return true;
        }
        return false;
    }

    /**
     * Enfocar un campo específico
     */
    focusField(fieldName) {
        const element = this.fieldElements[fieldName];
        if (element) {
            element.focus();
            return true;
        }
        return false;
    }

    /**
     * Marcar campo como completado (visual)
     */
    markFieldValid(fieldName) {
        const element = this.fieldElements[fieldName];
        if (!element) return false;

        const container = element.closest('[class*="lego-"]') || element.parentElement;
        if (container) {
            container.classList.add('lego-field--valid');
            container.classList.remove('lego-field--error');
        }
        return true;
    }

    /**
     * Marcar campo como incompleto (visual)
     */
    markFieldInvalid(fieldName) {
        const element = this.fieldElements[fieldName];
        if (!element) return false;

        const container = element.closest('[class*="lego-"]') || element.parentElement;
        if (container) {
            container.classList.add('lego-field--error');
            container.classList.remove('lego-field--valid');
        }
        return true;
    }
}
