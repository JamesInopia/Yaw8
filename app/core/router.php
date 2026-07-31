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

        $args = $this->resolveArgs($controller, $method);
        $controller->$method(...$args);
    }

    private function resolveArgs($controller, string $method): array {
        $reflection = new ReflectionMethod($controller, $method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();

            if (isset($_GET[$name])) {
                $args[] = $_GET[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        return $args;
    }
}