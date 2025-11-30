<?php

namespace Components\Core\Home\Components\HeaderComponent;

use Core\Components\CoreComponent\CoreComponent;
use Core\Dtos\ScriptCoreDTO;
use Core\Providers\StringMethods;
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

        // Render params button (for debugging/managing persistent params)
        $paramsButton = (new IconButtonComponent(
            icon: "options-outline",
            size: "medium",
            variant: "ghost",
            onClick: "toggleParamsPopover()",
            title: "Ver parámetros persistentes",
            className: "header-params-btn"
        ))->render();

        return <<<HTML
        <header id="top-header" class="main-header">
            <div class="header-left">
                {$breadcrumb}
            </div>
            <div class="header-center">
                {$reloadButton}
                {$closeButton}
                <div class="params-popover-container">
                    <div class="params-btn-wrapper">
                        {$paramsButton}
                        <span class="params-badge" id="params-badge" style="display: none;">0</span>
                    </div>
                    <div class="params-popover" id="params-popover">
                        <div class="params-popover__header">
                            <span class="params-popover__title">Parámetros Persistentes</span>
                            <button class="params-popover__close" onclick="closeParamsPopover()">
                                <ion-icon name="close-outline"></ion-icon>
                            </button>
                        </div>
                        <div class="params-popover__module-info" id="params-module-info">
                            <!-- Module info will be populated by JS -->
                        </div>
                        <div class="params-popover__content" id="params-content">
                            <!-- Params will be populated by JS -->
                        </div>
                        <div class="params-popover__footer">
                            <button class="params-popover__clear-btn" onclick="clearAllParams()">
                                <ion-icon name="trash-outline"></ion-icon>
                                Limpiar todos
                            </button>
                        </div>
                    </div>
                </div>
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
                    <!-- User Dropdown Menu -->
                    <div class="user-dropdown" id="user-dropdown">
                        <div class="user-dropdown__item" id="user-profile-btn">
                            <ion-icon name="person-outline"></ion-icon>
                            <span>Mi Perfil</span>
                        </div>
                        <div class="user-dropdown__item" id="user-settings-btn">
                            <ion-icon name="settings-outline"></ion-icon>
                            <span>Configuración</span>
                        </div>
                        <div class="user-dropdown__divider"></div>
                        <div class="user-dropdown__item user-dropdown__item--logout" id="logout-btn">
                            <ion-icon name="log-out-outline"></ion-icon>
                            <span>Cerrar sesión</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        HTML;
    }
}