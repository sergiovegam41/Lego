/**
 * SelectView - Capa de presentación para SelectComponent
 *
 * FILOSOFÍA LEGO:
 * Responsabilidad única: manipular el DOM basándose en el estado del modelo.
 * No contiene lógica de negocio, solo renderizado y animaciones.
 *
 * RESPONSABILIDADES:
 * - Renderizar estado del modelo en el DOM
 * - Actualizar clases CSS según estado
 * - Sincronizar select nativo
 * - Manejar animaciones visuales
 *
 * CONSISTENCIA DIMENSIONAL:
 * "Las distancias importan" - mantiene consistencia visual
 * mediante clases CSS y variables de diseño.
 */

window.SelectView = class SelectView {
    constructor(container) {
        this.container = container;
        this.trigger = container.querySelector('.lego-select__trigger');
        this.dropdown = container.querySelector('.lego-select__dropdown');
        this.search = container.querySelector('.lego-select__search');
        this.optionsContainer = container.querySelector('.lego-select__options');
        this.nativeSelect = container.querySelector('.lego-select__native');
        this.valueDisplay = container.querySelector('.lego-select__value');

        this.optionElements = new Map(); // Map de value -> DOM element
        this.buildOptionElementsMap();
    }

    /**
     * Construir mapa de opciones para acceso rápido
     */
    buildOptionElementsMap() {
        const options = this.container.querySelectorAll('.lego-select__option');
        options.forEach(option => {
            const value = option.getAttribute('data-value');
            this.optionElements.set(value, option);
        });
    }

    /**
     * Actualizar display de valor seleccionado
     */
    updateValueDisplay(label, placeholder = 'Selecciona una opción') {
        this.valueDisplay.textContent = label || placeholder;
    }

    /**
     * Actualizar UI de opciones seleccionadas
     */
    updateSelectedOptions(selectedValues) {
        this.optionElements.forEach((element, value) => {
            const isSelected = selectedValues.includes(value);
            const labelElement = element.querySelector('.lego-select__option-label');
            const label = labelElement ? labelElement.textContent : element.textContent;

            if (isSelected) {
                element.classList.add('lego-select__option--selected');
                element.setAttribute('aria-selected', 'true');
                element.innerHTML = `<span class="lego-select__option-label">${label}</span><span class="lego-select__checkmark">✓</span>`;
            } else {
                element.classList.remove('lego-select__option--selected');
                element.setAttribute('aria-selected', 'false');
                element.innerHTML = `<span class="lego-select__option-label">${label}</span>`;
            }
        });
    }

    /**
     * Sincronizar select nativo con valores seleccionados
     */
    syncNativeSelect(selectedValues) {
        if (!this.nativeSelect) return;

        Array.from(this.nativeSelect.options).forEach(opt => {
            opt.selected = selectedValues.includes(opt.value);
        });

        // Trigger change event para formularios
        this.nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /**
     * Abrir dropdown
     */
    open() {
        this.container.classList.add('lego-select--open');

        // Focus en búsqueda si existe
        if (this.search) {
            setTimeout(() => this.search.focus(), 100);
        }
    }

    /**
     * Cerrar dropdown
     */
    close() {
        this.container.classList.remove('lego-select--open');

        // Limpiar búsqueda
        if (this.search) {
            this.search.value = '';
        }
    }

    /**
     * Filtrar opciones visibles
     */
    filterOptions(searchTerm) {
        const term = searchTerm.toLowerCase();
        let hasVisibleOptions = false;

        this.optionElements.forEach((element) => {
            const labelElement = element.querySelector('.lego-select__option-label');
            const label = (labelElement ? labelElement.textContent : element.textContent).toLowerCase();
            const matches = label.includes(term);

            if (matches) {
                element.classList.remove('lego-select__option--hidden');
                hasVisibleOptions = true;
            } else {
                element.classList.add('lego-select__option--hidden');
            }
        });

        // Mostrar/ocultar mensaje de "no hay resultados"
        this.updateEmptyMessage(!hasVisibleOptions && searchTerm);
    }

    /**
     * Mostrar mensaje de "no hay resultados"
     */
    updateEmptyMessage(show) {
        let emptyMessage = this.optionsContainer.querySelector('.lego-select__empty');

        if (show) {
            if (!emptyMessage) {
                emptyMessage = document.createElement('div');
                emptyMessage.className = 'lego-select__empty';
                emptyMessage.textContent = 'No se encontraron resultados';
                this.optionsContainer.appendChild(emptyMessage);
            }
        } else if (emptyMessage) {
            emptyMessage.remove();
        }
    }

    /**
     * Actualizar opción enfocada para navegación con teclado
     */
    updateFocusedOption(focusedIndex) {
        const visibleOptions = this.getVisibleOptions();

        visibleOptions.forEach((element, index) => {
            if (index === focusedIndex) {
                element.style.backgroundColor = 'var(--bg-surface-secondary)';
                element.scrollIntoView({ block: 'nearest' });
            } else if (!element.classList.contains('lego-select__option--selected')) {
                element.style.backgroundColor = '';
            }
        });
    }

    /**
     * Obtener opciones visibles (no ocultas por búsqueda)
     */
    getVisibleOptions() {
        return Array.from(this.optionElements.values()).filter(opt =>
            !opt.classList.contains('lego-select__option--hidden')
        );
    }

    /**
     * Obtener elemento de opción por índice visible
     */
    getOptionByIndex(index) {
        const visible = this.getVisibleOptions();
        return visible[index] || null;
    }

    /**
     * Emitir evento custom
     */
    emitCustomEvent(eventName, detail) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true
        });
        this.container.dispatchEvent(event);
    }

    /**
     * Focus en trigger
     */
    focusTrigger() {
        this.trigger.focus();
    }

    /**
     * Obtener valor del search input
     */
    getSearchValue() {
        return this.search ? this.search.value : '';
    }
}
