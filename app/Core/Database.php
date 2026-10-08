<?php
namespace App\Core;

use App\Config\Database as DbConfig;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static string $activeDriver = 'mysql';

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = DbConfig::getConfig();
        $connected = false;

        // Try MySQL/TiDB first if configured
        if ($config['driver'] === 'mysql' && !empty($config['host'])) {
            try {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => (int)(getenv('DB_TIMEOUT') ?: 10),
                ];

                if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
                    $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
                }

                // TiDB Cloud / SSL Configuration
                if (!empty($config['ssl'])) {
                    $caPath = $config['ssl_ca'] ?? '';
                    if (!$caPath) {
                        $commonCaPaths = [
                            dirname(__DIR__, 2) . '/database/isrgrootx1.pem',
                            '/etc/ssl/certs/ca-certificates.crt', // Render / Debian / Ubuntu
                            '/etc/pki/tls/certs/ca-bundle.crt',  // CentOS / Fedora
                            '/etc/ssl/ca-bundle.pem',            // openSUSE
                        ];
                        foreach ($commonCaPaths as $path) {
                            if (file_exists($path)) {
                                $caPath = $path;
                                break;
                            }
                        }
                    }

                    if ($caPath && file_exists($caPath) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
                    }

                    if (isset($config['ssl_verify']) && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = (bool)$config['ssl_verify'];
                    }
                }

                $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
                self::$instance = $pdo;
                self::$activeDriver = 'mysql';
                $connected = true;
            } catch (PDOException $e) {
                error_log("TiDB/MySQL connection attempt notice: " . $e->getMessage());

                // If database doesn't exist (SQLSTATE 1049), try creating it
                if ($e->getCode() == 1049) {
                    try {
                        $rootDsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
                        $rootPdo = new PDO($rootDsn, $config['user'], $config['password'], $options);
                        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        
                        self::$instance = new PDO($dsn, $config['user'], $config['password'], $options);
                        self::$activeDriver = 'mysql';
                        $connected = true;
                    } catch (PDOException $e2) {
                        error_log("Database auto-creation failed: " . $e2->getMessage());
                    }
                }
            }
        }

        // If MySQL/TiDB not connected (e.g. offline dev without credentials), fallback to SQLite
        if (!$connected) {
            $sqlitePath = $config['sqlite_path'];
            $dir = dirname($sqlitePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec("PRAGMA foreign_keys = ON;");
            self::$instance = $pdo;
            self::$activeDriver = 'sqlite';
        }

        // Auto-initialize schema & seed data if tables do not exist
        self::ensureSchemaInitialized(self::$instance, self::$activeDriver);

        return self::$instance;
    }

    public static function getActiveDriver(): string
    {
        if (self::$instance === null) {
            self::getConnection();
        }
        return self::$activeDriver;
    }

    private static function ensureSchemaInitialized(PDO $pdo, string $driver): void
    {
        $hasTables = false;
        try {
            if ($driver === 'mysql') {
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                $hasTables = ($stmt && $stmt->rowCount() > 0);
            } else {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
                $hasTables = ($stmt && $stmt->fetchColumn() !== false);
            }
        } catch (\Exception $e) {
            $hasTables = false;
        }

        if (!$hasTables) {
            $baseDir = dirname(__DIR__, 2);
            $schemaFile = $driver === 'sqlite'
                ? $baseDir . '/database/sqlite_schema.sql'
                : $baseDir . '/database/schema.sql';
            $seedFile = $driver === 'sqlite'
                ? $baseDir . '/database/sqlite_seed.sql'
                : $baseDir . '/database/seed_data.sql';

            self::executeSqlFile($pdo, $schemaFile);
            self::executeSqlFile($pdo, $seedFile);
        }
    }

    private static function executeSqlFile(PDO $pdo, string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $sql = file_get_contents($filePath);
        if (empty(trim($sql))) {
            return;
        }

        try {
            $pdo->exec($sql);
        } catch (\Exception $e) {
            // Split and run statement by statement if bulk execution fails
            $cleaned = preg_replace('/--[^\n]*\n/', "\n", $sql);
            $statements = explode(';', $cleaned);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (\Exception $subEx) {
                        // Suppress non-breaking notice
                    }
                }
            }
        }
    }


    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder(self::getConnection(), $table);
    }

    public static function raw(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}

class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $select = ['*'];
    private array $wheres = [];
    private array $params = [];
    private array $joins = [];
    private array $orderBys = [];
    private ?int $limitCount = null;
    private ?int $offsetCount = null;

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function select(string ...$columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        if ($value === null) {
            $op = '=';
            $val = $operatorOrValue;
        } else {
            $op = $operatorOrValue;
            $val = $value;
        }

        $paramKey = ':w_' . count($this->params) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $this->wheres[] = "{$column} {$op} {$paramKey}";
        $this->params[$paramKey] = $val;

        return $this;
    }

    public function whereRaw(string $sql, array $params = []): self
    {
        $this->wheres[] = $sql;
        foreach ($params as $k => $v) {
            $this->params[$k] = $v;
        }
        return $this;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        if ($value === null) {
            $op = '=';
            $val = $operatorOrValue;
        } else {
            $op = $operatorOrValue;
            $val = $value;
        }

        $paramKey = ':w_' . count($this->params) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        if (empty($this->wheres)) {
            $this->wheres[] = "{$column} {$op} {$paramKey}";
        } else {
            $last = array_pop($this->wheres);
            $this->wheres[] = "({$last} OR {$column} {$op} {$paramKey})";
        }
        $this->params[$paramKey] = $val;

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            $this->wheres[] = "1 = 0";
            return $this;
        }
        $placeholders = [];
        foreach ($values as $i => $val) {
            $key = ':win_' . count($this->params) . '_' . $i;
            $placeholders[] = $key;
            $this->params[$key] = $val;
        }
        $this->wheres[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBys[] = "{$column} " . strtoupper($direction);
        return $this;
    }

    public function limit(int $limit, ?int $offset = null): self
    {
        $this->limitCount = $limit;
        if ($offset !== null) {
            $this->offsetCount = $offset;
        }
        return $this;
    }

    public function get(): array
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBys)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBys);
        }

        if ($this->limitCount !== null) {
            $sql .= " LIMIT {$this->limitCount}";
            if ($this->offsetCount !== null) {
                $sql .= " OFFSET {$this->offsetCount}";
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}";
        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }

    public function sum(string $column): float
    {
        $sql = "SELECT SUM({$column}) as total FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0.00);
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);

        $binds = [];
        foreach ($data as $col => $val) {
            $binds[':' . $col] = $val;
        }
        $stmt->execute($binds);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(array $data): bool
    {
        $sets = [];
        $binds = $this->params;

        foreach ($data as $col => $val) {
            $paramKey = ':set_' . preg_replace('/[^a-zA-Z0-9_]/', '', $col);
            $sets[] = "{$col} = {$paramKey}";
            $binds[$paramKey] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($binds);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->params);
    }
}
