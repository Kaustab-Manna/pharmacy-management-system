<?php
namespace App\Config;

class Database
{
    public static function getConfig(): array
    {
        // 1. Check for standard DATABASE_URL or TIDB_DATABASE_URL (TiDB Cloud / Render / PlanetScale / Aiven)
        $databaseUrl = getenv('TIDB_DATABASE_URL') ?: (getenv('DATABASE_URL') ?: (getenv('CLEARDB_DATABASE_URL') ?: ''));
        if ($databaseUrl) {
            $databaseUrl = trim($databaseUrl, "\"' \t\n\r");
            $parsed = parse_url($databaseUrl);
            if ($parsed && isset($parsed['host'])) {
                $query = [];
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $query);
                }
                $isTidb = str_contains($parsed['host'], 'tidbcloud.com') || (isset($parsed['port']) && (int)$parsed['port'] === 4000);
                $enableSsl = $isTidb || isset($query['ssl']) || isset($query['sslmode']) || (getenv('DB_SSL') === 'true') || (getenv('TIDB_ENABLE_SSL') !== 'false');

                return [
                    'driver'      => 'mysql',
                    'host'        => trim($parsed['host'], "\"' \t\n\r"),
                    'port'        => (int)($parsed['port'] ?? ($isTidb ? 4000 : 3306)),
                    'user'        => trim(urldecode($parsed['user'] ?? 'root'), "\"' \t\n\r"),
                    'password'    => trim(urldecode($parsed['pass'] ?? ''), "\"' \t\n\r"),
                    'database'    => trim(ltrim($parsed['path'] ?? '', '/'), "\"' \t\n\r"),
                    'charset'     => 'utf8mb4',
                    'ssl'         => $enableSsl,
                    'ssl_ca'      => getenv('TIDB_SSL_CA') ?: (getenv('DB_SSL_CA') ?: null),
                    'ssl_verify'  => getenv('TIDB_SSL_VERIFY') !== 'false' && getenv('DB_SSL_VERIFY') !== 'false',
                    'sqlite_path' => dirname(__DIR__, 2) . '/database/pharmacy.sqlite',
                ];
            }
        }

        // 2. Check for explicit TiDB environment variables (TIDB_HOST, TIDB_PORT, TIDB_USER, etc.)
        $tidbHost = trim(getenv('TIDB_HOST') ?: ($_ENV['TIDB_HOST'] ?? ''), "\"' \t\n\r");
        if ($tidbHost) {
            return [
                'driver'      => 'mysql',
                'host'        => $tidbHost,
                'port'        => (int)(getenv('TIDB_PORT') ?: ($_ENV['TIDB_PORT'] ?? 4000)),
                'user'        => trim(getenv('TIDB_USER') ?: ($_ENV['TIDB_USER'] ?? 'root'), "\"' \t\n\r"),
                'password'    => trim(getenv('TIDB_PASSWORD') ?: (getenv('TIDB_PASS') ?: ($_ENV['TIDB_PASSWORD'] ?? ($_ENV['TIDB_PASS'] ?? ''))), "\"' \t\n\r"),
                'database'    => trim(getenv('TIDB_DATABASE') ?: (getenv('TIDB_DB') ?: ($_ENV['TIDB_DATABASE'] ?? ($_ENV['TIDB_DB'] ?? 'pharmacy_db'))), "\"' \t\n\r"),
                'charset'     => 'utf8mb4',
                'ssl'         => getenv('TIDB_ENABLE_SSL') !== 'false',
                'ssl_ca'      => getenv('TIDB_SSL_CA') ?: null,
                'ssl_verify'  => getenv('TIDB_SSL_VERIFY') !== 'false',
                'sqlite_path' => dirname(__DIR__, 2) . '/database/pharmacy.sqlite',
            ];
        }

        // 3. Check for standard DB_* environment variables
        $dbHost = trim(getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ''), "\"' \t\n\r");
        $dbDriver = trim(getenv('DB_DRIVER') ?: ($_ENV['DB_DRIVER'] ?? 'sqlite'), "\"' \t\n\r");
        $dbPort = (int)(getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? 3306));
        $isTidb = str_contains((string)$dbHost, 'tidbcloud.com') || $dbPort === 4000;
        $enableSsl = $isTidb || (getenv('DB_SSL') === 'true') || (getenv('TIDB_ENABLE_SSL') === 'true');

        return [
            'driver'      => !empty($dbHost) ? ($dbDriver === 'sqlite' ? 'mysql' : $dbDriver) : $dbDriver,
            'host'        => $dbHost ?: '127.0.0.1',
            'port'        => $dbPort,
            'user'        => trim(getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'), "\"' \t\n\r"),
            'password'    => trim(getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: ($_ENV['DB_PASS'] ?? '')), "\"' \t\n\r"),
            'database'    => trim(getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'pharmacy_db'), "\"' \t\n\r"),
            'charset'     => 'utf8mb4',
            'ssl'         => $enableSsl,
            'ssl_ca'      => getenv('DB_SSL_CA') ?: null,
            'ssl_verify'  => getenv('DB_SSL_VERIFY') !== 'false',
            'sqlite_path' => dirname(__DIR__, 2) . '/database/pharmacy.sqlite',
        ];
    }
}
