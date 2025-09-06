<?php

return [
  'database' => [
    // MySql 
    /*
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'gerenciadorFilmes',
    'user' => 'root',
    'charset' => 'utf8mb4'
    */

    // SQLite 
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database.sqlite',
  ],
];