<?php

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, string $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, string $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    /**
     * Wraps $callback so every get/post/put/delete call made inside it gets
     * $attributes['prefix'] prepended to its path and $attributes['middleware']
     * merged in ahead of its own middleware. Nestable (stack-based) though
     * today's routes only use one level, for the new /trainer/* block.
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $prefix = '';
        $groupMiddleware = [];
        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'] ?? '';
            $groupMiddleware = array_merge($groupMiddleware, $group['middleware'] ?? []);
        }

        $path = $prefix . $path;
        $middleware = array_merge($groupMiddleware, $middleware);

        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $method = $method === 'HEAD' ? 'GET' : $method;
        $path = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            foreach ($route['middleware'] as $middlewareName) {
                Middleware::resolve($middlewareName)->handle($request);
            }

            $this->callHandler($route['handler'], $params, $request);
            return;
        }

        $this->handleNotFound($request);
    }

    private function match(string $pattern, string $path): ?array
    {
        $patternRegex = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern);
        $patternRegex = '#^' . $patternRegex . '$#';

        if (!preg_match($patternRegex, $path, $matches)) {
            return null;
        }

        array_shift($matches);

        preg_match_all('#\{([a-zA-Z_]+)\}#', $pattern, $paramNames);
        $names = $paramNames[1];

        return array_combine($names, $matches) ?: [];
    }

    private function callHandler(string $handler, array $params, Request $request): void
    {
        [$controllerName, $action] = explode('@', $handler);
        $class = "App\\Controllers\\{$controllerName}";

        $controller = new $class();
        $controller->$action($request, ...array_values($params));
    }

    private function handleNotFound(Request $request): void
    {
        if ($request->isJsonRequest() || str_starts_with($request->path(), '/api/')) {
            Response::json(['success' => false, 'message' => 'Not found'], 404);
        }

        http_response_code(404);
        echo View::render('errors/404', [], 'main');
        exit;
    }
}
