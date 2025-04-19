<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Core/bootstrap.php';


$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = rtrim($uri, '/') . '/';

if (strpos($uri, 'api/') === 0) {
    $_SERVER['REQUEST_URI'] = '/' . substr($uri, 4); 
    require __DIR__ . '/../Routes/Api.php';

} elseif (strpos($uri, 'view/') === 0) {
    $_SERVER['REQUEST_URI'] = '/' . substr($uri, 5);
    require __DIR__ . '/../Routes/Views.php';

} else {
    require __DIR__ . '/../Routes/Web.php';
}

Flight::start();
