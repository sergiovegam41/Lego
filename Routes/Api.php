<?php

namespace Routes;

use Flight;

class Api {

    static public function handle() {
 
    
        Flight::route('GET /', function () {
            echo 'hello world! desde api';
        });

        Flight::route('GET /test', function () {
            echo 'test desde api';
        });


 
     }
 
 }
 