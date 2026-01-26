<?php
declare(strict_types=1);

namespace Core;

use Core\Request;
use Core\Response;
use Core\Session;

class Router
{
    private array $routes = [];

    public function __construct(
        private Request $request,
        private Response $response,
        private Session $session
    ) {
    }

    public function add(string $requestMethod, string $path, array $handler, array $parameters = []): void
    {
        $this->routes[$requestMethod][$path] = [
            'handler' => $handler,
            'arguments' => $parameters
        ];
    }

    public function dispatch($requestMethod, $path): void
    {
        $path = $this->routes[$requestMethod][$path];
        $requestHandler = $path['handler'];
        $arguments = $path['arguments'];

        $controller = new $requestHandler['class'](
            $this->request,
            $this->response,
            $this->session
        );

        $action = $requestHandler['method'];

        $controller->$action(...$arguments);
    }
}
