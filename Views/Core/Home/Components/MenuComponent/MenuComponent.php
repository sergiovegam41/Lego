<?php

namespace Views\Core\Home\Components\MenuComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;
use Views\Core\Home\Components\MenuComponent\features\MenuItemComponent\MenuItemComponent;
use Views\Core\Home\Dtos\MenuItemDto;

class MenuComponent extends CoreComponent
{

    use StringMethods;
    protected $config;
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [
        '/assets/css/core/sidebar/menu-style.css',
        '/assets/css/core/sidebar/mobile-menu.css',
        'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css'
    ];

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {

        $this->JS_PATHS_WITH_ARG[] = [

            new ScriptCoreDTO("components/Core/Home/Components/MenuComponent/menu-component.js?v=1", [
                "message" => "hello word desde menu component "
            ])

        ];

        $HOST_NAME = env('HOST_NAME');

        /**
         * @param MenuItemDto[] $MENU_LIST
         */

        $MENU_LIST = [
            new MenuItemDto(
                id: "1",
                name: "Inicio",
                url: $HOST_NAME . '/inicio',
                iconName: "home-outline"
            ),
            new MenuItemDto(
                id: "2",
                name: "Tablero",
                url: $HOST_NAME . '/tablero',
                iconName: "grid-outline"
            ),
            new MenuItemDto(
                id: "3",
                name: "Actividades recientes",
                url: $HOST_NAME . '/actividades',
                iconName: "time-outline"
            ),
            new MenuItemDto(
                id: "4",
                name: "Submenú Profundo",
                url: "#",
                iconName: "chevron-forward-outline",
                childs: [
                    new MenuItemDto(
                        id: "5",
                        name: "Opción 1",
                        url: $HOST_NAME . '/opcion1',
                        iconName: "document-text-outline"
                    ),
                    new MenuItemDto(
                        id: "6",
                        name: "Mas",
                        url: $HOST_NAME . '/opcion2',
                        iconName: "document-text-outline",
                        childs: [
                            new MenuItemDto(
                                id: "7",
                                name: "Submenú Profundo",
                                url: "#",
                                iconName: "chevron-forward-outline",
                                childs: [
                                    new MenuItemDto(
                                        id: "8",
                                        name: "Opción 1",
                                        url: $HOST_NAME . '/opcion1',
                                        iconName: "document-text-outline"
                                    ),
                                    new MenuItemDto(
                                        id: "9",
                                        name: "Opción 2",
                                        url: $HOST_NAME . '/opcion2',
                                        iconName: "document-text-outline"
                                    )
                                ]
                            ),
                        ]
                    )
                ]
            ),
            new MenuItemDto(
                id: "10",
                name: "Submenú Nivel 3",
                url: "#",
                iconName: "chevron-forward-outline",
                childs: [
                    new MenuItemDto(
                        id: "11",
                        name: "Opción A",
                        url: $HOST_NAME . '/opcionA',
                        iconName: "list-outline"
                    ),
                    new MenuItemDto(
                        id: "12",
                        name: "Opción B",
                        url: $HOST_NAME . '/opcionB',
                        iconName: "list-outline"
                    )
                ]
            ),
            new MenuItemDto(
                id: "13",
                name: "Submenú Nivel 4",
                url: "#",
                iconName: "chevron-forward-outline",
                childs: [
                    new MenuItemDto(
                        id: "14",
                        name: "Gestión de Usuarios",
                        url: $HOST_NAME . '/gestion-usuarios',
                        iconName: "people-outline"
                    )
                ]
            ),
            new MenuItemDto(
                id: "15",
                name: "Configuración",
                url: "#",
                iconName: "settings-outline",
                childs: [
                    new MenuItemDto(
                        id: "16",
                        name: "Reportes",
                        url: $HOST_NAME . '/reportes',
                        iconName: "stats-chart-outline"
                    )
                ]
            ),
            new MenuItemDto(
                id: "17",
                name: "Automatizacion",
                url: $HOST_NAME . '/view/automation',
                iconName: "flash-outline"
            ),
        ];




        /**
         * @param string $FINAL_MENU_LIST
         */


        $FINAL_MENU_LIST = "";

        /**
         * @param MenuItemDto $MenuItem
         */

        foreach ($MENU_LIST as $key => $MenuItem) {
            # code...

            $FINAL_MENU_LIST .= (new MenuItemComponent($MenuItem))->render();
        }


        return <<<HTML

        <!-- Mobile Menu Toggle Button -->
        <button class="mobile-menu-toggle mobile-only" id="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <ion-icon name="menu-outline"></ion-icon>
            <span class="sr-only">Open menu</span>
        </button>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobile-overlay" onclick="closeMobileMenu()"></div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar lego-sidebar" id="sidebar">
            <header class="sidebar-header">
                <div class="image-text">
                    <span class="image">
                        <img class="user-image" src="/assets/images/logo.png" alt="Lego Logo">
                    </span>

                    <div class="text logo-text">
                        <span class="name">Lego</span>
                        <span class="profession">Framework</span>
                    </div>
                </div>

                <!-- Desktop toggle (hidden on mobile) -->
                <i class='bx bx-chevron-right toggle desktop-only' id="desktop-toggle"></i>
                
                <!-- Mobile close button -->
                <button class="mobile-close mobile-only" onclick="closeMobileMenu()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </header>

            <div class="menu-bar lego-container">

                <hr class="menu-divider">
                
                <!-- Search Box -->
                <div class="search-box lego-menu-item">
                    <ion-icon class="icon" name="search-outline"></ion-icon>
                    <input type="text" placeholder="Search" id="search-menu" class="search-input">
                </div>
                
                <!-- Main Menu -->
                <div class="menu" id="sidebar_menu">
                    <div class="custom-menu" id="main-menu">
                        {$FINAL_MENU_LIST}
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="bottom-content space-responsive-y">
                    
                    <div class="theme-toggle lego-menu-item" id="theme-toggle">
                        <a href="#" class="menu-link">
                            <ion-icon class="icon" name="moon-outline"></ion-icon>
                            <span class="text nav-text">Theme</span>
                        </a>
                    </div>
                    
                    <hr class="menu-divider">

                    <div class="logout-item lego-menu-item">
                        <a href="{$HOST_NAME}/login" class="menu-link">
                            <ion-icon class="icon" name="log-out-outline"></ion-icon>
                            <span class="text nav-text">Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Desktop Resize Handle (hidden on mobile) -->
            <div class="sidebar-resize-handle desktop-only" 
                 onmousedown="startSidebarResize(event)"
                 id="resize-handle">
            </div>
        </nav>
        
        <script>
        // ===== Mobile Menu Functions =====
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            
            // Prevent body scroll when menu is open
            if (sidebar.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        function closeMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
        
        // Close menu when clicking menu items (mobile only)
        function handleMenuItemClick() {
            if (window.innerWidth < 768) {
                closeMobileMenu();
            }
        }
        
        // ===== Desktop Resize Functions =====
        let sidebarResizing = false;
        let resizeStartX = 0;
        let resizeStartWidth = 0;
        
        function startSidebarResize(e) {
            // Only enable resize on desktop
            if (window.innerWidth < 768) return;
            
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar || sidebar.classList.contains('close')) return;
            
            sidebarResizing = true;
            resizeStartX = e.clientX;
            resizeStartWidth = sidebar.offsetWidth;
            
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            
            e.preventDefault();
            e.stopPropagation();
        }
        
        document.addEventListener('mousemove', function(e) {
            if (!sidebarResizing || window.innerWidth < 768) return;
            
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar) return;
            
            const newWidth = resizeStartWidth + (e.clientX - resizeStartX);
            
            // Constrain between 200px and 400px
            if (newWidth >= 200 && newWidth <= 400) {
                sidebar.style.width = newWidth + 'px';
                
                // Update CSS variable
                const widthRem = newWidth / 16;
                document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
            }
        });
        
        document.addEventListener('mouseup', function() {
            if (sidebarResizing) {
                sidebarResizing = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                
                // Save width to localStorage (desktop only)
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && window.innerWidth >= 768) {
                    localStorage.setItem('sidebarWidth', sidebar.offsetWidth);
                }
            }
        });
        
        // ===== Responsive Handler =====
        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (window.innerWidth >= 768) {
                // Desktop: close mobile menu, restore desktop behavior
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
                
                // Restore saved width
                const savedWidth = localStorage.getItem('sidebarWidth');
                if (savedWidth && savedWidth >= 200 && savedWidth <= 400) {
                    const widthRem = savedWidth / 16;
                    document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                    sidebar.style.width = savedWidth + 'px';
                }
            } else {
                // Mobile: reset inline styles
                sidebar.style.width = '';
            }
        }
        
        // Listen for resize events
        window.addEventListener('resize', handleResize);
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            handleResize();
            
            // Add click handlers to menu items
            const menuItems = document.querySelectorAll('.menu_item_openable');
            menuItems.forEach(item => {
                item.addEventListener('click', handleMenuItemClick);
            });
        });
        </script>
  
     
    HTML;
    }
}
