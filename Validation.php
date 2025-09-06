<?php

class Validation
{
  public $validations = [];

  public static function validate($rules, $data)
  {

    $validation = new self;

    foreach ($rules as $field => $rulesField) {
      foreach ($rulesField as $rule) {
        $fieldValue = $data[$field];

        if (str_contains($rule, ':')) {
          $temp = explode(':', $rule);
          $rule = $temp[0];
          $ruleValue = $temp[1];
          $validation->$rule($field, $fieldValue, $ruleValue);
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

  private function unique($field, $value, $table)
  {
    if (strlen($value) > 0) {
    }

    $db = new Database(config('database'));

    $result = $db->query(
      query: "select * from $table where $field = :value",
      params: ['value' => $value]
    )->fetch();

    if ($result) {
      $this->addValidationMessage($field, "Esse $field já está registrado.");
    }
  }

  private function required($field, $value)
  {
    if (strlen(trim($value)) == 0) {
      switch ($field) {
        case 'senha':
        case 'categoria':
        case 'capa':
          $error = "A $field é obrigatória.";
          break;
        case 'avaliacao':
          $error = "A avaliação é obrigatória.";
          break;
        case 'descricao':
          $error = "A descrição é obrigatória.";
          break;
        case 'comentario':
          $error = "O comentário é obrigatório.";
          break;
        case 'titulo':
          $error = "O título é obrigatório.";
          break;
        case 'ano_de_lancamento':
          $error = "O ano é obrigatório.";
          break;
        default:
          $error = "O $field é obrigatório.";
          break;
      }

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
    if ($field == 'ano_de_lancamento' && $value != '') {
      if ($value < $min) {
        $this->addValidationMessage($field, "O ano deve ser após $min.");
      }
    }

    if (strlen($value) < $min && $field != 'ano_de_lancamento') {
      if ($field == 'titulo') {
        $this->addValidationMessage($field, "O título deve ter no mínimo $min caracteres.");
      } else if ($field == 'descricao') {
        $this->addValidationMessage($field, "A descrição deve ter no mínimo $min caracteres.");
      } else {
        $this->addValidationMessage($field, "A $field deve ter no mínimo $min caracteres.");
      }
    }
  }

  private function max($field, $value, $max)
  {
    if ($field == 'ano_de_lancamento' && $value > $max) {
      $this->addValidationMessage($field, "Livros do futuro são inválidos.");
    } else {
      if (strlen($value) > $max) {
        $this->addValidationMessage($field, "A $field deve ter no máximo $max caracteres.");
      }
    }
  }

  private function strong($field, $value)
  {
    if (! strpbrk($value, '@#$%&!?*-_+=/[](){};,.:|')) {
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

    if ($customName) {
      $key .= '_' . $customName;
    }

    flash()->put($key, $this->validations);

    return sizeof($this->validations) > 0;
  }
}
