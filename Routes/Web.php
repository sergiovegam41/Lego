<?php
namespace Routes;

use App\Controllers\Auth\Providers\AuthGroups\Admin\Middlewares\AdminMiddlewares;
use Core\Helpers\LegoHelpers;
use Core\Response;
use Flight;
use Views\Core\Home\Components\MainComponent\MainComponent;
use Views\Core\Login\LoginComponent;



Flight::route('GET /admin/',function (){

    if( AdminMiddlewares::isAutenticated() ){
        $componet = new MainComponent([ ]);
        return Response::uri( $componet->render() );
    }
} );

Flight::route('GET /login',function (){
    $componet = new LoginComponent([]);
    Response::uri( $componet->render() );
} );

Flight::route('GET /',function (){

    //redirect to login 
   LegoHelpers::redirect('admin');
   
} );

