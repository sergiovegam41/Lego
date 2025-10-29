/**
 * ValidationEngine - Motor de validación agnóstico
 *
 * FILOSOFÍA LEGO:
 * Valida datos contra reglas definidas. No conoce entidades específicas.
 *
 * USO:
 * const validator = new ValidationEngine({
 *     name: { required: true, minLength: 3 },
 *     email: { required: true, pattern: 'email' },
 *     age: { type: 'number', min: 0, max: 150 }
 * });
 * const errors = validator.validate({ name: 'Juan', email: 'juan@example.com' });
 */

class ValidationEngine {
    constructor(rules = {}) {
        this.rules = rules;
        this.customValidators = {};
    }

    /**
     * Registrar validador personalizado
     */
    registerValidator(name, fn) {
        this.customValidators[name] = fn;
    }

    /**
     * Validar datos contra las reglas
     */
    validate(data) {
        const errors = {};

        for (const [field, fieldRules] of Object.entries(this.rules)) {
            const value = data[field];
            const fieldErrors = this.validateField(field, value, fieldRules);

            if (fieldErrors.length > 0) {
                errors[field] = fieldErrors;
            }
        }

        return errors;
    }

    /**
     * Validar un campo individual
     */
    validateField(field, value, rules) {
        const errors = [];

        // required
        if (rules.required && !value && value !== 0 && value !== false) {
            errors.push('Campo requerido');
            return errors; // No validar más si está vacío
        }

        if (!value && value !== 0 && value !== false) {
            return errors; // Campo no requerido y vacío, ok
        }

        // type
        if (rules.type) {
            if (!this.validateType(value, rules.type)) {
                errors.push(`Debe ser de tipo ${rules.type}`);
            }
        }

        // minLength
        if (rules.minLength && typeof value === 'string' && value.length < rules.minLength) {
            errors.push(`Mínimo ${rules.minLength} caracteres`);
        }

        // maxLength
        if (rules.maxLength && typeof value === 'string' && value.length > rules.maxLength) {
            errors.push(`Máximo ${rules.maxLength} caracteres`);
        }

        // min
        if (rules.min !== undefined && Number(value) < rules.min) {
            errors.push(`Valor mínimo: ${rules.min}`);
        }

        // max
        if (rules.max !== undefined && Number(value) > rules.max) {
            errors.push(`Valor máximo: ${rules.max}`);
        }

        // pattern (regex)
        if (rules.pattern) {
            if (!this.validatePattern(value, rules.pattern)) {
                errors.push(`Formato inválido: ${rules.pattern}`);
            }
        }

        // pattern by name (email, phone, etc)
        if (rules.patternName) {
            if (!this.validatePatternName(value, rules.patternName)) {
                errors.push(`${rules.patternName} inválido`);
            }
        }

        // custom validator
        if (rules.custom && this.customValidators[rules.custom]) {
            const customError = this.customValidators[rules.custom](value);
            if (customError) {
                errors.push(customError);
            }
        }

        return errors;
    }

    /**
     * Validar tipo de dato
     */
    validateType(value, type) {
        const typeMap = {
            'string': (v) => typeof v === 'string',
            'number': (v) => !isNaN(v) && typeof v !== 'boolean',
            'boolean': (v) => typeof v === 'boolean',
            'array': (v) => Array.isArray(v),
            'object': (v) => typeof v === 'object' && v !== null && !Array.isArray(v),
            'date': (v) => v instanceof Date || !isNaN(Date.parse(v))
        };

        const validator = typeMap[type];
        return validator ? validator(value) : true;
    }

    /**
     * Validar regex
     */
    validatePattern(value, pattern) {
        try {
            const regex = new RegExp(pattern);
            return regex.test(String(value));
        } catch (e) {
            console.error('[ValidationEngine] Regex inválida:', pattern);
            return false;
        }
    }

    /**
     * Validar patrones predefinidos
     */
    validatePatternName(value, patternName) {
        const patterns = {
            'email': /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            'phone': /^[\d\s\-\+\(\)]{7,}$/,
            'url': /^https?:\/\/.+/,
            'sku': /^[A-Z0-9\-]+$/,
            'slug': /^[a-z0-9\-]+$/,
            'uuid': /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i,
            'ipv4': /^(\d{1,3}\.){3}\d{1,3}$/
        };

        const pattern = patterns[patternName];
        return pattern ? pattern.test(String(value)) : true;
    }

    /**
     * ¿Tiene errores?
     */
    hasErrors(errors) {
        return Object.keys(errors).length > 0;
    }

    /**
     * Obtener mensaje de error para un campo
     */
    getErrorMessage(field, errors) {
        if (!errors[field]) return null;
        return Array.isArray(errors[field]) ? errors[field][0] : errors[field];
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ValidationEngine;
}
