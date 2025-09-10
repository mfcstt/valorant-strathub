<?php

class Database
{
    private $pdo;

    public function __construct()
    {
        // Lê variáveis de ambiente do Render
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 5432; // Porta padrão do PostgreSQL
        $dbname = getenv('DB_NAME') ?: 'postgres';
        $user = getenv('DB_USER') ?: '';
        $password = getenv('DB_PASS') ?: '';
        $sslmode = 'require'; // Supabase exige SSL

        // DSN para PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false
            ]);
        } catch (PDOException $e) {
            // Log detalhado para debug
            error_log("Erro ao conectar no banco de dados: " . $e->getMessage());
            die("Não foi possível conectar ao banco de dados. Verifique as variáveis de ambiente e a porta (5432).");
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
