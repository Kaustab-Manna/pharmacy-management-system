<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, string|array|callable $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, string|array|callable $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    private function addRoute(string $method, string $uri, string|array|callable $handler): void
    {
        $uri = '/' . trim($uri, '/');
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $uri,
            'handler' => $handler
        ];
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->getMethod();
        $requestUri = '/' . trim($request->getUri(), '/');

        // Check each registered route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert CI route wildcards to regex
            $pattern = $route['pattern'];
            $regex = str_replace(
                ['(:num)', '(:segment)', '(:any)'],
                ['([0-9]+)', '([^/]+)', '([^/]+)'],
                $pattern
            );
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $requestUri, $matches)) {
                array_shift($matches); // Remove full match

                $handler = $route['handler'];

                try {
                    if (is_callable($handler)) {
                        call_user_func_array($handler, $matches);
                        return;
                    }

                    if (is_string($handler)) {
                        if (str_contains($handler, '@')) {
                            [$controllerName, $action] = explode('@', $handler);
                        } else {
                            $controllerName = $handler;
                            $action = 'index';
                        }

                        $controllerClass = str_starts_with($controllerName, 'App\\') 
                            ? $controllerName 
                            : "App\\Controllers\\{$controllerName}";

                        if (class_exists($controllerClass)) {
                            $controller = new $controllerClass();
                            if (method_exists($controller, $action)) {
                                call_user_func_array([$controller, $action], $matches);
                                return;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("Router caught exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    http_response_code(500);
                    echo "<!DOCTYPE html><html><head><title>Application Notice - INFOSOF</title>";
                    echo "<style>body{font-family:system-ui,-apple-system,sans-serif;padding:60px 20px;background:#f8fafc;color:#1e293b;max-width:800px;margin:0 auto;} .card{background:#fff;border-radius:12px;padding:30px;box-shadow:0 4px 15px rgba(0,0,0,0.06);border:1px solid #e2e8f0;} h1{color:#dc2626;font-size:24px;margin-top:0;display:flex;align-items:center;gap:10px;} .notice{background:#fee2e2;border-left:4px solid #ef4444;padding:12px 16px;border-radius:6px;margin:18px 0;font-size:15px;color:#991b1b;} .btn{display:inline-block;padding:10px 18px;background:#0d9488;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;margin-right:10px;font-size:14px;}</style>";
                    echo "</head><body><div class='card'>";
                    echo "<h1><span>⚠️</span> System Notice</h1>";
                    echo "<div class='notice'>" . htmlspecialchars($e->getMessage()) . "</div>";
                    echo "<p style='color:#64748b;font-size:14px;'>The action could not be completed. You can safely return to the previous page.</p>";
                    echo "<a href='javascript:history.back()' class='btn'>&larr; Go Back</a>";
                    echo "<a href='/' class='btn' style='background:#0284c7;'>Dashboard</a>";
                    echo "</div></body></html>";
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title>";
        echo "<style>body{font-family:sans-serif;text-align:center;padding:100px;background:#f8fafc;color:#1e293b;} h1{font-size:48px;color:#0d9488;} a{color:#0d9488;text-decoration:none;font-weight:bold;}</style>";
        echo "</head><body><h1>404</h1><h2>Page Not Found</h2><p>Requested URI: " . htmlspecialchars($requestUri) . "</p>";
        echo "<p><a href='/'>&larr; Return to Dashboard</a></p></body></html>";
    }
}
