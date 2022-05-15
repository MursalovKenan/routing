<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;
use Mursalov\Routing\Exceptions\RouterException;
use ReflectionClass;

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
            return $this->get404();
        }
        $action = $this->routes[$uri];
        if (!is_array($action)) {
            return $action;
        }
        [$classPath, $methodName] = $action;
        return $this->getClassMethod($classPath, $methodName);
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

    private function get404()
    {
        return static function () {
            http_response_code(404);
            echo '<h3>404 page not found</h3>';
        };
    }

    private function getClassMethod(string $class, string $method)
    {
        if (!class_exists($class)) {
            throw new RouterException('Class path ' . $class . ' not found');
        }
        if (method_exists($class, $method)) {
            return static function () use ($class, $method) {
//                $reflectionActionMethod = new \ReflectionMethod($controllerClass, $methodName);
//                $params = $reflectionActionMethod->getAttributes();
//                var_dump($params);
//                exit();
//                foreach ($params as $param) {
//
//                }
                $classObj = new $class();
                $classObj->$method();
            };
        }
    }
}