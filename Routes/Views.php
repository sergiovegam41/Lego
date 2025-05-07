<?php

namespace Routes;

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Response;
use Flight;
use Views\Core\Automation\AutomationComponent;

Flight::route('GET /', function () {

});

Flight::route('GET /automation', function () {
    
    if( AdminMiddlewares::isAutenticated() ){

        $componet = new AutomationComponent([  ]);
        return Response::uri( $componet->render() );

    }

});