<?php
namespace Routes;

use Core\Response;
use Flight;
use Views\Core\Home\Components\MainComponent\MainComponent;

Flight::route('GET /',function (){
    $componet = new MainComponent([ ]);
    Response::uri( $componet->render() );
} );

Flight::route('GET /admin', function () {
    
});