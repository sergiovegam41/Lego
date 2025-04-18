<?php

namespace Routes;

use App\Controllers\Auth\Controllers\AuthGroupsController;
use Core\Controller\CoreController;
use Flight;

Flight::route('POST|GET /auth/@group/@accion', fn ($group, $accion) => new AuthGroupsController($group, $accion));

$dynamicRoutes= CoreController::getMymapControllers();

foreach ($dynamicRoutes as $keyRoutes => $valRoutes){

    Flight::route("POST|GET /$keyRoutes/@accion", fn ($accion) => new $valRoutes($accion));

}