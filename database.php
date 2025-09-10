<?php

class Database
{
    private $pdo;

    public function __construct($config)
    {
        $driver = $config['driver'] ?? 'pgsql';
        $host = $config['host'] ?? '';
        $port = $config['port'] ?? 5432;
        $dbname = $config['dbname'] ?? 'postgres';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        $sslmode = $config['sslmode'] ?? 'require';

        $dsn = "$driver:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao conectar no banco de dados: " . $e->getMessage());
            die("Não foi possível conectar ao banco de dados. Verifique as configurações.");
        }
    }

    public function query($sql, $class = null, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($class) {
            return $stmt->fetchAll(PDO::FETCH_CLASS, $class);
        }

        return $stmt;
    }

    public function fetchOne($sql, $class = null, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($class) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
            return $stmt->fetch();
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
