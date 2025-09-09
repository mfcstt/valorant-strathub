<?php

return [
    'database' => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'aws-1-sa-east-1.pooler.supabase.com',
        'port' => $_ENV['DB_PORT'] ?? '6543',
        'dbname' => $_ENV['DB_NAME'] ?? 'postgres',
        'user' => $_ENV['DB_USER'] ?? 'postgres.bblwxvyyzbusvszyoodt',
        'password' => $_ENV['DB_PASSWORD'] ?? 'VavazinhoHoje',
        'sslmode' => 'require',
    ],
];
