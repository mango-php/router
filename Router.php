<?php

namespace Mango;

class Router
{
    private $routes;
    private $dependencyContainer;

    public function __construct(DependencyContainer $dependencyContainer) {
        $this->routes = [];
        $this->dependencyContainer = $dependencyContainer;
    }

    public function addGet($path, $callback) {
        $this->add(['GET'], $path, $callback);
    }

    public function addPost($path, $callback) {
        $this->add(['POST'], $path, $callback);
    }

    public function addPut($path, $callback) {
        $this->add(['PUT'], $path, $callback);
    }

    public function addPatch($path, $callback) {
        $this->add(['PATCH'], $path, $callback);
    }

    public function addDelete($path, $callback) {
        $this->add(['DELETE'], $path, $callback);
    }

    public function add(array $methods, string $path, mixed $callback) {
        $this->routes[] = [
            'path' => $path,
            'methods' => $methods,
            'callback' => $callback,
        ];
    }

    private function findRouteForPath($path) : ?array {
        $matchingRoute = null;

        foreach ($this->routes as $route) {
            if (array_key_exists('path', $route) && $route['path'] == $path) {
                $matchingRoute = $route;
            }
        }

        return $matchingRoute;
    }

    public function resolve(string $path) : void {
        $matchingRoute = $this->findRouteForPath($path);

        if ($matchingRoute == null) {
            echo '404';
        }

        $callback = $matchingRoute['callback'];

        if (is_callable($callback)) {
            echo $callback();
        }
        else {
            if (is_string($callback)) {
                $controllerObject = $this->dependencyContainer->fetch($callback);

                if ($controllerObject != null && method_exists($controllerObject, '__invoke')) {
                    echo call_user_func(array($controllerObject, '__invoke'));
                }
                else {
                    echo 'cant do it';
                }
            }
            else {
                $controller = $callback[0];
                $method = $callback[1];

                $controllerObject = $this->dependencyContainer->fetch($controller);
                echo $controllerObject->$method();
            }
        }
    }
}