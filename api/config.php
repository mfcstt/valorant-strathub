<?php

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Check if we should use SQLite fallback
$useSqlite = false;
if (isset($_ENV['USE_SQLITE']) && $_ENV['USE_SQLITE'] === 'true') {
    $useSqlite = true;
}

return [
    'database' => $useSqlite ? [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/public/database.sqlite'
    ] : [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'YOUR_DB_HOST',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? 'postgres',
        'user' => $_ENV['DB_USER'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? 'YOUR_DB_PASSWORD',
        'sslmode' => 'require',
    ],
];
