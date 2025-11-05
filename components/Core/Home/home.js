console.log("Home dashboard component loaded");

// Hacer que las tarjetas del dashboard y botones de acción sean clicables usando LEGO modules
document.addEventListener('DOMContentLoaded', function() {
    const activeModuleContainer = document.querySelector('#module-inicio');
    if (!activeModuleContainer) return;

    // Dashboard cards navigation
    const dashboardCards = activeModuleContainer.querySelectorAll('.dashboard-card[data-module-id]');
    dashboardCards.forEach(card => {
        card.addEventListener('click', function() {
            const moduleId = this.getAttribute('data-module-id');
            const moduleUrl = this.getAttribute('data-module-url');
            const moduleName = this.querySelector('h3')?.textContent || moduleId;

            if (moduleId && moduleUrl && window.openModule) {
                window.openModule(moduleId, moduleUrl, moduleName, {
                    url: moduleUrl,
                    name: moduleName
                });
            }
        });
    });

    // Quick action buttons navigation
    const actionButtons = activeModuleContainer.querySelectorAll('.action-button[data-module-id]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const moduleId = this.getAttribute('data-module-id');
            const moduleUrl = this.getAttribute('data-module-url');
            const moduleName = this.querySelector('span')?.textContent || moduleId;

            if (moduleId && moduleUrl && window.openModule) {
                window.openModule(moduleId, moduleUrl, moduleName, {
                    url: moduleUrl,
                    name: moduleName
                });
            }
        });
    });
});
