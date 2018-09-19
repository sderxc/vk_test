<?php


function error(int $code, callable $callback = null) {
    static $callbacks = [];
    if ($callback) {
        return $callbacks[$code] = $callback;
    }
    http_response_code($code);
    if (isset($callbacks[$code])) {
        $callbacks[$code]();
    }
}


// Модифицированная версия:
function route(string $path, callable $callback = null, string $method = 'GET')
{
    static $routes = [];
    if ($callback) {
        // Тут нужно поработать над перменной $path
        return $routes[$method]["#^{$path}$#"] = $callback;
    }
    $method = $_SERVER['REQUEST_METHOD'];
    if (isset($routes[$method])) {
        foreach ($routes[$method] as $pattern => $callback) {
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return $callback(...$matches);
            }
        }
    }
    error(404);
}