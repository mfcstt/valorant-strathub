<?php

if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$useSqlite = (($_ENV['USE_SQLITE'] ?? getenv('USE_SQLITE')) === 'true');

return [
    'database' => $useSqlite ? [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/public/database.sqlite'
    ] : [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'YOUR_DB_HOST',
        'port' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'postgres',
        'user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'YOUR_DB_PASSWORD',
        'sslmode' => 'require',
    ],
];
