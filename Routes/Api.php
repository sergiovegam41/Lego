<?php

namespace Routes;

use App\Controllers\Auth\AuthGroupsController;
use Flight;

class Api {

    static public function handle() {
 
        Flight::route('POST|GET /auth/@group/@accion', fn ($group, $accion) => new AuthGroupsController($group, $accion));
    
        Flight::route('GET /', function () {
            echo 'hello world! desde api';
        });

        Flight::route('GET /test', function () {
            echo 'test desde api';
        });




 
     }
 
 }
 