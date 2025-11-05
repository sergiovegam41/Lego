/**
 * TextAreaComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Actualizar contador de caracteres
 * - Auto-resize del textarea
 * - Validación del lado del cliente
 * - Eventos custom
 */

(function() {
    const textareas = document.querySelectorAll('.lego-textarea');

    textareas.forEach((container) => {
        const textarea = container.querySelector('.lego-textarea__field');
        const counter = container.querySelector('.lego-textarea__counter');
        const textareaId = container.getAttribute('data-textarea-id');
        const isAutoResize = container.classList.contains('lego-textarea--auto-resize');

        if (!textarea) return;

        // Actualizar contador de caracteres
        if (counter) {
            const updateCounter = () => {
                const maxLength = textarea.getAttribute('maxlength');
                const currentLength = textarea.value.length;
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

            textarea.addEventListener('input', updateCounter);
            textarea.addEventListener('keyup', updateCounter);
        }

        // Auto-resize
        if (isAutoResize) {
            const adjustHeight = () => {
                // Reset height para recalcular
                textarea.style.height = 'auto';

                // Calcular nueva altura basada en scrollHeight
                const newHeight = Math.max(textarea.scrollHeight, 100); // mínimo 100px
                textarea.style.height = newHeight + 'px';
            };

            textarea.addEventListener('input', adjustHeight);
            textarea.addEventListener('change', adjustHeight);

            // Ajustar altura inicial
            adjustHeight();
        }

        // Validación en tiempo real
        textarea.addEventListener('blur', () => {
            validateTextarea(textarea, container);
        });

        // Limpiar error al comenzar a escribir
        textarea.addEventListener('input', () => {
            container.classList.remove('lego-textarea--error');
            const errorDiv = container.querySelector('.lego-textarea__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        });

        // Emit custom event al cambiar valor
        textarea.addEventListener('change', () => {
            const event = new CustomEvent('lego:textarea-change', {
                detail: {
                    id: textareaId,
                    value: textarea.value,
                    isValid: textarea.checkValidity()
                },
                bubbles: true
            });
            container.dispatchEvent(event);
        });

        // Tab para indentar en lugar de cambiar focus
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();

                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;

                // Insertar tab
                textarea.value = value.substring(0, start) + '\t' + value.substring(end);

                // Restaurar posición del cursor
                textarea.selectionStart = textarea.selectionEnd = start + 1;

                // Trigger input event para actualizar contador
                textarea.dispatchEvent(new Event('input'));
            }
        });
    });

    /**
     * Valida un textarea y muestra errores
     */
    function validateTextarea(textarea, container) {
        const errorDiv = container.querySelector('.lego-textarea__error');

        if (!textarea.checkValidity()) {
            container.classList.add('lego-textarea--error');
            textarea.classList.add('shake');

            if (errorDiv) {
                errorDiv.style.display = 'block';
            }

            setTimeout(() => {
                textarea.classList.remove('shake');
            }, 300);

            return false;
        } else {
            container.classList.remove('lego-textarea--error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
            return true;
        }
    }

    // Exponer API pública
    window.LegoTextArea = {
        validate: (textareaId) => {
            const container = document.querySelector(`[data-textarea-id="${textareaId}"]`);
            if (!container) return false;
            const textarea = container.querySelector('.lego-textarea__field');
            return validateTextarea(textarea, container);
        },
        getValue: (textareaId) => {
            const container = document.querySelector(`[data-textarea-id="${textareaId}"]`);
            if (!container) return null;
            const textarea = container.querySelector('.lego-textarea__field');
            return textarea ? textarea.value : null;
        },
        setValue: (textareaId, value) => {
            const container = document.querySelector(`[data-textarea-id="${textareaId}"]`);
            if (!container) return;
            const textarea = container.querySelector('.lego-textarea__field');
            if (textarea) {
                textarea.value = value;
                textarea.dispatchEvent(new Event('input'));
            }
        },
        setError: (textareaId, errorMessage) => {
            const container = document.querySelector(`[data-textarea-id="${textareaId}"]`);
            if (!container) return;

            container.classList.add('lego-textarea--error');
            const errorDiv = container.querySelector('.lego-textarea__error');
            if (errorDiv) {
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            }
        },
        clearError: (textareaId) => {
            const container = document.querySelector(`[data-textarea-id="${textareaId}"]`);
            if (!container) return;

            container.classList.remove('lego-textarea--error');
            const errorDiv = container.querySelector('.lego-textarea__error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    };
})();
