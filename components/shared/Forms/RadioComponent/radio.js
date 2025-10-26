/**
 * RadioComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Manejo de grupos de radios
 * - Eventos custom
 * - Validación
 */

(function() {
    const radios = document.querySelectorAll('.lego-radio');

    radios.forEach((container) => {
        const input = container.querySelector('.lego-radio__input');
        const radioId = container.getAttribute('data-radio-id');
        const radioGroup = container.getAttribute('data-radio-group');

        if (!input) return;

        // Manejar cambios
        input.addEventListener('change', () => {
            // Limpiar error
            container.classList.remove('lego-radio--error');
            const errorDiv = container.querySelector('.lego-radio__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }

            // Limpiar errores de otros radios en el mismo grupo
            const groupRadios = document.querySelectorAll(`[data-radio-group="${radioGroup}"]`);
            groupRadios.forEach((groupContainer) => {
                groupContainer.classList.remove('lego-radio--error');
                const groupErrorDiv = groupContainer.querySelector('.lego-radio__error');
                if (groupErrorDiv) {
                    groupErrorDiv.style.display = 'none';
                }
            });

            // Emit custom event
            const event = new CustomEvent('lego:radio-change', {
                detail: {
                    id: radioId,
                    group: radioGroup,
                    value: input.value,
                    checked: input.checked
                },
                bubbles: true
            });
            container.dispatchEvent(event);
        });

        // Navegación con teclado (arrows)
        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                e.preventDefault();

                const groupRadios = Array.from(document.querySelectorAll(`[data-radio-group="${radioGroup}"]`));
                const currentIndex = groupRadios.findIndex(r => r.getAttribute('data-radio-id') === radioId);

                let nextIndex;
                if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                    nextIndex = (currentIndex + 1) % groupRadios.length;
                } else {
                    nextIndex = (currentIndex - 1 + groupRadios.length) % groupRadios.length;
                }

                const nextRadio = groupRadios[nextIndex];
                const nextInput = nextRadio.querySelector('.lego-radio__input');
                if (nextInput && !nextInput.disabled) {
                    nextInput.checked = true;
                    nextInput.focus();
                    nextInput.dispatchEvent(new Event('change'));
                }
            }
        });

        // Validación al perder foco
        input.addEventListener('blur', () => {
            if (input.hasAttribute('required')) {
                const groupInputs = document.querySelectorAll(`input[name="${radioGroup}"]`);
                const anyChecked = Array.from(groupInputs).some(inp => inp.checked);

                if (!anyChecked) {
                    container.classList.add('lego-radio--error');
                    const errorDiv = container.querySelector('.lego-radio__error');
                    if (errorDiv) {
                        errorDiv.style.display = 'block';
                    }
                }
            }
        });
    });

    // Exponer API pública
    window.LegoRadio = {
        getSelectedValue: (groupName) => {
            const checkedInput = document.querySelector(`input[name="${groupName}"]:checked`);
            return checkedInput ? checkedInput.value : null;
        },
        getSelectedId: (groupName) => {
            const checkedInput = document.querySelector(`input[name="${groupName}"]:checked`);
            if (!checkedInput) return null;
            return checkedInput.id;
        },
        setSelected: (radioId) => {
            const container = document.querySelector(`[data-radio-id="${radioId}"]`);
            if (!container) return;
            const input = container.querySelector('.lego-radio__input');
            if (input) {
                input.checked = true;
                input.dispatchEvent(new Event('change'));
            }
        },
        setSelectedByValue: (groupName, value) => {
            const input = document.querySelector(`input[name="${groupName}"][value="${value}"]`);
            if (input) {
                input.checked = true;
                input.dispatchEvent(new Event('change'));
            }
        },
        clearSelection: (groupName) => {
            const groupInputs = document.querySelectorAll(`input[name="${groupName}"]`);
            groupInputs.forEach(input => {
                input.checked = false;
            });
        },
        setError: (groupName, errorMessage) => {
            const groupRadios = document.querySelectorAll(`[data-radio-group="${groupName}"]`);
            groupRadios.forEach((container) => {
                container.classList.add('lego-radio--error');
                const errorDiv = container.querySelector('.lego-radio__error');
                if (errorDiv) {
                    errorDiv.textContent = errorMessage;
                    errorDiv.style.display = 'block';
                }
            });
        },
        clearError: (groupName) => {
            const groupRadios = document.querySelectorAll(`[data-radio-group="${groupName}"]`);
            groupRadios.forEach((container) => {
                container.classList.remove('lego-radio--error');
                const errorDiv = container.querySelector('.lego-radio__error');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }
            });
        }
    };
})();
