import { createElementImgLoading, createElementLoading } from './loadingComponents.js';

const loading = (status, feedbackObject = {}) => {

    if (status) {
        let overlay = createElementLoading(feedbackObject);
        let loadingImage = createElementImgLoading(feedbackObject);
        overlay.appendChild(loadingImage);
        document.body.appendChild(overlay);
    } else {
        let overlay = document.querySelector('#loadingOverlay');
        if (Object.values(feedbackObject).length > 0) {   
            let image = overlay.querySelector('img');
            image.classList.remove('spinner');
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