/**
 * InputTextComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Actualizar contador de caracteres en tiempo real
 * - Validación del lado del cliente
 * - Manejo de eventos custom
 * - Animaciones de validación
 */

(function() {
    const inputs = document.querySelectorAll('.lego-input-text');

    inputs.forEach((container) => {
        const input = container.querySelector('.lego-input-text__field');
        const counter = container.querySelector('.lego-input-text__counter');
        const inputId = container.getAttribute('data-input-id');

        if (!input) return;

        // Actualizar contador de caracteres
        if (counter) {
            const updateCounter = () => {
                const maxLength = input.getAttribute('maxlength');
                const currentLength = input.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;

                // Cambiar color si se acerca al límite
                if (currentLength >= maxLength * 0.9) {
                    counter.style.color = 'var(--color-orange-600)';
                } else if (currentLength >= maxLength) {
                    counter.style.color = 'var(--color-red-600)';
                } else {
                    counter.style.color = 'var(--text-secondary)';
                }
            };

            input.addEventListener('input', updateCounter);
            input.addEventListener('keyup', updateCounter);
        }

        // Validación en tiempo real
        input.addEventListener('blur', () => {
            validateInput(input, container);
        });

        // Limpiar error al comenzar a escribir
        input.addEventListener('input', () => {
            container.classList.remove('lego-input-text--error');
            const errorDiv = container.querySelector('.lego-input-text__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        });

        // Emit custom event al cambiar valor
        input.addEventListener('change', () => {
            const event = new CustomEvent('lego:input-change', {
                detail: {
                    id: inputId,
                    value: input.value,
                    isValid: input.checkValidity()
                },
                bubbles: true
            });
            container.dispatchEvent(event);
        });
    });

    /**
     * Valida un input y muestra errores
     */
    function validateInput(input, container) {
        const errorDiv = container.querySelector('.lego-input-text__error');

        if (!input.checkValidity()) {
            container.classList.add('lego-input-text--error');
            input.classList.add('shake');

            if (errorDiv) {
                errorDiv.style.display = 'block';
            }

            setTimeout(() => {
                input.classList.remove('shake');
            }, 300);

            return false;
        } else {
            container.classList.remove('lego-input-text--error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
            return true;
        }
    }

    // Exponer API pública
    window.LegoInputText = {
        validate: (inputId) => {
            const container = document.querySelector(`[data-input-id="${inputId}"]`);
            if (!container) return false;
            const input = container.querySelector('.lego-input-text__field');
            return validateInput(input, container);
        },
        getValue: (inputId) => {
            const container = document.querySelector(`[data-input-id="${inputId}"]`);
            if (!container) return null;
            const input = container.querySelector('.lego-input-text__field');
            return input ? input.value : null;
        },
        setValue: (inputId, value) => {
            const container = document.querySelector(`[data-input-id="${inputId}"]`);
            if (!container) return;
            const input = container.querySelector('.lego-input-text__field');
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('input'));
            }
        },
        setError: (inputId, errorMessage) => {
            const container = document.querySelector(`[data-input-id="${inputId}"]`);
            if (!container) return;

            container.classList.add('lego-input-text--error');
            const errorDiv = container.querySelector('.lego-input-text__error');
            if (errorDiv) {
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            }
        },
        clearError: (inputId) => {
            const container = document.querySelector(`[data-input-id="${inputId}"]`);
            if (!container) return;

            container.classList.remove('lego-input-text--error');
            const errorDiv = container.querySelector('.lego-input-text__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    };
})();
