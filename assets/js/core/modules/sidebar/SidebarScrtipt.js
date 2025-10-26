export function activeMenu() {
        addEventForToggle();
        
        // Add click handlers for parent menu items
        document.querySelectorAll('.menu-parent').forEach(parent => {
            parent.querySelector('a').addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Toggle active class
                parent.classList.toggle('active');

                // Close other open menus
                document.querySelectorAll('.menu-parent.active').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('active');
                    }
                });
            });
        });
}

function addEventForToggle() {
    const body = document.querySelector('body'),
          sidebar = body.querySelector('nav'),
          toggle = body.querySelector(".toggle"),
          searchBtn = body.querySelector(".search-box"),
          sidebarShade = document.querySelector('#content-sidebar-shade');

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");

        if (sidebar.classList.contains("close")) {
            // Cuando el sidebar está cerrado - usar valor real de CSS variable
            const collapsedWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width-collapsed');
            const collapsedWidthPx = parseFloat(collapsedWidth) * 16; // Convertir rem a px
            
            sidebarShade.style.minWidth = collapsedWidthPx + "px";
            sidebarShade.style.width = collapsedWidthPx + "px";
            
            // Agregar clase para CSS adicional si es necesario
            document.body.classList.add('sidebar-collapsed');
        } else {
            // Cuando el sidebar está abierto - usar el ancho actual o por defecto
            const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
            if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
                sidebarShade.style.minWidth = currentWidth + "px";
                sidebarShade.style.width = currentWidth + "px";
            } else {
                const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
                const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
                sidebarShade.style.minWidth = defaultWidthPx + "px";
                sidebarShade.style.width = defaultWidthPx + "px";
            }
            
            // Remover clase CSS
            document.body.classList.remove('sidebar-collapsed');
        }
    });

    searchBtn.addEventListener("click", () => {
        sidebar.classList.remove("close");

        // Al abrir el sidebar con el botón de búsqueda - usar el ancho guardado o por defecto
        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
            sidebarShade.style.minWidth = currentWidth + "px";
            sidebarShade.style.width = currentWidth + "px";
        } else {
            const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
            sidebarShade.style.minWidth = defaultWidthPx + "px";
            sidebarShade.style.width = defaultWidthPx + "px";
        }
        
        // Remover clase CSS
        document.body.classList.remove('sidebar-collapsed');
    });
    
    // Inicializar el estado correcto del shade al cargar la página
    initializeSidebarShade();
}

/**
 * Inicializa el shade del sidebar con el ancho correcto
 * basado en el estado actual del sidebar (collapsed o no)
 */
function initializeSidebarShade() {
    const sidebar = document.querySelector('nav.sidebar');
    const sidebarShade = document.querySelector('#content-sidebar-shade');
    
    if (!sidebar || !sidebarShade) return;
    
    if (sidebar.classList.contains("close")) {
        // Sidebar está colapsado
        const collapsedWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width-collapsed');
        const collapsedWidthPx = parseFloat(collapsedWidth) * 16; // Convertir rem a px
        
        sidebarShade.style.minWidth = collapsedWidthPx + "px";
        sidebarShade.style.width = collapsedWidthPx + "px";
        document.body.classList.add('sidebar-collapsed');
    } else {
        // Sidebar está abierto - usar ancho guardado o por defecto
        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
            sidebarShade.style.minWidth = currentWidth + "px";
            sidebarShade.style.width = currentWidth + "px";
        } else {
            const defaultWidth = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
            const defaultWidthPx = parseFloat(defaultWidth) * 16; // Convertir rem a px
            sidebarShade.style.minWidth = defaultWidthPx + "px";
            sidebarShade.style.width = defaultWidthPx + "px";
        }
        document.body.classList.remove('sidebar-collapsed');
    }
}

export function toggleSubMenu(element) {
    const submenu = element.nextElementSibling;
    const chevronIcon = element.querySelector(".icon_menu_chevron");

    if (submenu.style.display === "block") {
        submenu.style.display = "none";
        if (chevronIcon) {
            chevronIcon.setAttribute("name", "chevron-forward-outline");
        }
    } else {
        submenu.style.display = "block";
        if (chevronIcon) {
            chevronIcon.setAttribute("name", "chevron-down-outline");
        }
    }
}