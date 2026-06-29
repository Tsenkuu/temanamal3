<?php
namespace App\Config;

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Cari rute yang cocok
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $path => $callback) {
                // Ganti {slug} atau {id} dengan regex
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $path);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    // Ekstrak named parameters
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value;
                        }
                    }

                    if (is_array($callback)) {
                        $controller = new $callback[0]();
                        $action = $callback[1];
                        return call_user_func_array([$controller, $action], $params);
                    } else if (is_callable($callback)) {
                        return call_user_func_array($callback, $params);
                    }
                }
            }
        }

        // Fallback untuk file lama yang dipanggil langsung melalui .htaccess (misal berita.php)
        // Kita tidak boleh memutus fitur lama. Jika bukan route kita, let .htaccess atau file asli take over?
        // Masalahnya index.php dipanggil JIKA file tidak ada.
        
        http_response_code(404);
        echo "404 Not Found";
    }
}
