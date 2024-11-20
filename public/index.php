<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/bootstrap.php';
require __DIR__ . '/../app/utils/global.php';

use routes\Web;


$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = rtrim($uri, '/') . '/';

if (strpos($uri, 'api/') === 0) {
    
    $routesFile = __DIR__ . '/../routes/api.php';
    $_SERVER['REQUEST_URI'] = '/' . substr($uri, 4); 

    
} elseif (strpos($uri, 'view/') === 0) {
    $routesFile = __DIR__ . '/../routes/views.php';
    $_SERVER['REQUEST_URI'] = '/' . substr($uri, 5);
} else {
    // use Web;
    
    Web::handle();
}

// use $routesFile;
Flight::start();