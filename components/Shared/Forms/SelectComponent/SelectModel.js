/**
 * SelectModel - Modelo de estado para SelectComponent
 *
 * FILOSOFÍA LEGO:
 * Single Source of Truth para el estado del select.
 * Mantiene consistencia entre UI y estado interno.
 *
 * RESPONSABILIDADES:
 * - Gestionar estado de selección (single/multiple)
 * - Validar cambios de estado
 * - Notificar observadores de cambios
 * - Mantener sincronización con select nativo
 *
 * PATRÓN OBSERVER:
 * Permite que View y Controller reaccionen a cambios de estado
 * sin acoplamiento directo.
 */

window.SelectModel = class SelectModel {
    constructor(options = {}) {
        this.id = options.id;
        this.isMultiple = options.isMultiple || false;
        this.options = options.options || [];
        this.selectedValues = options.selectedValues || [];
        this.isOpen = false;
        this.focusedIndex = -1;
        this.searchTerm = '';

        // Observer pattern
        this.observers = [];
    }

    /**
     * Observador para reaccionar a cambios de estado
     */
    subscribe(observer) {
        this.observers.push(observer);
    }

    /**
     * Notificar a todos los observadores
     */
    notify(event, data) {
        this.observers.forEach(observer => {
            if (observer[event]) {
                observer[event](data);
            }
        });
    }

    /**
     * Seleccionar una opción
     *
     * IMPORTANTE: Este es el único lugar donde se modifica selectedValues
     * No hay .click() hacks - cambio de estado directo y limpio
     */
    selectValue(value, options = {}) {
        const silent = options.silent || false;

        if (this.isMultiple) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                // Deseleccionar
                this.selectedValues.splice(index, 1);
            } else {
                // Seleccionar
                this.selectedValues.push(value);
            }
        } else {
            // Selección única
            this.selectedValues = [value];
        }

        // Notificar cambio
        if (!silent) {
            this.notify('onSelectionChange', {
                value: this.isMultiple ? this.selectedValues : value,
                selectedValues: this.selectedValues
            });
        }
    }

    /**
     * Establecer valor programáticamente
     *
     * API PÚBLICA sin .click() hack
     */
    setValue(value, options = {}) {
        const silent = options.silent || false;

        if (Array.isArray(value)) {
            // Multiple values
            this.selectedValues = [...value];
        } else {
            // Single value
            this.selectedValues = [value];
        }

        if (!silent) {
            this.notify('onSelectionChange', {
                value: this.isMultiple ? this.selectedValues : value,
                selectedValues: this.selectedValues
            });
        }
    }

    /**
     * Obtener valores actuales
     */
    getValue() {
        return this.isMultiple ? this.selectedValues : (this.selectedValues[0] || null);
    }

    /**
     * Verificar si un valor está seleccionado
     */
    isSelected(value) {
        return this.selectedValues.includes(value);
    }

    /**
     * Abrir/cerrar dropdown
     */
    setOpen(isOpen) {
        const wasOpen = this.isOpen;
        this.isOpen = isOpen;

        if (wasOpen !== isOpen) {
            this.notify('onOpenChange', { isOpen });
        }

        // Reset search al cerrar
        if (!isOpen) {
            this.setSearchTerm('');
        }
    }

    /**
     * Establecer término de búsqueda
     */
    setSearchTerm(term) {
        this.searchTerm = term;
        this.notify('onSearchChange', { searchTerm: term });
    }

    /**
     * Establecer índice enfocado para navegación con teclado
     */
    setFocusedIndex(index) {
        this.focusedIndex = index;
        this.notify('onFocusChange', { focusedIndex: index });
    }

    /**
     * Obtener opciones filtradas por término de búsqueda
     */
    getFilteredOptions() {
        if (!this.searchTerm) {
            return this.options;
        }

        const term = this.searchTerm.toLowerCase();
        return this.options.filter(option => {
            if (option.options) {
                // Es un grupo - filtrar subopciones
                return option.options.some(subOption =>
                    subOption.label.toLowerCase().includes(term)
                );
            }
            return option.label.toLowerCase().includes(term);
        });
    }

    /**
     * Obtener label de valor seleccionado
     */
    getSelectedLabel() {
        if (this.isMultiple && this.selectedValues.length > 0) {
            const count = this.selectedValues.length;
            return `${count} seleccionado${count > 1 ? 's' : ''}`;
        }

        if (this.selectedValues.length === 0) {
            return '';
        }

        const value = this.selectedValues[0];
        return this.findLabelByValue(value);
    }

    /**
     * Buscar label por value en opciones (incluyendo grupos)
     */
    findLabelByValue(value) {
        for (const option of this.options) {
            if (option.options) {
                // Es un grupo
                for (const subOption of option.options) {
                    if (subOption.value === value) {
                        return subOption.label;
                    }
                }
            } else {
                if (option.value === value) {
                    return option.label;
                }
            }
        }
        return '';
    }

    /**
     * Reset completo del estado
     */
    reset() {
        this.selectedValues = [];
        this.isOpen = false;
        this.focusedIndex = -1;
        this.searchTerm = '';

        this.notify('onReset', {});
    }
}
