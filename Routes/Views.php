<?php

namespace Routes;

use Flight;


Flight::route('GET /', function () {
 
             
    echo "desde views";
 
 });
 
 Flight::route('GET /test', function () {
     echo 'test desde web';
 });