<?php

class Database {
    private $pdo;

    public function __construct($config) {
        $driver = $config['driver'];
        
        if ($driver === 'sqlite') {
            $dsn = "$driver:" . $config['database'];
            $this->pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } else {
            $host = $config['host'];
            $port = $config['port'];
            $dbname = $config['dbname'];
            $user = $config['user'];
            $password = $config['password'];
            $sslmode = $config['sslmode'] ?? 'require';

            $dsn = "$driver:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";

            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
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
