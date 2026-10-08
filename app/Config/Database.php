<?php
namespace App\Config;

class Database
{
    public static function getConfig(): array
    {
        // 1. Check for standard DATABASE_URL (Render MySQL / PlanetScale / Supabase / Aiven)
        $databaseUrl = getenv('DATABASE_URL') ?: (getenv('CLEARDB_DATABASE_URL') ?: '');
        if ($databaseUrl) {
            $parsed = parse_url($databaseUrl);
            if ($parsed && isset($parsed['host'])) {
                return [
                    'driver'   => 'mysql',
                    'host'     => $parsed['host'],
                    'port'     => $parsed['port'] ?? 3306,
                    'user'     => $parsed['user'] ?? 'root',
                    'password' => $parsed['pass'] ?? '',
                    'database' => ltrim($parsed['path'] ?? '', '/'),
                    'charset'  => 'utf8mb4',
                ];
            }
        }

        // 2. Check for explicit environment variables
        $dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1');
        $dbUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
        $dbPass = getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: ($_ENV['DB_PASS'] ?? ''));
        $dbName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'pharmacy_db');
        $dbPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306);
        $dbDriver = getenv('DB_DRIVER') ?: ($_ENV['DB_DRIVER'] ?? 'mysql');

        return [
            'driver'   => $dbDriver,
            'host'     => $dbHost,
            'port'     => (int)$dbPort,
            'user'     => $dbUser,
            'password' => $dbPass,
            'database' => $dbName,
            'charset'  => 'utf8mb4',
            'sqlite_path' => dirname(__DIR__, 2) . '/database/pharmacy.sqlite',
        ];
    }
}
