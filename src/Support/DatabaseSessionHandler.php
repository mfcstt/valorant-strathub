<?php

declare(strict_types=1);

namespace App\Support;

use SessionHandlerInterface;

/**
 * Sessão persistida no banco, em vez de arquivo local.
 *
 * ## Por que existe
 *
 * O handler padrão do PHP grava sessão em arquivo (`session.save_path`,
 * normalmente `/tmp`) e presume que a mesma máquina atenda a próxima
 * requisição. Isso é verdade num servidor Apache/PHP-FPM de processo longo,
 * mas não na Vercel: cada requisição pode cair numa instância serverless
 * diferente, sem disco compartilhado entre elas. Na prática, a sessão criada
 * no GET de uma página deixava de existir quando o POST do formulário
 * chegava - o token CSRF gerado na hora de desenhar a página não batia mais
 * com nada, e todo formulário do site (login, cadastro, criar estratégia,
 * avaliar, favoritar) devolvia 419 mesmo com a pessoa fazendo tudo certo.
 *
 * Movendo a sessão para o banco, ela existe independente de qual instância
 * atende cada requisição.
 */
final class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    // A interface declara `string|false`, mas esta implementação nunca falha
    // de um jeito que justifique `false` - string vazia já cobre "sem dados".
    // PHP permite estreitar o tipo de retorno de forma covariante.
    public function read(string $id): string
    {
        $row = $this->database->first(
            'SELECT payload FROM sessions WHERE id = :id',
            ['id' => $id],
        );

        // String vazia (não false) sinaliza "sessão nova, sem dados ainda" -
        // é o que o PHP espera para um id que ainda não tem registro.
        return is_array($row) ? (string) $row['payload'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $now = time();

        // Upsert escrito como existência + branch, e não como
        // `INSERT ... ON CONFLICT` (Postgres) ou `INSERT OR REPLACE` (SQLite),
        // porque a sintaxe diverge entre os dois drivers que o projeto suporta.
        $exists = $this->database->scalar('SELECT 1 FROM sessions WHERE id = :id', ['id' => $id]);

        if ($exists !== false) {
            $this->database->execute(
                'UPDATE sessions SET payload = :payload, last_activity = :now WHERE id = :id',
                ['payload' => $data, 'now' => $now, 'id' => $id],
            );
        } else {
            $this->database->execute(
                'INSERT INTO sessions (id, payload, last_activity) VALUES (:id, :payload, :now)',
                ['id' => $id, 'payload' => $data, 'now' => $now],
            );
        }

        return true;
    }

    public function destroy(string $id): bool
    {
        $this->database->execute('DELETE FROM sessions WHERE id = :id', ['id' => $id]);

        return true;
    }

    public function gc(int $max_lifetime): int
    {
        return $this->database->execute(
            'DELETE FROM sessions WHERE last_activity < :cutoff',
            ['cutoff' => time() - $max_lifetime],
        );
    }
}
