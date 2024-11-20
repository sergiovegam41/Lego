<?php

namespace routes;

use Flight;

class Views {

    static public function handle() {
 
         Flight::route('GET /', function () {
 
             
            echo "desde views";
         
         });
         
         Flight::route('GET /test', function () {
             echo 'test desde web';
         });
         
 
     }
 
 }
 