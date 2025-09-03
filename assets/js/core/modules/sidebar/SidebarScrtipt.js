export function activeMenu() {
    document.addEventListener('DOMContentLoaded', () => {
        addEventForToggle();
        
        if (window.themeManager) {
            const toggleButton = document.getElementById('theme-toggle');
            toggleButton?.addEventListener('click', () => {
                const newTheme = window.themeManager.toggle();
                // Update ion-icon if exists
                const ionIcon = document.querySelector('#theme-toggle ion-icon');
                if (ionIcon) {
                    ionIcon.name = newTheme === 'dark' ? 'sunny-outline' : 'moon-outline';
                }
            });

            // Subscribe to theme changes to update icon
            window.themeManager.subscribe((theme) => {
                const ionIcon = document.querySelector('#theme-toggle ion-icon');
                if (ionIcon) {
                    ionIcon.name = theme === 'dark' ? 'sunny-outline' : 'moon-outline';
                }
            });
        } else {
            // Fallback for legacy theme handling
            const toggleButton = document.getElementById('theme-toggle');
            let currentTheme = window.storageManager ? window.storageManager.getTheme() : localStorage.getItem('lego_theme') || 'dark';

            // Apply saved theme when loading the page
            if (currentTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                if (document.body) {
                    document.body.classList.add('dark');
                    document.body.classList.remove('light');
                }
                document.documentElement.style.colorScheme = 'dark';
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
                if (document.body) {
                    document.body.classList.add('light');
                    document.body.classList.remove('dark');
                }
                document.documentElement.style.colorScheme = 'light';
            }

            toggleButton?.addEventListener('click', () => {
                const isDarkMode = document.documentElement.classList.contains('dark');
                const newTheme = isDarkMode ? 'light' : 'dark';
                
                // Apply theme to html and body
                if (newTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                    if (document.body) {
                        document.body.classList.add('dark');
                        document.body.classList.remove('light');
                    }
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.classList.add('light');
                    document.documentElement.classList.remove('dark');
                    if (document.body) {
                        document.body.classList.add('light');
                        document.body.classList.remove('dark');
                    }
                    document.documentElement.style.colorScheme = 'light';
                }
                
                if (window.storageManager) {
                    window.storageManager.setTheme(newTheme);
                } else {
                    localStorage.setItem('lego_theme', newTheme);
                }
                
                const ionIcon = document.querySelector('#theme-toggle ion-icon');
                if (ionIcon) {
                    ionIcon.name = newTheme === 'dark' ? 'sunny-outline' : 'moon-outline';
                }
            });

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
            // Cuando el sidebar está cerrado
            sidebarShade.style.minWidth = "var(--sidebar-width-collapsed)";
        } else {
            // Cuando el sidebar está abierto - usar el ancho actual o por defecto
            const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
            if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
                sidebarShade.style.minWidth = currentWidth + "px";
            } else {
                sidebarShade.style.minWidth = "var(--sidebar-width)";
            }
        }
    });

    searchBtn.addEventListener("click", () => {
        sidebar.classList.remove("close");

        // Al abrir el sidebar con el botón de búsqueda - usar el ancho guardado o por defecto
        const currentWidth = window.storageManager ? window.storageManager.getSidebarWidth() : localStorage.getItem('lego_sidebar_width');
        if (currentWidth && currentWidth >= 200 && currentWidth <= 400) {
            sidebarShade.style.minWidth = currentWidth + "px";
        } else {
            sidebarShade.style.minWidth = "var(--sidebar-width)";
        }
    });
}

export function toggleSubMenu(element) {
    const submenu = element.nextElementSibling;
    const icon = element.querySelector("ion-icon");

    if (submenu.style.display === "block") {
        submenu.style.display = "none";
        icon.setAttribute("name", "chevron-forward-outline");
    } else {
        submenu.style.display = "block";
        icon.setAttribute("name", "chevron-down-outline");
    }
}