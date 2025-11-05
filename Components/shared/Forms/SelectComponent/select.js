/**
 * SelectComponent - Inicialización y API pública (REFACTORIZADO MVC)
 *
 * FILOSOFÍA LEGO:
 * Arquitectura MVC limpia que separa responsabilidades.
 * API pública sin .click() hacks - cambios de estado directos.
 *
 * MEJORAS vs VERSIÓN ANTERIOR:
 * ✅ Sin .click() hacks en setValue()
 * ✅ Separación clara Model/View/Controller
 * ✅ Modo silencioso para cambios programáticos
 * ✅ Observer pattern para sincronización
 * ✅ API pública más robusta y predecible
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan" - todos los componentes Select
 * usan la misma arquitectura, manteniendo proporciones conceptuales.
 */

// SelectModel, SelectView y SelectController ya están disponibles en window

// Store global de instancias para API pública
const selectInstances = new Map();

/**
 * Inicializar todos los selects en la página
 */
function initializeSelects() {
    const containers = document.querySelectorAll('.lego-select');

    containers.forEach((container) => {
        const selectId = container.getAttribute('data-select-id');
        const nativeSelect = container.querySelector('.lego-select__native');
        const isMultiple = nativeSelect?.hasAttribute('multiple');

        // Extraer opciones del DOM
        const options = extractOptionsFromDOM(container);

        // Extraer valores seleccionados iniciales
        const selectedValues = nativeSelect
            ? Array.from(nativeSelect.selectedOptions).map(opt => opt.value)
            : [];

        // Crear instancias MVC
        const model = new window.SelectModel({
            id: selectId,
            isMultiple,
            options,
            selectedValues
        });

        const view = new window.SelectView(container);
        const controller = new window.SelectController(container, model, view);

        // Guardar instancia para API pública
        selectInstances.set(selectId, { model, view, controller });

        // Sincronizar estado inicial
        view.updateSelectedOptions(selectedValues);
        view.updateValueDisplay(model.getSelectedLabel());
    });
}

/**
 * Extraer opciones del DOM para construir modelo
 */
function extractOptionsFromDOM(container) {
    const options = [];
    const optionElements = container.querySelectorAll('.lego-select__option');
    const groups = container.querySelectorAll('.lego-select__group');

    if (groups.length > 0) {
        // Hay grupos
        groups.forEach(group => {
            const groupLabel = group.querySelector('.lego-select__group-label')?.textContent || '';
            const groupOptions = [];

            group.querySelectorAll('.lego-select__option').forEach(opt => {
                groupOptions.push({
                    value: opt.getAttribute('data-value'),
                    label: opt.querySelector('.lego-select__option-label')?.textContent || opt.textContent
                });
            });

            options.push({
                label: groupLabel,
                options: groupOptions
            });
        });
    } else {
        // Sin grupos
        optionElements.forEach(opt => {
            options.push({
                value: opt.getAttribute('data-value'),
                label: opt.querySelector('.lego-select__option-label')?.textContent || opt.textContent
            });
        });
    }

    return options;
}

/**
 * API Pública - Sin .click() hacks
 */
window.LegoSelect = {
    /**
     * Obtener valor actual
     * @param {string} selectId - ID del select
     * @returns {string|string[]|null}
     */
    getValue: (selectId) => {
        const instance = selectInstances.get(selectId);
        if (!instance) {
            console.warn(`LegoSelect: No se encontró select con id "${selectId}"`);
            return null;
        }
        return instance.model.getValue();
    },

    /**
     * Establecer valor programáticamente
     * SIN .click() hack - cambio de estado directo
     *
     * @param {string} selectId - ID del select
     * @param {string|string[]} value - Valor o valores a establecer
     * @param {Object} options - Opciones
     * @param {boolean} options.silent - Si es true, no emite eventos (default: false)
     */
    setValue: (selectId, value, options = {}) => {
        const instance = selectInstances.get(selectId);
        if (!instance) {
            console.warn(`LegoSelect: No se encontró select con id "${selectId}"`);
            return;
        }

        // Cambio de estado directo sin .click()
        instance.model.setValue(value, options);

        // Si no es silent, la vista se actualiza automáticamente via Observer
        // Si es silent, actualizamos vista manualmente
        if (options.silent) {
            const selectedValues = Array.isArray(value) ? value : [value];
            instance.view.updateSelectedOptions(selectedValues);
            instance.view.updateValueDisplay(instance.model.getSelectedLabel());
            instance.view.syncNativeSelect(selectedValues);
        }
    },

    /**
     * Abrir dropdown programáticamente
     */
    open: (selectId) => {
        const instance = selectInstances.get(selectId);
        if (!instance) {
            console.warn(`LegoSelect: No se encontró select con id "${selectId}"`);
            return;
        }
        instance.model.setOpen(true);
    },

    /**
     * Cerrar dropdown programáticamente
     */
    close: (selectId) => {
        const instance = selectInstances.get(selectId);
        if (!instance) {
            console.warn(`LegoSelect: No se encontró select con id "${selectId}"`);
            return;
        }
        instance.model.setOpen(false);
    },

    /**
     * Reset select a estado inicial
     */
    reset: (selectId) => {
        const instance = selectInstances.get(selectId);
        if (!instance) {
            console.warn(`LegoSelect: No se encontró select con id "${selectId}"`);
            return;
        }
        instance.model.reset();
    },

    /**
     * Obtener instancia completa (model, view, controller)
     * Útil para debugging o extensiones
     */
    getInstance: (selectId) => {
        return selectInstances.get(selectId) || null;
    }
};

// Auto-inicializar cuando el DOM esté listo
// IMPORTANTE: En Lego, los scripts se cargan dinámicamente, por lo que
// necesitamos un pequeño delay para asegurar que todas las dependencias
// (SelectModel, SelectView, SelectController) estén completamente cargadas
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSelects);
} else {
    // Dar tiempo para que todos los scripts se carguen completamente
    setTimeout(initializeSelects, 50);
}

// Exponer función de inicialización globalmente
window.initializeSelects = initializeSelects;
