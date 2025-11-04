<?php

namespace Components\Core\Home\Components\MenuComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Providers\StringMethods;
use Components\Core\Home\Components\MenuComponent\features\MenuItemComponent\MenuItemComponent;
use Components\Core\Home\Collections\MenuItemCollection;

/**
 * MenuComponent - Sidebar navegable con menú multinivel
 *
 * FILOSOFÍA LEGO:
 * Componente declarativo estilo Flutter que acepta una colección de items
 * y los renderiza en un menú lateral con funcionalidades opcionales.
 *
 * PARÁMETROS:
 * @param MenuItemCollection $options - Items del menú (OBLIGATORIO)
 * @param string $title - Título del sidebar (OBLIGATORIO)
 * @param string $subtitle - Subtítulo/versión (OBLIGATORIO)
 * @param string $icon - Icono principal (OBLIGATORIO)
 * @param bool $collapsible - Permite colapsar el sidebar (OPCIONAL, default: false)
 * @param bool $resizable - Permite redimensionar el sidebar (OPCIONAL, default: false)
 * @param bool $searchable - Muestra buscador de items (OPCIONAL, default: false)
 *
 * EJEMPLO:
 * new MenuComponent(
 *     options: new MenuItemCollection(
 *         new MenuItemDto(id: "1", name: "Home", url: "/", iconName: "home")
 *     ),
 *     title: "Lego Framework",
 *     subtitle: "v1.0",
 *     icon: "menu-outline",
 *     searchable: true,
 *     resizable: true
 * )
 */
class MenuComponent extends CoreComponent
{
    use StringMethods;

    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [
        '/assets/css/core/sidebar/menu-style.css',
        'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css'
    ];

    public function __construct(
        public MenuItemCollection $options,
        public string $title,
        public string $subtitle,
        public string $icon,
        public bool $collapsible = false,
        public bool $resizable = false,
        public bool $searchable = false
    ) {}

    protected function component(): string
    {
        // Pasar la estructura completa del menú a JavaScript como fuente de verdad
        // Esto permite que window.lego.menu tenga acceso a la jerarquía completa
        // sin depender del DOM para construir breadcrumbs y otras features

        // Convertir a array (NO a JSON string) porque CoreComponent lo codificará después
        $menuStructureArray = array_map(fn($item) => $item->toArray(), iterator_to_array($this->options));

        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./menu-component.js", [
                "menuStructure" => $menuStructureArray
            ])
        ];

        // Renderizar items del menú desde $this->options
        $FINAL_MENU_LIST = "";
        foreach ($this->options as $menuItem) {
            $FINAL_MENU_LIST .= (new MenuItemComponent($menuItem))->render();
        }

        // Renderizar buscador si está habilitado
        $searchBox = $this->searchable ? $this->renderSearchBox() : '';

        // Renderizar resize handle si está habilitado
        $resizeHandle = $this->resizable ? $this->renderResizeHandle() : '';

        return <<<HTML

        <nav class="sidebar " id="sidebar">
            <header>
                <div class="image-text">
                    <span class="image">
                        <img class="user-image" src="/assets/images/logo.png" alt="">
                    </span>

                    <div class="text logo-text">
                        <span class="name">{$this->title}</span>
                        <span class="profession">{$this->subtitle}</span>
                    </div>

                </div>

                <i class='bx bx-chevron-right toggle'></i>
            </header>

            <div class="menu-bar">
                <hr>

                {$searchBox}

                <div class="menu" id="sidebar_menu">
                    <div class="custom-menu" id="">
                        {$FINAL_MENU_LIST}
                    </div>
                </div>

                <div class="bottom-content">
                    <li class="">
                        <a href="{$this->getLogoutUrl()}">
                            <ion-icon class ='icon'  name="log-out-outline"></ion-icon>
                            <span class="text nav-text">Logout</span>
                        </a>
                    </li>
                </div>
            </div>

            {$resizeHandle}
        </nav>

        <script>
        // cuando pongo directamente el codigo de menu-component.js aqui si funciona pero desde el componente no TODO revisar
        </script>


    HTML;
    }

    /**
     * Renderiza el buscador de items del menú
     */
    private function renderSearchBox(): string
    {
        return <<<HTML
        <li class="search-box">
            <ion-icon class ='icon' name="search-outline"></ion-icon>
            <input type="text" placeholder="Search" id="search-menu">
        </li>
        HTML;
    }

    /**
     * Renderiza el handle para redimensionar el sidebar
     */
    private function renderResizeHandle(): string
    {
        return <<<HTML
        <!-- Resize handle inside sidebar -->
        <div class="sidebar-resize-handle"
             style="position: absolute !important; top: 25% !important; right: -3px !important; width: 6px !important; height: 50% !important; background: transparent !important; cursor: col-resize !important; z-index: 9999 !important; border-radius: 0 3px 3px 0 !important; transition: all 0.2s ease !important;">
        </div>
        HTML;
    }

    /**
     * Retorna la URL de logout
     */
    private function getLogoutUrl(): string
    {
        $HOST_NAME = env('HOST_NAME');
        return $HOST_NAME . '/login';
    }
}
