<?php

namespace Views\Core\Home\Components\HeaderComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;

class HeaderComponent extends CoreComponent
{
    use StringMethods;
    protected $config;
    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [
        'components/Core/Home/Components/HeaderComponent/header-component.css'
    ];

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("components/Core/Home/Components/HeaderComponent/header-component.js", [
                "user" => [
                    "name" => "Admin User",
                    "role" => "Administrator",
                    "avatar" => "person-circle-outline"
                ],
                "notifications" => [
                    "count" => 3,
                    "unread" => true
                ]
            ])
        ];

        return <<<HTML
        <header id="top-header" class="main-header">
            <div class="header-left">
                <div class="notification-btn" id="notification-btn">
                    <ion-icon name="notifications-outline"></ion-icon>
                    <span class="notification-badge">3</span>
                </div>
                <div class="theme-toggle-header" id="theme-toggle">
                    <ion-icon name="sunny" class="sun"></ion-icon>
                    <ion-icon name="moon" class="moon"></ion-icon>
                </div>
            </div>
            <div class="header-right">
                <div class="user-info" id="user-info">
                    <div class="user-details">
                        <span class="user-name">Admin User</span>
                        <span class="user-role">Administrator</span>
                    </div>
                    <div class="user-avatar">
                        <ion-icon name="person-circle-outline"></ion-icon>
                    </div>
                </div>
            </div>
        </header>
        HTML;
    }
}