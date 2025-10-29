/**
 * ImageGalleryManager - Gestor de galería con FilePond
 *
 * FILOSOFÍA LEGO:
 * Funcionalidad adicional para FilePond: marcar imagen principal
 */

// Helper para agregar botones de imagen principal a FilePond
window.ImageGalleryFilePondHelpers = {

    /**
     * Agrega botón de "marcar como principal" a cada item de FilePond
     */
    addPrimaryButtons(pond, setPrimaryEndpoint, galleryId) {
        // Escuchar cuando se agreguen items
        pond.on('addfile', (error, file) => {
            if (error) return;

            // Esperar a que se renderice el item
            setTimeout(() => {
                this.addButtonToItem(pond, file, setPrimaryEndpoint, galleryId);
            }, 100);
        });

        // Agregar botones a items existentes
        setTimeout(() => {
            const files = pond.getFiles();
            files.forEach(file => {
                this.addButtonToItem(pond, file, setPrimaryEndpoint, galleryId);
            });
        }, 200);
    },

    /**
     * Agrega botón a un item específico
     */
    addButtonToItem(pond, file, setPrimaryEndpoint, galleryId) {
        const element = file.file instanceof Blob ?
            document.querySelector(`[data-filepond-item-id="${file.id}"]`) : null;

        if (!element) return;

        // Verificar si ya tiene el botón
        if (element.querySelector('.set-primary-button')) return;

        const isPrimary = file.getMetadata('is_primary') === true;
        const imageId = file.getMetadata('id') || file.serverId;

        // Si es la imagen principal, mostrar badge
        if (isPrimary) {
            const badge = document.createElement('div');
            badge.className = 'primary-image-badge';
            badge.innerHTML = `
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Principal
            `;
            element.appendChild(badge);
            return; // No agregar botón si ya es principal
        }

        // Crear botón de "marcar como principal"
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'set-primary-button';
        button.title = 'Marcar como imagen principal';
        button.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        `;

        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (!imageId) {
                AlertService.warning('Espera a que la imagen termine de subir');
                return;
            }

            try {
                button.disabled = true;
                const response = await fetch(setPrimaryEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image_id: imageId
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    AlertService.success('Imagen principal actualizada');

                    // Actualizar todos los items
                    this.refreshPrimaryBadges(pond, imageId, galleryId);
                } else {
                    AlertService.error(data.message || 'Error al marcar como principal');
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                AlertService.error('Error de red al marcar como principal');
                button.disabled = false;
            }
        });

        element.appendChild(button);
    },

    /**
     * Actualiza los badges de imagen principal
     */
    refreshPrimaryBadges(pond, newPrimaryId, galleryId) {
        const files = pond.getFiles();

        files.forEach(file => {
            const imageId = file.getMetadata('id') || file.serverId;
            const element = document.querySelector(`[data-filepond-item-id="${file.id}"]`);

            if (!element) return;

            // Remover badges y botones existentes
            const existingBadge = element.querySelector('.primary-image-badge');
            const existingButton = element.querySelector('.set-primary-button');

            if (existingBadge) existingBadge.remove();
            if (existingButton) existingButton.remove();

            // Si es la nueva imagen principal
            if (String(imageId) === String(newPrimaryId)) {
                file.setMetadata('is_primary', true);

                const badge = document.createElement('div');
                badge.className = 'primary-image-badge';
                badge.innerHTML = `
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Principal
                `;
                element.appendChild(badge);
            } else {
                // Agregar botón para marcar como principal
                file.setMetadata('is_primary', false);

                const setPrimaryEndpoint = window.filePondInstances[galleryId]?.config?.setPrimaryEndpoint;
                if (setPrimaryEndpoint) {
                    this.addButtonToItem(pond, file, setPrimaryEndpoint, galleryId);
                }
            }
        });
    }
};

console.log('[ImageGallery] Helper de FilePond cargado');
