<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;
use Mursalov\Routing\Exceptions\RouterException;
use ReflectionClass;
use ReflectionException;

/**
 * Class for routing.
 * @author Kenan Mursalov
 */
class Router implements RouteInterface
{
    /**
     * Routes array.
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Add rout to route array.
     *
     * @param string $path
     * @param array|callable $action
     * @return void
     * @throws RouterException
     */
    public function addRoute(string $path, array|callable $action): void
    {
        $path = trim($path, '/');
        if (is_array($action) && !(count($action) === 2)) {
            throw new RouterException('Array argument most contain two arguments');
        }
        $this->routes[$path] = $action;
    }

    /**
     * Return callback with function which added to route.
     *
     * @param string $uri
     * @return callable
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
     * Cell method with parameters if params exist and requested in GET array.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @return array
     * @throws ReflectionException
     * @throws RouterException
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
     * Validate class exist and method in this class exist.
     *
     * @param string $class
     * @param string $method
     * @return void
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