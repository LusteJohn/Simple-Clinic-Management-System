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

    public function put($path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
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

        $methodRoutes = $this->routes[$method] ?? [];
        $handler = $methodRoutes[$path] ?? null;
        $params = [];

        if (!$handler) {
            foreach ($methodRoutes as $routePath => $routeHandler) {
                $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $routePath);

                if (!preg_match('#^' . $pattern . '$#', $path, $matches)) {
                    continue;
                }

                $handler = $routeHandler;
                $params = array_slice($matches, 1);
                break;
            }
        }

        if (!$handler) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        [$class, $function] = $handler;

        $controller = new $class();
        call_user_func_array([$controller, $function], $params);
    }
}