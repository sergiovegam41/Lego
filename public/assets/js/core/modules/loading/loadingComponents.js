const createElementLoading = ( feedbackObject = {} ) => {
    let overlay = document.createElement('DIV');
    overlay.style.position = 'absolute';
    overlay.style.height = '100%';
    overlay.style.width = '100%';
    overlay.style.background = 'var(--color-white-17)';
    overlay.style.top = '0';
    overlay.style['backdrop-filter'] = 'blur(1.5px)';
    overlay.style['display'] = 'flex';
    overlay.style['justify-content'] = 'center';
    overlay.style['align-items'] = 'center';
    overlay.style['flex-direction'] = 'column';

    if(feedbackObject?.withMenu) overlay.style['z-index'] = '100';
    overlay.id = 'loadingOverlay';

    return overlay;
}; 

const createElementImgLoading = ( feedbackObject = {} ) => {
    let loadingImage = document.createElement('img');
    loadingImage.src = 'assets/images/loading/loading.svg';
    loadingImage.classList.add('spinner');
    return loadingImage;
};

export { createElementLoading, createElementImgLoading };
