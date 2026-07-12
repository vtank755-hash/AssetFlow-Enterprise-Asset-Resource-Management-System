<?php
namespace App\Core;

class Router {
    protected $routes = [];

    /**
     * Add a routing mapping rules.
     * 
     * @param string $route Route path (e.g. '/dashboard', '/assets/create')
     * @param string $handler Controller action identifier (e.g. 'AssetController@create')
     */
    public function add($route, $handler) {
        $this->routes[trim($route, '/')] = $handler;
    }

    /**
     * Dispatch the current request to the matched controller action.
     * 
     * @param string $requestUri Server REQUEST_URI value
     */
    public function dispatch($requestUri) {
        // Parse paths and strip queries
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Remove BASE_URL prefix from path if it exists
        if (BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
            $path = substr($path, strlen(BASE_URL));
        }
        
        $path = trim($path, '/');
        
        // Fallback root redirect to dashboard
        if ($path === '') {
            $path = 'dashboard';
        }

        if (array_key_exists($path, $this->routes)) {
            $handler = $this->routes[$path];
            list($controllerClass, $method) = explode('@', $handler);
            $fullControllerClass = "App\\Controllers\\" . $controllerClass;

            if (class_exists($fullControllerClass)) {
                $controller = new $fullControllerClass();
                if (method_exists($controller, $method)) {
                    $controller->$method();
                    return;
                }
            }
        }

        // Renders 404 page if no route matched
        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        $viewFile = dirname(__DIR__) . '/views/errors/404.php';
        if (file_exists($viewFile)) {
            // Render 404 view directly without standard dashboard layout
            $no_layout = true;
            require $viewFile;
        } else {
            echo "<h1>404 Page Not Found</h1><p>The requested page was not found on this system.</p>";
        }
    }
}
