/**
 * CheckboxComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Manejo de estado indeterminate
 * - Eventos custom
 * - Validación
 */

(function() {
    const checkboxes = document.querySelectorAll('.lego-checkbox');

    checkboxes.forEach((container) => {
        const input = container.querySelector('.lego-checkbox__input');
        const checkboxId = container.getAttribute('data-checkbox-id');

        if (!input) return;

        // Configurar estado indeterminate inicial
        if (container.classList.contains('lego-checkbox--indeterminate')) {
            input.indeterminate = true;
        }

        // Manejar cambios
        input.addEventListener('change', () => {
            // Si estaba indeterminate, quitarlo
            if (container.classList.contains('lego-checkbox--indeterminate')) {
                container.classList.remove('lego-checkbox--indeterminate');
                input.indeterminate = false;
            }

            // Limpiar error
            container.classList.remove('lego-checkbox--error');
            const errorDiv = container.querySelector('.lego-checkbox__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }

            // Emit custom event
            const event = new CustomEvent('lego:checkbox-change', {
                detail: {
                    id: checkboxId,
                    checked: input.checked,
                    value: input.value
                },
                bubbles: true
            });
            container.dispatchEvent(event);
        });

        // Validación al perder foco
        input.addEventListener('blur', () => {
            if (input.hasAttribute('required') && !input.checked) {
                container.classList.add('lego-checkbox--error');
                const errorDiv = container.querySelector('.lego-checkbox__error');
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }
            }
        });
    });

    // Exponer API pública
    window.LegoCheckbox = {
        isChecked: (checkboxId) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return false;
            const input = container.querySelector('.lego-checkbox__input');
            return input ? input.checked : false;
        },
        setChecked: (checkboxId, checked = true) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return;
            const input = container.querySelector('.lego-checkbox__input');
            if (input) {
                input.checked = checked;
                input.dispatchEvent(new Event('change'));
            }
        },
        setIndeterminate: (checkboxId, indeterminate = true) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return;
            const input = container.querySelector('.lego-checkbox__input');
            if (input) {
                input.indeterminate = indeterminate;
                if (indeterminate) {
                    container.classList.add('lego-checkbox--indeterminate');
                } else {
                    container.classList.remove('lego-checkbox--indeterminate');
                }
            }
        },
        toggle: (checkboxId) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return;
            const input = container.querySelector('.lego-checkbox__input');
            if (input) {
                input.checked = !input.checked;
                input.dispatchEvent(new Event('change'));
            }
        },
        setError: (checkboxId, errorMessage) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return;

            container.classList.add('lego-checkbox--error');
            const errorDiv = container.querySelector('.lego-checkbox__error');
            if (errorDiv) {
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            }
        },
        clearError: (checkboxId) => {
            const container = document.querySelector(`[data-checkbox-id="${checkboxId}"]`);
            if (!container) return;

            container.classList.remove('lego-checkbox--error');
            const errorDiv = container.querySelector('.lego-checkbox__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    };
})();
