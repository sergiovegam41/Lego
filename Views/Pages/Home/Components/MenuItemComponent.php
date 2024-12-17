<?php

namespace Views\Pages\Home\Components;

use Core\Components\CoreComponent\CoreComponent;
use Core\providers\StringMethods;
use Views\Pages\Home\Dtos\MenuItemDto;

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

        // Si no tiene hijos, es un elemento final
        if ($this->config->childs == []) {
            return <<<HTML
            <div class="custom-menu-section">
                <button class="custom-button">
                    <ion-icon name="{$iconName}"></ion-icon> 
                    {$name}
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
            <div class="custom-menu-section">
                <div class="custom-menu-title" onclick="toggleSubMenu(this)">
                    <ion-icon name="chevron-forward-outline"></ion-icon>
                    {$name}
                </div>
                <div class="custom-submenu level-{$level}">
                    {$FINAL_LIST}
                </div>
            </div>
            HTML;
        }
    }
}
