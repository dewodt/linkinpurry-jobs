<?php

namespace src\core;

use src\exceptions\HttpExceptionFactory;

class Router
{
    // Store registered routes for the app
    private $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    /**
     * Add a route to the app with certain method, path, handler (Class@method), and middlewares
     * @param string $method
     * @param string $path
     * @param callable Factory function that returns the function handler (associative array controller, method) (only 1)
     * @param callable Factory function that returns the middlewares (>= 0)
     */
    private function addRoute(string $method, string $path, callable $handlerFactory, callable $middlewaresFactory = null): void
    {
        $middlewaresFactory = $middlewaresFactory ?? function () {
            return [];
        };

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handlerFactory' => $handlerFactory,
            'middlewaresFactory' => $middlewaresFactory,
        ];
    }

    /**
     * Add a GET route to the app
     */
    public function get(string $path, callable $handlerFactory, callable $middlewaresFactory = null)
    {
        $this->addRoute('GET', $path, $handlerFactory, $middlewaresFactory);
    }

    /**
     * Add a POST route to the app
     */
    public function post(string $path, callable $handlerFactory, callable $middlewaresFactory = null)
    {
        $this->addRoute('POST', $path, $handlerFactory, $middlewaresFactory);
    }

    /**
     * Add a PUT route to the app
     */
    public function put(string $path, callable $handlerFactory, callable $middlewaresFactory = null)
    {
        $this->addRoute('PUT', $path, $handlerFactory, $middlewaresFactory);
    }

    /**
     * Add a DELETE route to the app
     */
    public function delete(string $path, callable $handlerFactory, callable $middlewaresFactory = null)
    {
        $this->addRoute('DELETE', $path, $handlerFactory, $middlewaresFactory);
    }

    /**
     * Dispatch the request to the correct handler
     */
    public function dispatch(): void
    {
        $res = new Response();
        $reqMethod = $_SERVER['REQUEST_METHOD'];
        $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($this->matchPath($route['path'], $reqPath) && $route['method'] === $reqMethod) {
                // Initialize request & response
                $req = new Request($route['path']);

                // Call all middlewares
                $middlewares = $route['middlewaresFactory']();
                foreach ($middlewares as $middleware) {
                    $middleware->handle($req, $res);
                }

                // Call the handler
                // echo var_dump($route['handlerFactory']());
                $handler = $route['handlerFactory']();
                $controller = $handler['controller'];
                $method = $handler['method'];
                $controller->$method($req, $res);

                return;
            }
        }

        // If not found, default redirect to 404 page
        throw HttpExceptionFactory::create(404, 'Route not found');
    }

    private function matchPath(string $router, string $uri): bool
    {
        // Parse path parameters /{id}/ into regex
        // and match from the beginning to the end of the string
        $regex = '#^' . preg_replace('/{[^}]+}/', '([^/]+)', $router) . '$#';
        return preg_match($regex, $uri);
    }
}