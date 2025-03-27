import { createElementImgLoading, createElementLoading } from './loadingComponents.js';

// Definir el comportamiento de la clase spinner
const defineSpinnerAnimation = () => {
    const style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = `
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);
};

// Llamar a la función para definir la animación al cargar el script
defineSpinnerAnimation();

const loading = (status, feedbackObject = {}) => {
    if (status) {
        let overlay = createElementLoading(feedbackObject);
        let loadingImage = createElementImgLoading(feedbackObject);

        // Agregar clase para animación de giro
        loadingImage.classList.add('spinner');

        overlay.appendChild(loadingImage);
        document.body.appendChild(overlay);
    } else {
        let overlay = document.querySelector('#loadingOverlay');
        if (Object.values(feedbackObject).length > 0) {   
            let image = overlay.querySelector('img');
            image.classList.remove('spinner'); // Detener animación
            image.style.height = '100px';
            image.style.width = '100px';
            if (feedbackObject?.success) {
                image.setAttribute('src', 'assets/images/loading/success.svg');   
            } else {
                image.setAttribute('src', 'assets/images/loading/error.svg');
            }
            let messageElement = document.createElement('h5');
            messageElement.innerText = feedbackObject?.message ?? '';
            messageElement.style.margin = '10px';
            overlay.appendChild(messageElement);
            
            setTimeout(() => {
                overlay.remove();
            }, 1000);
        } else {
            overlay.remove();
        }
    }
};

export { loading };