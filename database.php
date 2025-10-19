<?php

class Database {
    private $pdo;

    public function __construct($config) {
        $driver = $config['driver'];
        
        if ($driver === 'sqlite') {
            $dsn = "$driver:" . $config['database'];
            try {
                $this->pdo = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } catch (PDOException $e) {
                throw new Exception("SQLite connection failed: " . $e->getMessage());
            }
        } else {
            $host = $config['host'];
            $port = $config['port'];
            $dbname = $config['dbname'];
            $user = $config['user'];
            $password = $config['password'];
            $sslmode = $config['sslmode'] ?? 'require';

            $dsn = "$driver:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";

            try {
                $this->pdo = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10 // 10 second timeout
                ]);
            } catch (PDOException $e) {
                // Check if it's a hostname resolution error
                if (strpos($e->getMessage(), 'could not translate host name') !== false) {
                    throw new Exception("Database connection failed: Cannot resolve hostname '$host'. Please check your Supabase configuration or use SQLite fallback by setting USE_SQLITE=true in your .env file.");
                }
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function query($sql, $class = null, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($class) {
            return $stmt->fetchAll(PDO::FETCH_CLASS, $class);
        }

        return $stmt;
    }

    public function fetchOne($sql, $class = null, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($class) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
            return $stmt->fetch();
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
