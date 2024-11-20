<?php

namespace routes;

use Flight;

class Views {

    static public function handle() {
 
    
        Flight::route('GET /', function () {
            echo 'hello world! desde api';
        });

        Flight::route('GET /test', function () {
            echo 'test desde api';
        });


 
     }
 
 }
 