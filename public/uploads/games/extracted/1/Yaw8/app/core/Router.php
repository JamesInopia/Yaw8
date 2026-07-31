<?php
class Router {
    private array $routes;

    public function __construct(array $routes){
        $this->routes = $routes;
    }

    public function route(string $url){
        if (!isset($this->routes[$url])) { 
            echo '404 Page Not Found'; 
            return;
        }

        [$controllerClass, $method] = $this->routes[$url];
        $controller = new $controllerClass();

        if (!method_exists($controller, $method)){
            echo 'Method Not Found';
            return;
        }

        $controller->$method();
    }
}