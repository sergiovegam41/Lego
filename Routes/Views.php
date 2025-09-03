<?php

namespace Routes;

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Response;
use Flight;
use Views\Core\Automation\AutomationComponent;
use Views\Core\Home\HomeComponent;

Flight::route('GET /', function () {

});

Flight::route('GET /inicio', function () {
    
    if( AdminMiddlewares::isAutenticated() ){

        $component = new HomeComponent([]);
        return Response::uri( $component->render() );

    }

});

Flight::route('GET /automation', function () {
    
    if( AdminMiddlewares::isAutenticated() ){

        $componet = new AutomationComponent([  ]);
        return Response::uri( $componet->render() );

    }

});