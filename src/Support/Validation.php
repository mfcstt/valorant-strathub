<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Validação declarativa dos formulários.
 *
 * ## O que mudou em relação à versão anterior
 *
 * - As mensagens eram herdadas de um projeto de catálogo de filmes/livros
 *   (havia uma regra sobre "ano de lançamento" e a mensagem "Livros do futuro
 *   são inválidos"). Agora falam do domínio de estratégias.
 * - `unique` interpolava o nome da tabela e da coluna direto no SQL. Identificadores
 *   não podem ser parametrizados por prepared statement, então aqui eles são
 *   conferidos contra uma allowlist antes de entrar na consulta.
 * - `unique` abria uma conexão nova ao banco a cada chamada; hoje usa a conexão
 *   compartilhada da requisição.
 */
final class Validation
{
    /**
     * Pares tabela.coluna aceitos pela regra `unique`. Como identificadores SQL
     * não podem ser parametrizados, a única defesa segura é a lista fechada.
     *
     * @var array<string, list<string>>
     */
    private const UNIQUE_ALLOWLIST = [
        'users' => ['email'],
    ];

    /**
     * Rótulo e gênero de cada campo, para as mensagens saírem com a
     * concordância certa em português.
     *
     * @var array<string, array{0: string, 1: 'm'|'f'}>
     */
    private const LABELS = [
        'nome' => ['nome', 'm'],
        'email' => ['e-mail', 'm'],
        'senha' => ['senha', 'f'],
        'nova_senha' => ['nova senha', 'f'],
        'confirmar_senha' => ['confirmação de senha', 'f'],
        'elo' => ['elo', 'm'],
        'titulo' => ['título', 'm'],
        'categoria' => ['categoria', 'f'],
        'descricao' => ['descrição', 'f'],
        'agente' => ['agente', 'm'],
        'mapa' => ['mapa', 'm'],
        'capa' => ['imagem de capa', 'f'],
        'video' => ['vídeo', 'm'],
        'avaliacao' => ['avaliação', 'f'],
        'comentario' => ['comentário', 'm'],
    ];

    /** @var array<string, list<string>> */
    private array $errors = [];

    /**
     * @param array<string, list<string>> $rules  campo => lista de regras
     * @param array<string, mixed>        $data   dados da requisição
     */
    public static function validate(array $rules, array $data): self
    {
        $validation = new self();

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $value = $data[$field] ?? '';
                $value = is_scalar($value) ? (string) $value : '';

                [$name, $argument] = str_contains($rule, ':')
                    ? explode(':', $rule, 2)
                    : [$rule, null];

                $validation->apply($name, $field, $value, $argument);
            }
        }

        return $validation;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Guarda os erros no flash para a próxima renderização do formulário.
     *
     * @param string|null $form sufixo quando há mais de um formulário na página
     */
    public function flashErrors(?string $form = null): bool
    {
        $key = $form !== null ? "validations_{$form}" : 'validations';

        flash()->put($key, $this->errors);

        return $this->fails();
    }

    private function apply(string $rule, string $field, string $value, ?string $argument): void
    {
        match ($rule) {
            'required' => $this->required($field, $value),
            'email' => $this->email($field, $value),
            'min' => $this->min($field, $value, (int) $argument),
            'max' => $this->max($field, $value, (int) $argument),
            'strong' => $this->strong($field, $value),
            'unique' => $this->unique($field, $value, (string) $argument),
            'in' => $this->in($field, $value, (string) $argument),
            'integer' => $this->integer($field, $value),
            'between' => $this->between($field, $value, (string) $argument),
            default => throw new InvalidArgumentException("Regra de validação desconhecida: {$rule}"),
        };
    }

    private function required(string $field, string $value): void
    {
        if (trim($value) === '') {
            $this->addError($field, sprintf(
                '%s %s é obrigatóri%s.',
                $this->article($field),
                $this->label($field),
                $this->gender($field) === 'f' ? 'a' : 'o',
            ));
        }
    }

    private function email(string $field, string $value): void
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, 'Informe um e-mail válido.');
        }
    }

    private function min(string $field, string $value, int $min): void
    {
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->addError($field, sprintf(
                '%s %s deve ter no mínimo %d caracteres.',
                $this->article($field),
                $this->label($field),
                $min,
            ));
        }
    }

    private function max(string $field, string $value, int $max): void
    {
        if (mb_strlen($value) > $max) {
            $this->addError($field, sprintf(
                '%s %s deve ter no máximo %d caracteres.',
                $this->article($field),
                $this->label($field),
                $max,
            ));
        }
    }

    /**
     * Exige ao menos um caractere não alfanumérico.
     */
    private function strong(string $field, string $value): void
    {
        if ($value === '') {
            return;
        }

        if (preg_match('/[^\p{L}\p{N}]/u', $value) !== 1) {
            $this->addError($field, sprintf(
                '%s %s deve conter ao menos um caractere especial (por exemplo @, #, ! ou _).',
                $this->article($field),
                $this->label($field),
            ));
        }
    }

    /**
     * @param string $argument no formato `tabela,coluna` (a coluna assume o
     *                         nome do campo quando omitida)
     */
    private function unique(string $field, string $value, string $argument): void
    {
        if ($value === '') {
            return;
        }

        [$table, $column] = array_pad(explode(',', $argument, 2), 2, null);
        $table = (string) $table;
        $column = $column ?? $field;

        if (!isset(self::UNIQUE_ALLOWLIST[$table]) || !in_array($column, self::UNIQUE_ALLOWLIST[$table], true)) {
            throw new InvalidArgumentException(
                "Par {$table}.{$column} não está na allowlist da regra 'unique'."
            );
        }

        $exists = Database::connection()->scalar(
            "SELECT 1 FROM {$table} WHERE {$column} = :value LIMIT 1",
            ['value' => $value],
        );

        if ($exists !== false) {
            $this->addError($field, sprintf('Este %s já está cadastrado.', $this->label($field)));
        }
    }

    /**
     * @param string $argument valores aceitos, separados por vírgula
     */
    private function in(string $field, string $value, string $argument): void
    {
        $allowed = array_map('trim', explode(',', $argument));

        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, sprintf('Selecione %s %s válid%s.',
                $this->gender($field) === 'f' ? 'uma' : 'um',
                $this->label($field),
                $this->gender($field) === 'f' ? 'a' : 'o',
            ));
        }
    }

    private function integer(string $field, string $value): void
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, sprintf(
                '%s %s deve ser um número inteiro.',
                $this->article($field),
                $this->label($field),
            ));
        }
    }

    /**
     * @param string $argument no formato `min,max`
     */
    private function between(string $field, string $value, string $argument): void
    {
        if ($value === '') {
            return;
        }

        [$min, $max] = array_map('intval', array_pad(explode(',', $argument, 2), 2, '0'));
        $number = filter_var($value, FILTER_VALIDATE_INT);

        if ($number === false || $number < $min || $number > $max) {
            $this->addError($field, sprintf(
                '%s %s deve estar entre %d e %d.',
                $this->article($field),
                $this->label($field),
                $min,
                $max,
            ));
        }
    }

    private function label(string $field): string
    {
        return self::LABELS[$field][0] ?? str_replace('_', ' ', $field);
    }

    /** @return 'm'|'f' */
    private function gender(string $field): string
    {
        return self::LABELS[$field][1] ?? 'm';
    }

    private function article(string $field): string
    {
        return $this->gender($field) === 'f' ? 'A' : 'O';
    }
}
