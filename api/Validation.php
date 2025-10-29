<?php

class Validation
{
    public $validations = [];

    public static function validate($rules, $data)
    {
        $validation = new self;

        foreach ($rules as $field => $rulesField) {
            foreach ($rulesField as $rule) {
                $fieldValue = $data[$field] ?? '';

                if (str_contains($rule, ':')) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                    $validation->$ruleName($field, $fieldValue, $ruleValue);
                } else {
                    $validation->$rule($field, $fieldValue);
                }
            }
        }

        return $validation;
    }

    public function addValidationMessage($field, $error)
    {
        if (!isset($this->validations[$field])) {
            $this->validations[$field] = [];
        }
        $this->validations[$field][] = $error;
    }

    // Validar se o valor é único no banco
    private function unique($field, $value, $table)
    {
        if (strlen($value) === 0) return;

        $db = new Database(config('database')['database']);

        $result = $db->query(
            "SELECT * FROM $table WHERE $field = :value",
            null, // não precisamos de mapeamento de classe
            ['value' => $value]
        );

        $existing = $result->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $this->addValidationMessage($field, "Esse $field já está registrado.");
        }
    }

    private function required($field, $value)
    {
        if (strlen(trim($value)) == 0) {
            $error = match($field) {
                'senha', 'categoria', 'capa' => "A $field é obrigatória.",
                'avaliacao' => "A avaliação é obrigatória.",
                'descricao' => "A descrição é obrigatória.",
                'comentario' => "O comentário é obrigatório.",
                'titulo' => "O título é obrigatório.",
                'ano_de_lancamento' => "O ano é obrigatório.",
                default => "O $field é obrigatório."
            };
            $this->addValidationMessage($field, $error);
        }
    }

    private function email($field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addValidationMessage($field, "Email inválido.");
        }
    }

    private function min($field, $value, $min)
    {
        if ($field === 'ano_de_lancamento' && $value != '' && $value < $min) {
            $this->addValidationMessage($field, "O ano deve ser após $min.");
        } elseif (strlen($value) < $min && $field !== 'ano_de_lancamento') {
            $error = match($field) {
                'titulo' => "O título deve ter no mínimo $min caracteres.",
                'descricao' => "A descrição deve ter no mínimo $min caracteres.",
                default => "A $field deve ter no mínimo $min caracteres."
            };
            $this->addValidationMessage($field, $error);
        }
    }

    private function max($field, $value, $max)
    {
        if ($field === 'ano_de_lancamento' && $value > $max) {
            $this->addValidationMessage($field, "Livros do futuro são inválidos.");
        } elseif (strlen($value) > $max) {
            $this->addValidationMessage($field, "A $field deve ter no máximo $max caracteres.");
        }
    }

    private function strong($field, $value)
    {
        if (!strpbrk($value, '@#$%&!?*-_+=/[](){};,.:|')) {
            $this->addValidationMessage($field, "A $field deve ter algum Caractere Especial.");
        }
    }

    private function passwordMatch($field, $value, $hashedPassword)
    {
        if (!password_verify($value, $hashedPassword)) {
            $this->addValidationMessage($field, 'Email ou senha incorretos!');
        }
    }

    public function notPassed($customName = null)
    {
        $key = 'validations';
        if ($customName) $key .= '_' . $customName;

        flash()->put($key, $this->validations);

        return !empty($this->validations);
    }
}
