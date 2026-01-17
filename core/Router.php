<?php
declare(strict_types=1);

namespace Core;

use Core\Request;

class Router
{
    private $routes = [];

    public function __construct(private Request $request)
    {
    }

    public function add(string $method, string $path, array $handler, ?array $parameters = null): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'parameters' => $parameters
        ];
    }

    public function dispatch($method, $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && $route['path'] === $path) {

                $handler = $route['handler'];

                $controller = new $handler[0]($this->request);
                $action = $handler[1];

                if (!empty($route['parameters']['obj']) && \is_array($route['parameters']['obj'])) {
                    $controller->$action(...$route['parameters']['obj']);
                    return;

                } elseif (!empty($route['parameters']['obj'])) {
                    $controller->$action($route['parameters']['obj']);
                    return;
                }

                $controller->$action();
                return;
            }
        }
    }
}
