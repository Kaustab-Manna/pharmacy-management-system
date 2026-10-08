<?php
/**
 * INFOSOF TECHNOLOGIES - Pharmacy Management Software
 * Front Controller & Application Bootstrapper
 * Compatible with PHP 8.x, MySQL 8.x / 5.x, CodeIgniter MVC Standard
 */

// Error reporting for development (can be configured in production via env)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Define Base Paths
define('ROOTPATH', dirname(__DIR__));
define('APPPATH', ROOTPATH . '/app');
define('PUBLICPATH', __DIR__);

// Load .env configuration if present
$envFile = ROOTPATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Register PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APPPATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Request;
use App\Core\Router;

// Initialize Router and Request
$router = new Router();
$request = new Request();

// Load System Routes
require_once APPPATH . '/Config/Routes.php';

// Dispatch Application
$router->dispatch($request);
