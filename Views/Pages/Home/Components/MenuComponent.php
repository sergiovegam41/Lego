<?php

namespace Views\Pages\Home\Components;

use Core\Components\CoreComponent\CoreComponent;
use Core\providers\StringMethods;
use Views\Pages\Home\Dtos\MenuItemDto;

class MenuComponent extends CoreComponent 
{

  use StringMethods;
  protected $config;
  protected $JS_PATHS = [ ];
  protected $JS_PATHS_WITH_ARG = [ ];
  protected $CSS_PATHS = [
    '/assets/css/core/sidebar/menu-style.css',
    'https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css'
  ];

  public function __construct($config)
  {
    $this->config = $config;
  }

  protected function component(): string
  {
    $HOST_NAME = env('HOST_NAME');

       /**
     * @param MenuItemDto[] $MENU_LIST
     */


    $MENU_LIST = [
        new MenuItemDto(
            id: "1", 
            name: "Departamentos", 
            url:$HOST_NAME . '/resourses/home',  
            iconName:"map-outline" 
        ),
        new MenuItemDto(
            id: "2", 
            name: "Usuarios", 
            url:$HOST_NAME . '/resourses/home',  
            iconName:"person-circle-outline" 
        ),
        new MenuItemDto(
            id: "3", 
            name: "Home", 
            url:$HOST_NAME . '/resourses/home',  
            iconName:"home-outline" 
        ),
        new MenuItemDto(
            id: "5", 
            name: "Configuracion", 
            url:$HOST_NAME . '/resourses/home',  
            iconName:"cog-outline",
            childs: [
                new MenuItemDto(
                    id: "6", 
                    name: "Usuarios", 
                    url:$HOST_NAME . '/resourses/home',  
                    iconName:"person-circle-outline" 
                ),
                new MenuItemDto(
                    id: "7", 
                    name: "Home", 
                    url:$HOST_NAME . '/resourses/home',  
                    iconName:"home-outline" 
                ),
            ]
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
        $FINAL_MENU_LIST .= (new MenuItemComponent( $MenuItem ))->render();
        
        
    }
    


    // p($HOST_NAME);
    return <<<HTML

        <nav class="sidebar">
            <header>
                <div class="image-text">
                    <span class="image">
                        <img class="user-image" src="/assets/images/logo.png" alt="">
                        <!-- <p>Sergio Vega</p> -->
                    </span>

                    <div class="text logo-text">
                        <span class="name">Lego</span>
                        <span class="profession">Freamework</span>
                    </div>
                    
                </div>

                <i class='bx bx-chevron-right toggle'></i>
            </header>

            <div class="menu-bar">


                <hr>
        
                <div class="menu" id="sidebar_menu">

                    <li class="search-box">
                        <ion-icon class ='icon' name="search-outline"></ion-icon>
                        <input type="text" placeholder="Search" id="search-menu">
                    </li>

                    <ul class="menu-links" id="menu-links">
                        

                        {$FINAL_MENU_LIST}


                    </ul>
                </div>

                <div class="bottom-content">

                    <li class="" id="theme-toggle">
                        <a >
                            <ion-icon class ='icon'  name="moon-outline"></ion-icon>
                            <span class="text nav-text">Theme</span>
                        </a>
                    </li>
                    
                    <hr>

                    <li class="">
                        <a href="{$HOST_NAME}">
                            <ion-icon class ='icon'  name="log-out-outline"></ion-icon>
                            <span class="text nav-text">Logout</span>
                        </a>
                    </li>
                    
                </div>
            </div>

        </nav>
  
     
    HTML;

  }
}
