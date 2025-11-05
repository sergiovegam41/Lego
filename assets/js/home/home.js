let context = {CONTEXT}
// console.log("Home console")

// console.log(context)

// Auto-load the dashboard when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's already an active module
    if (!window.moduleStore || !window.moduleStore.getActiveModule()) {
        // No active module - load the dashboard
        setTimeout(() => {
            if (window.openModule) {
                const HOST_NAME = window.location.origin;
                openModule('inicio', `${HOST_NAME}/component/inicio`, 'Inicio', {
                    url: `${HOST_NAME}/component/inicio`,
                    name: 'Inicio'
                });
            }
        }, 100); // Small delay to ensure all scripts are loaded
    }
});
