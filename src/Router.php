<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;
use http\Exception;

class Router implements RouteInterface
{
    protected array $routes;
    public function route(string $uri): callable
    {
        $action = $this->routes[$uri];
        if (!is_array($action)) {
            return $action;
        }
        [$classPath, $methodName] = $action;
        if (class_exists($classPath)) {
            $controllerClass = new $classPath;
            if (method_exists($controllerClass, $methodName)) {
                return $controllerClass->$methodName;
            }
        }
        return static function () {
            http_response_code(404);
        };
    }

    public function addRoute(string $path, array | callable $action)
    {
        if (!filter_var($path, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Incorrect uri path');
        }
        if (empty($action)) {
            throw new \RuntimeException('Action parameter is empty');
        }
        if (!is_array($action)) {
            $this->routes[$path] = $action;
            return;
        }
        $class = array_key_first($action);
        if (class_exists($class)) {
            $this->routes[$path] = $action;
            return;
        }
    }
}