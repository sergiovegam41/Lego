/**
 * SelectController - Controlador para SelectComponent
 *
 * FILOSOFÍA LEGO:
 * Coordina Model y View sin lógica de negocio propia.
 * Traduce eventos de usuario en cambios de estado.
 *
 * RESPONSABILIDADES:
 * - Manejar eventos de usuario (click, teclado)
 * - Coordinar actualizaciones entre Model y View
 * - Implementar Observer para reaccionar a cambios de modelo
 *
 * SEPARACIÓN DE RESPONSABILIDADES:
 * Model: QUÉ cambió
 * View: CÓMO se muestra
 * Controller: CUÁNDO cambiar
 */

// SelectModel y SelectView ya están disponibles en window

window.SelectController = class SelectController {
    constructor(container, model, view) {
        this.container = container;
        this.model = model;
        this.view = view;

        // Suscribirse a cambios del modelo
        this.subscribeToModel();

        // Configurar event listeners
        this.setupEventListeners();
    }

    /**
     * Suscribirse a cambios del modelo (Observer pattern)
     */
    subscribeToModel() {
        this.model.subscribe({
            onSelectionChange: (data) => {
                // Actualizar UI
                this.view.updateSelectedOptions(data.selectedValues);
                this.view.updateValueDisplay(this.model.getSelectedLabel());
                this.view.syncNativeSelect(data.selectedValues);

                // Emit custom event
                this.view.emitCustomEvent('lego:select-change', {
                    id: this.model.id,
                    value: data.value,
                    selectedValues: data.selectedValues
                });

                // Cerrar dropdown si es selección única
                if (!this.model.isMultiple) {
                    this.model.setOpen(false);
                }
            },

            onOpenChange: (data) => {
                if (data.isOpen) {
                    this.view.open();
                } else {
                    this.view.close();
                }
            },

            onSearchChange: (data) => {
                this.view.filterOptions(data.searchTerm);
            },

            onFocusChange: (data) => {
                this.view.updateFocusedOption(data.focusedIndex);
            },

            onReset: () => {
                this.view.updateSelectedOptions([]);
                this.view.updateValueDisplay('');
                this.view.syncNativeSelect([]);
            }
        });
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Toggle dropdown al hacer click en trigger
        this.view.trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.handleToggle();
        });

        // Navegación con teclado en trigger
        this.view.trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.handleToggle();
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                this.model.setOpen(true);
            }
        });

        // Cerrar al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.model.setOpen(false);
            }
        });

        // Búsqueda
        if (this.view.search) {
            this.view.search.addEventListener('input', (e) => {
                this.model.setSearchTerm(e.target.value);
            });

            this.view.search.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.model.setOpen(false);
                    this.view.focusTrigger();
                }
            });
        }

        // Seleccionar opción
        this.view.optionElements.forEach((element, value) => {
            element.addEventListener('click', () => {
                this.handleSelectOption(value);
            });

            element.addEventListener('mouseenter', () => {
                const visibleOptions = this.view.getVisibleOptions();
                const index = visibleOptions.indexOf(element);
                this.model.setFocusedIndex(index);
            });
        });

        // Navegación con teclado en dropdown
        this.view.dropdown.addEventListener('keydown', (e) => {
            this.handleKeyboardNavigation(e);
        });
    }

    /**
     * Toggle dropdown
     */
    handleToggle() {
        this.model.setOpen(!this.model.isOpen);
    }

    /**
     * Seleccionar opción
     */
    handleSelectOption(value) {
        this.model.selectValue(value);
    }

    /**
     * Navegación con teclado
     */
    handleKeyboardNavigation(e) {
        const visibleOptions = this.view.getVisibleOptions();
        const currentIndex = this.model.focusedIndex;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const newIndex = Math.min(currentIndex + 1, visibleOptions.length - 1);
            this.model.setFocusedIndex(newIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const newIndex = Math.max(currentIndex - 1, 0);
            this.model.setFocusedIndex(newIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const option = this.view.getOptionByIndex(currentIndex);
            if (option) {
                const value = option.getAttribute('data-value');
                this.handleSelectOption(value);
            }
        } else if (e.key === 'Escape') {
            this.model.setOpen(false);
            this.view.focusTrigger();
        }
    }
}
