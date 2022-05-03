<?php

namespace Mursalov\Routing;

use Aigletter\Contracts\Routing\RouteInterface;

class Router implements RouteInterface
{
    protected array $routes;

    public function route(string $uri): callable
    {
        // TODO: Implement route() method.
    }

    public function addRoute(string $path, $action)
    {
//        todo: add check
        $this->routes[$path] = $action;
    }
}