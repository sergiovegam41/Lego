<?php

namespace Routes;

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Response;
use Core\Services\ApiRouteDiscovery;
use Flight;
use Components\Core\Automation\AutomationComponent;
use Components\Core\Home\HomeComponent;

// 🚀 Auto-descubrir rutas API de componentes
ApiRouteDiscovery::discover();

