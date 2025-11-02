<?php

namespace Components\Core\Home\Components\HeaderComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\providers\StringMethods;
use Components\Shared\Navigation\BreadcrumbComponent\BreadcrumbComponent;
use Components\Shared\Buttons\IconButtonComponent\IconButtonComponent;

/**
 * HeaderComponent - Barra superior de la aplicación
 *
 * PROPÓSITO:
 * Renderiza la barra superior con información del usuario y notificaciones
 */
class HeaderComponent extends CoreComponent
{
    use StringMethods;

    protected $JS_PATHS = [];
    protected $JS_PATHS_WITH_ARG = [];
    protected $CSS_PATHS = [
        './header-component.css'
    ];

    public function __construct(
        public string $title = "Dashboard",
        public bool $showNotifications = true
    ) {}

    protected function component(): string
    {
        $this->JS_PATHS_WITH_ARG[] = [
            new ScriptCoreDTO("./header-component.js", [
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

        // Render breadcrumb
        $breadcrumb = (new BreadcrumbComponent(
            items: [
                ['label' => 'Inicio', 'href' => '#']
            ]
        ))->render();

        // Render reload button
        $reloadButton = (new IconButtonComponent(
            icon: "reload-outline",
            size: "medium",
            variant: "ghost",
            onClick: "window.legoWindowManager?.reloadActive()",
            title: "Recargar página actual",
            className: "header-reload-btn"
        ))->render();

        // Render close button
        $closeButton = (new IconButtonComponent(
            icon: "close-outline",
            size: "medium",
            variant: "ghost",
            onClick: "window.legoWindowManager?.closeCurrentWindow()",
            title: "Cerrar ventana actual",
            className: "header-close-btn"
        ))->render();

        return <<<HTML
        <header id="top-header" class="main-header">
            <div class="header-left">
                {$breadcrumb}
            </div>
            <div class="header-center">
                {$reloadButton}
                {$closeButton}
            </div>
            <div class="header-right">
                <div class="notification-btn" id="notification-btn">
                    <ion-icon name="notifications-outline"></ion-icon>
                    <span class="notification-badge">3</span>
                </div>
                <div class="theme-toggle-header" id="theme-toggle">
                    <ion-icon name="sunny" class="sun"></ion-icon>
                    <ion-icon name="moon" class="moon"></ion-icon>
                </div>
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