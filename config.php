<?php

return [
    'database' => [
        'driver' => 'pgsql',
        'host' => getenv('DB_HOST') ?: 'aws-1-sa-east-1.pooler.supabase.com',
        'port' => getenv('DB_PORT') ?: '6543',
        'dbname' => getenv('DB_NAME') ?: 'postgres',
        'user' => getenv('DB_USER') ?: 'postgres.bblwxvyyzbusvszyoodt',
        'password' => getenv('DB_PASSWORD') ?: 'VavazinhoHoje',
        'sslmode' => 'require',
    ],
];
