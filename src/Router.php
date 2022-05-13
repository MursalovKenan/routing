<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;
use Mursalov\Routing\Exceptions\RouterException;

class Router implements RouteInterface
{
    protected array $routes = [];

    /**
     * @throws RouterException
     */
    public function route(string $uri): callable
    {
        $uri = trim($uri, '/');
        if (!isset($this->routes[$uri])) {
            return static function () {
                http_response_code(404);
                echo '<h3>404 page not found</h3>';
            };
        }
        $action = $this->routes[$uri];
        if (!is_array($action)) {
            return $action;
        }
        [$classPath, $methodName] = $action;
        if (!class_exists($classPath)) {
            throw new RouterException('Class path ' . $classPath . ' not found');
        }
        $controllerClass = new $classPath();
        if (method_exists($controllerClass, $methodName)) {
            return static function () use ($controllerClass, $methodName) {
                $controllerClass->$methodName();
            };
        }
        throw new RouterException('Unknown router exception');
    }

    /**
     * @throws RouterException
     */
    public function addRoute(string $path, array|callable $action)
    {
        $path = trim($path, '/');
        if (is_array($action) && !(count($action) === 2)) {
            throw new RouterException('Array argument most contain two arguments');
        }
        $this->routes[$path] = $action;
    }
}