<?php
namespace App;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip project prefix /esg if present
        $base = '/esg';
        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                [$controllerClass, $action] = $route['handler'];

                // Filter out string keyed matches for route params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = new $controllerClass();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // 404 handler
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center justify-content-center vh-100'><div class='text-center p-5 bg-white shadow rounded'><h1>404</h1><h4 class='text-muted mb-4'>查無此系統頁面 ({$uri})</h4><a href='/esg/' class='btn btn-primary'>返回 ESG 戰情首頁</a></div></body></html>";
    }
}
