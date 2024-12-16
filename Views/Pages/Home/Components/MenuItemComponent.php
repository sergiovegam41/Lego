<?php

namespace Views\Pages\Home\Components;


use Core\Components\CoreComponent\CoreComponent;
use Core\providers\StringMethods;
use Views\Pages\Home\Dtos\MenuItemDto;

class MenuItemComponent extends CoreComponent 
{

  use StringMethods;
  protected $config;
  protected $JS_PATHS = [ ];
  protected $JS_PATHS_WITH_ARG = [ ];
  protected $CSS_PATHS = [

  ];

  public function __construct(MenuItemDto $config)
  {
    $this->config = $config;
  }

  protected function component(): string
  {
    
    $id = $this->config->id;
    $name = $this->config->name;
    $url = $this->config->url;
    $iconName = $this->config->iconName;

    return <<<HTML
    
      <li iconName="drag" lego-link="$url"  data-module-id="$id" class="nav-link lego-module" data-id="$id">
          <a>
              <ion-icon class ='icon' name="$iconName"></ion-icon>
              <span class="text nav-text">$name</span>
              <button class="close-btn" onclick="event.stopPropagation(); lego.closeModule('$id')">X</button>
          </a>
      </li>
     
    HTML;

  }
}
