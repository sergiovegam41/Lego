<?php

namespace Views\Core\Home\Components;

use Core\Components\CoreComponent\CoreComponent;
use Core\providers\StringMethods;
use Views\Core\Home\Dtos\MenuItemDto;

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
        )
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


    return <<<HTML

        <nav class="sidebar ">
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

                    <div class="custom-menu" id="">
                        

                        {$FINAL_MENU_LIST}


                    </div>
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
