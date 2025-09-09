<?php

return [
    'database' => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? '***REDIGIDO***',
        'port' => $_ENV['DB_PORT'] ?? '6543',
        'dbname' => $_ENV['DB_NAME'] ?? 'postgres',
        'user' => $_ENV['DB_USER'] ?? '***REDIGIDO***',
        'password' => $_ENV['DB_PASSWORD'] ?? '***REDIGIDO***',
        'sslmode' => 'require',
    ],
];
