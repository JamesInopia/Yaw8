<?php
define('BASE_URL', 'http://yaw8.com/Yaw8/public');

spl_autoload_register(function ($class) {
    $folders = [
        __DIR__ . '/../app/core/',
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/services/',
        __DIR__ . '/../app/models/'
    ];

    foreach ($folders as $folder) {
        $file = $folder . $class . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$routes = require_once __DIR__ . '/../app/config/routes.php';

$router = new Router($routes);
$router->route($_GET["url"] ?? 'home');