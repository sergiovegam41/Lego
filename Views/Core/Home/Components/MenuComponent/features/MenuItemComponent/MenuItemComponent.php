<?php

namespace Views\Core\Home\Components\MenuComponent\features\MenuItemComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\providers\StringMethods;
use Views\Core\Home\Dtos\MenuItemDto;

class MenuItemComponent extends CoreComponent 
{
    use StringMethods;

    protected $config;
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [];

    public function __construct(MenuItemDto $config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        $id = $this->config->id;
        $name = $this->config->name;
        $url = $this->config->url;
        $iconName = $this->config->iconName ?? 'document-text-outline'; // Icono por defecto
        $this->config->level = $this->config->level + 1;
        $level = $this->config->level;
        $levelAux = $level - 1;

        // Si no tiene hijos, es un elemento final
        if ($this->config->childs == []) {
            return <<<HTML
            <div class="custom-menu-section menu_item_openable" moduleId="{$id}">
                <button class="custom-button level-{$levelAux}">
                    <ion-icon name="{$iconName}" class="icon_menu"></ion-icon> 
                    <p class="text_menu_option" >{$name}</p>
                </button>
            </div>
            HTML;
        } 
        // Si tiene hijos, es un menú desplegable
        else {
            $FINAL_LIST = "";

            foreach ($this->config->childs as $key => $MenuItem) {
                $MenuItem->level = $level;
                $FINAL_LIST .= (new MenuItemComponent($MenuItem))->render();
            }

            

            return <<<HTML
            <div class="custom-menu-section ">
                <div class="custom-menu-title level-{$levelAux}" onclick="toggleSubMenu(this)">
                    <ion-icon name="chevron-forward-outline" class="icon_menu"></ion-icon>
                    <p class="text_menu_option" >{$name}</p>
                </div>
                <div class="custom-submenu section-level-{$level}">
                    {$FINAL_LIST}
                </div>
            </div>
            HTML;
        }
    }
}
