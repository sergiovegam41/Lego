/**
 * ButtonComponent - Lógica de interacción
 *
 * RESPONSABILIDADES:
 * - Manejo de estados de loading
 * - Eventos custom
 * - Prevención de doble click
 * - Animaciones de feedback
 */

(function() {
    const buttons = document.querySelectorAll('.lego-button');

    buttons.forEach((button) => {
        const buttonId = button.getAttribute('data-button-id');

        // Prevenir doble click en estado loading
        button.addEventListener('click', (e) => {
            if (button.classList.contains('lego-button--loading')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Emit custom event
            const event = new CustomEvent('lego:button-click', {
                detail: {
                    id: buttonId,
                    text: button.querySelector('.lego-button__text')?.textContent
                },
                bubbles: true
            });
            button.dispatchEvent(event);
        });

        // Efecto ripple mejorado
        button.addEventListener('mousedown', (e) => {
            if (button.disabled || button.classList.contains('lego-button--loading')) {
                return;
            }

            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const ripple = document.createElement('span');
            ripple.className = 'lego-button__ripple';
            ripple.style.cssText = `
                position: absolute;
                left: ${x}px;
                top: ${y}px;
                width: 0;
                height: 0;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.4);
                transform: translate(-50%, -50%);
                pointer-events: none;
            `;

            button.appendChild(ripple);

            // Animar
            requestAnimationFrame(() => {
                ripple.style.transition = 'width 0.6s ease, height 0.6s ease, opacity 0.6s ease';
                ripple.style.width = '200px';
                ripple.style.height = '200px';
                ripple.style.opacity = '0';
            });

            // Remover después de la animación
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Exponer API pública
    window.LegoButton = {
        setLoading: (buttonId, loading = true) => {
            const button = document.querySelector(`[data-button-id="${buttonId}"]`);
            if (!button) return;

            if (loading) {
                button.classList.add('lego-button--loading');
                button.disabled = true;

                // Agregar spinner si no existe
                if (!button.querySelector('.lego-button__loader')) {
                    const loader = document.createElement('span');
                    loader.className = 'lego-button__loader';
                    loader.innerHTML = '<span class="lego-button__spinner"></span>';
                    button.insertBefore(loader, button.firstChild);
                }
            } else {
                button.classList.remove('lego-button--loading');
                button.disabled = false;

                // Remover spinner
                const loader = button.querySelector('.lego-button__loader');
                if (loader) {
                    loader.remove();
                }
            }
        },

        setDisabled: (buttonId, disabled = true) => {
            const button = document.querySelector(`[data-button-id="${buttonId}"]`);
            if (!button) return;

            if (disabled) {
                button.classList.add('lego-button--disabled');
                button.disabled = true;
            } else {
                button.classList.remove('lego-button--disabled');
                button.disabled = false;
            }
        },

        setText: (buttonId, text) => {
            const button = document.querySelector(`[data-button-id="${buttonId}"]`);
            if (!button) return;

            const textElement = button.querySelector('.lego-button__text');
            if (textElement) {
                textElement.textContent = text;
            }
        },

        click: (buttonId) => {
            const button = document.querySelector(`[data-button-id="${buttonId}"]`);
            if (button && !button.disabled) {
                button.click();
            }
        },

        /**
         * Simula un proceso async con loading state
         * @param {string} buttonId
         * @param {Function} asyncFn - Función async a ejecutar
         */
        async withLoading(buttonId, asyncFn) {
            this.setLoading(buttonId, true);
            try {
                const result = await asyncFn();
                return result;
            } finally {
                this.setLoading(buttonId, false);
            }
        }
    };
})();
