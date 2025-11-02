<?php

namespace Components\Core\Home\Components\MenuComponent\features\MenuItemComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Providers\StringMethods;
use Components\Core\Home\Dtos\MenuItemDto;

/**
 * MenuItemComponent - Item individual del menú (recursivo para submenús)
 *
 * PROPÓSITO:
 * Renderiza un item del menú que puede ser:
 * - Item simple con link
 * - Item con submenú (recursivo)
 *
 * CARACTERÍSTICAS:
 * - Soporte multinivel (recursivo)
 * - Iconos personalizados por item
 * - Auto-manejo de niveles de anidación
 *
 * EJEMPLO:
 * new MenuItemComponent(
 *     item: new MenuItemDto(
 *         id: "1",
 *         name: "Home",
 *         url: "/",
 *         iconName: "home-outline"
 *     )
 * )
 */
class MenuItemComponent extends CoreComponent
{
    use StringMethods;

    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct(
        public MenuItemDto $item
    ) {}

    protected function component(): string
    {
        $id = $this->item->id;
        $name = $this->item->name;
        $url = $this->item->url;
        $iconName = $this->item->iconName ?? 'document-text-outline';
        $this->item->level = $this->item->level + 1;
        $level = $this->item->level;
        $levelAux = $level - 1;

        // Si no tiene hijos, es un elemento final
        if (empty($this->item->childs)) {
            return <<<HTML
            <div class="custom-menu-section menu_item_openable" moduleId="{$id}" moduleUrl="{$url}" data-menu-item-id="{$id}">
                <button class="menu-close-button" title="Cerrar">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
                <button class="custom-button level-{$levelAux}">
                    <ion-icon name="{$iconName}" class="icon_menu"></ion-icon>
                    <p class="text_menu_option">{$name}</p>
                    <div class="menu-state-indicator"></div>
                </button>
            </div>
            HTML;
        } 

        // Si tiene hijos, es un menú desplegable
        else {
            $FINAL_LIST = "";

            foreach ($this->item->childs as $childItem) {
                $childItem->level = $level;
                $FINAL_LIST .= (new MenuItemComponent($childItem))->render();
            }

            return <<<HTML
            <div class="custom-menu-section ">
                <div class="custom-menu-title level-{$levelAux}" onclick="toggleSubMenu(this)">
                    <ion-icon name="{$iconName}" class="icon_menu icon_menu_parent"></ion-icon>
                    <p class="text_menu_option" >{$name}</p>
                    <ion-icon name="chevron-forward-outline" class="icon_menu icon_menu_chevron"></ion-icon>
                </div>
                <div class="custom-submenu section-level-{$level}">
                    {$FINAL_LIST}
                </div>
            </div>
            HTML;


        }
    }
}
