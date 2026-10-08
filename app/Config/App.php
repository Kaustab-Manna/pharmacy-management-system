<?php
namespace App\Config;

class App
{
    public static string $appName = 'INFOSOF Pharmacy Management';
    public static string $appVersion = '3.0.0';
    public static string $companyName = 'INFOSOF TECHNOLOGIES';
    public static string $timezone = 'Asia/Kolkata';
    public static string $defaultLocale = 'en';

    public static function baseURL(): string
    {
        if ($envUrl = getenv('APP_URL')) {
            return rtrim($envUrl, '/');
        }

        // Detect protocol
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                    ? 'https://' : 'http://';
        
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        
        // Find base path
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $base = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/\\');
        
        return $protocol . $host . $base;
    }
}
