<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;

    if (class_exists(\Dotenv\Dotenv::class) && file_exists($projectRoot . '/.env')) {
        \Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

date_default_timezone_set((string) env('APP_TIMEZONE', 'Asia/Manila'));
ini_set('display_errors', env('APP_DEBUG', 'false') === 'true' ? '1' : '0');
error_reporting(E_ALL);

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $driver = (string) env('DB_CONNECTION', 'mysql');
        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $database = (string) env('DB_DATABASE', '');
        $username = (string) env('DB_USERNAME', '');
        $password = (string) env('DB_PASSWORD', '');

        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=utf8mb4', $driver, $host, $port, $database);

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    }
}

if (!function_exists('redis')) {
    function redis(): ?Redis
    {
        static $redis = null;
        static $attempted = false;

        if ($attempted) {
            return $redis;
        }

        $attempted = true;

        if (!extension_loaded('redis')) {
            return null;
        }

        try {
            $redis = new Redis();
            $redis->connect(
                (string) env('REDIS_HOST', '127.0.0.1'),
                (int) env('REDIS_PORT', 6379),
                2.0
            );
            $redis->ping();
        } catch (Exception $e) {
            $redis = null;
        }

        return $redis;
    }
}
