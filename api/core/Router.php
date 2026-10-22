<?php
/**
 * TechStore - RESTful API Router
 * Supports dynamic route parameters {param}, middleware execution, and controller dispatching
 */

namespace Api\Core;

use Api\Helpers\Response;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function patch(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    public function delete(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function options(string $path, array $handler, array $middlewares = []): void {
        $this->addRoute('OPTIONS', $path, $handler, $middlewares);
    }

    public function addRoute(string $method, string $path, array $handler, array $middlewares = []): void {
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        // Convert {param} to named regex (?P<param>[^/]+)
        $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request): void {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $request->setRouteParams($params);

                // Run Middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    if (is_string($middlewareClass) && class_exists($middlewareClass)) {
                        $middleware = new $middlewareClass();
                        $middleware->handle($request);
                    } elseif (is_callable($middlewareClass)) {
                        $middlewareClass($request);
                    }
                }

                // Dispatch to Controller
                [$controllerClass, $action] = $route['handler'];
                if (!class_exists($controllerClass)) {
                    Response::error("Controller class {$controllerClass} not found", 'SERVER_ERROR', 500);
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    Response::error("Action {$action} not found in {$controllerClass}", 'SERVER_ERROR', 500);
                }

                $controller->$action($request);
                return;
            }
        }

        // Fallback: 404
        Response::error("Endpoint [{$method} {$path}] not found", 'NOT_FOUND', 404);
    }
}
