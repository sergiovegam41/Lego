/**
 * SelectComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Toggle del dropdown
 * - Búsqueda en tiempo real
 * - Selección de opciones
 * - Manejo de teclado (arrows, enter, esc)
 * - Eventos custom
 */

(function() {
    const selects = document.querySelectorAll('.lego-select');

    selects.forEach((container) => {
        const trigger = container.querySelector('.lego-select__trigger');
        const dropdown = container.querySelector('.lego-select__dropdown');
        const search = container.querySelector('.lego-select__search');
        const options = container.querySelectorAll('.lego-select__option');
        const nativeSelect = container.querySelector('.lego-select__native');
        const valueDisplay = container.querySelector('.lego-select__value');
        const selectId = container.getAttribute('data-select-id');
        const isMultiple = nativeSelect?.hasAttribute('multiple');

        let isOpen = false;
        let selectedValues = [];
        let focusedIndex = -1;

        // Inicializar valores seleccionados
        if (nativeSelect) {
            const selectedOptions = Array.from(nativeSelect.selectedOptions);
            selectedValues = selectedOptions.map(opt => opt.value);
        }

        // Toggle dropdown
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown();
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                openDropdown();
            }
        });

        // Cerrar al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });

        // Búsqueda
        if (search) {
            search.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                filterOptions(searchTerm);
            });

            search.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeDropdown();
                    trigger.focus();
                }
            });
        }

        // Seleccionar opción
        options.forEach((option, index) => {
            option.addEventListener('click', () => {
                selectOption(option);
            });

            option.addEventListener('mouseenter', () => {
                focusedIndex = index;
                updateFocusedOption();
            });
        });

        // Navegación con teclado en dropdown
        dropdown.addEventListener('keydown', (e) => {
            const visibleOptions = Array.from(options).filter(opt =>
                !opt.classList.contains('lego-select__option--hidden')
            );

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                focusedIndex = Math.min(focusedIndex + 1, visibleOptions.length - 1);
                updateFocusedOption();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusedIndex = Math.max(focusedIndex - 1, 0);
                updateFocusedOption();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (focusedIndex >= 0 && focusedIndex < visibleOptions.length) {
                    selectOption(visibleOptions[focusedIndex]);
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            }
        });

        function toggleDropdown() {
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        }

        function openDropdown() {
            isOpen = true;
            container.classList.add('lego-select--open');
            if (search) {
                setTimeout(() => search.focus(), 100);
            }
        }

        function closeDropdown() {
            isOpen = false;
            container.classList.remove('lego-select--open');
            if (search) {
                search.value = '';
                filterOptions('');
            }
            focusedIndex = -1;
        }

        function selectOption(option) {
            const value = option.getAttribute('data-value');
            const labelElement = option.querySelector('.lego-select__option-label');
            const label = labelElement ? labelElement.textContent : option.textContent;

            if (isMultiple) {
                // Toggle selección múltiple
                const index = selectedValues.indexOf(value);
                if (index > -1) {
                    selectedValues.splice(index, 1);
                    option.classList.remove('lego-select__option--selected');
                    option.setAttribute('aria-selected', 'false');
                    // Mantener estructura HTML
                    option.innerHTML = `<span class="lego-select__option-label">${label}</span>`;
                } else {
                    selectedValues.push(value);
                    option.classList.add('lego-select__option--selected');
                    option.setAttribute('aria-selected', 'true');
                    option.innerHTML = `<span class="lego-select__option-label">${label}</span><span class="lego-select__checkmark">✓</span>`;
                }

                // Actualizar display
                const count = selectedValues.length;
                valueDisplay.textContent = count > 0
                    ? `${count} seleccionado${count > 1 ? 's' : ''}`
                    : trigger.getAttribute('data-placeholder') || 'Selecciona una opción';

                // Actualizar select nativo
                updateNativeSelect();
            } else {
                // Selección única
                selectedValues = [value];

                // Actualizar UI - mantener estructura HTML
                options.forEach(opt => {
                    opt.classList.remove('lego-select__option--selected');
                    opt.setAttribute('aria-selected', 'false');
                    const optLabelElement = opt.querySelector('.lego-select__option-label');
                    const optLabel = optLabelElement ? optLabelElement.textContent : opt.textContent;
                    opt.innerHTML = `<span class="lego-select__option-label">${optLabel}</span>`;
                });
                option.classList.add('lego-select__option--selected');
                option.setAttribute('aria-selected', 'true');
                option.innerHTML = `<span class="lego-select__option-label">${label}</span><span class="lego-select__checkmark">✓</span>`;

                valueDisplay.textContent = label;

                // Actualizar select nativo
                updateNativeSelect();

                // Cerrar dropdown
                closeDropdown();
            }

            // Emit custom event
            const event = new CustomEvent('lego:select-change', {
                detail: {
                    id: selectId,
                    value: isMultiple ? selectedValues : value,
                    label: isMultiple ? null : label
                },
                bubbles: true
            });
            container.dispatchEvent(event);

            // Trigger change en select nativo
            nativeSelect.dispatchEvent(new Event('change'));
        }

        function updateNativeSelect() {
            if (!nativeSelect) return;

            Array.from(nativeSelect.options).forEach(opt => {
                opt.selected = selectedValues.includes(opt.value);
            });
        }

        function filterOptions(searchTerm) {
            let hasVisibleOptions = false;

            options.forEach(option => {
                const labelElement = option.querySelector('.lego-select__option-label');
                const label = (labelElement ? labelElement.textContent : option.textContent).toLowerCase();
                const matches = label.includes(searchTerm);

                if (matches) {
                    option.classList.remove('lego-select__option--hidden');
                    hasVisibleOptions = true;
                } else {
                    option.classList.add('lego-select__option--hidden');
                }
            });

            // Mostrar mensaje si no hay resultados
            let emptyMessage = dropdown.querySelector('.lego-select__empty');
            if (!hasVisibleOptions && searchTerm) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('div');
                    emptyMessage.className = 'lego-select__empty';
                    emptyMessage.textContent = 'No se encontraron resultados';
                    dropdown.querySelector('.lego-select__options').appendChild(emptyMessage);
                }
            } else if (emptyMessage) {
                emptyMessage.remove();
            }
        }

        function updateFocusedOption() {
            const visibleOptions = Array.from(options).filter(opt =>
                !opt.classList.contains('lego-select__option--hidden')
            );

            visibleOptions.forEach((opt, index) => {
                if (index === focusedIndex) {
                    opt.style.backgroundColor = 'var(--bg-surface-secondary)';
                    opt.scrollIntoView({ block: 'nearest' });
                } else if (!opt.classList.contains('lego-select__option--selected')) {
                    opt.style.backgroundColor = '';
                }
            });
        }
    });

    // Exponer API pública
    window.LegoSelect = {
        getValue: (selectId) => {
            const container = document.querySelector(`[data-select-id="${selectId}"]`);
            if (!container) return null;
            const nativeSelect = container.querySelector('.lego-select__native');
            if (!nativeSelect) return null;

            if (nativeSelect.hasAttribute('multiple')) {
                return Array.from(nativeSelect.selectedOptions).map(opt => opt.value);
            }
            return nativeSelect.value;
        },
        setValue: (selectId, value) => {
            const container = document.querySelector(`[data-select-id="${selectId}"]`);
            if (!container) return;

            const options = container.querySelectorAll('.lego-select__option');
            const targetOption = Array.from(options).find(opt =>
                opt.getAttribute('data-value') === value
            );

            if (targetOption) {
                targetOption.click();
            }
        },
        open: (selectId) => {
            const container = document.querySelector(`[data-select-id="${selectId}"]`);
            if (!container) return;
            container.classList.add('lego-select--open');
        },
        close: (selectId) => {
            const container = document.querySelector(`[data-select-id="${selectId}"]`);
            if (!container) return;
            container.classList.remove('lego-select--open');
        }
    };
})();
