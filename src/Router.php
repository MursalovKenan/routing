<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;
use Mursalov\Routing\Exceptions\RouterException;
use ReflectionClass;
use ReflectionException;

class Router implements RouteInterface
{
    protected array $routes = [];

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

    /**
     * @throws RouterException
     */
    public function route(string $uri): callable
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        var_dump($uri);
        if (!isset($this->routes[$uri])) {
            throw new RouterException('route not found');
        }
        $action = $this->routes[$uri];
        if (!is_array($action)) {
            return $action;
        }
        [$classPath, $methodName] = $action;
        $class = new $classPath;
        return function () use ($class, $methodName)
        {
            $reflectionMethod = new \ReflectionMethod($class, $methodName);
            $params = $this->cellClassMethod($reflectionMethod);
            $reflectionMethod->invokeArgs($class, $params);
        };
    }

    /**
     * @param string $class
     * @param string $method
     * @return \Closure
     * @throws RouterException
     * @throws ReflectionException
     */
    private function cellClassMethod(\ReflectionMethod $reflectionMethod): array
    {
        $reflectionParams = $reflectionMethod->getParameters();
        if (empty($reflectionParams)) {
            return [];
        }
        $params = [];
        foreach ($reflectionParams as $param) {
            $name = $param->getName();
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                throw new RouterException('Param type not a primitive');
            }
            $defaultValue = $param->getDefaultValue();
            if (isset($defaultValue)) {
                $params[$name] = $defaultValue;
            }
            if (isset($_GET[$name])) {
                $value = $_GET[$name];
            }
            if (isset($value)) {
                $params[$name] = $value;
            }
        }
        return $params;
    }

    /**
     * @throws RouterException
     */
    private function validateClassAndMethod(string $class, string $method): void
    {
        if (!class_exists($class)) {
            throw new RouterException('Class path ' . $class . ' not found');
        }
        if (!method_exists($class, $method)) {
            throw new RouterException('method ' . $method . ' not found');
        }
    }
}