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
        $uri = trim($uri , '/');
        if (!isset($this->routes[$uri])) {
            throw new RouterException('Route ' . $uri . ' not found');
        }
        $action = $this->routes[$uri];

        if (!is_array($action)) {
            return $action;
        }
        [$classPath, $methodName] = $action;
        if (!class_exists($classPath)){
            throw new RouterException('Class path ' . $classPath . ' not found');
        }
        $controllerClass = new $classPath;
        if (method_exists($controllerClass, $methodName)) {
            return $controllerClass->$methodName;
        }
        return static function () {
            http_response_code(404);
        };
    }

    public function addRoute(string $path, array | callable $action)
    {
        $path = trim($path, '/');
        $this->routes[$path] = $action;
    }
}