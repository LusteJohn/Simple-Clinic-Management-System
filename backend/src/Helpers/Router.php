<?php

namespace src\Helpers;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function middleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch($uri, $method)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (str_contains($path, '/index.php')) {
            $path = substr($path, strpos($path, '/index.php') + strlen('/index.php')) ?: '/';
        }

        if ($path === '') {
            $path = '/';
        }

        foreach ($this->middleware as $middleware) {
            $middleware($path, $method);
        }

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        [$class, $function] = $handler;

        $controller = new $class();
        $controller->$function();
    }
}