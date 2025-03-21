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

            // $redis = RedisClient::getInstance();

            // Guardar un valor en cache con expiración de 15 minutos
            // $tag = 'verify_codes';
            // $id = '3043707188';
            // $num_ale = rand(1, 1000);
            // $redis->setex("$tag:$id", 900, $num_ale);

            // Obtener el valor
            // $valor = $redis->get("$tag:$id");
            
            p("hi");

        });




 
     }
 
 }
 