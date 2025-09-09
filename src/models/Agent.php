<?php

class Agent
{
  public $id;
  public $name;
  public $photo;
  public $created_at;

  public function query($where, $params)
  {
    $database = new Database(config('database'));

    return $database->query(
      "SELECT * FROM agents WHERE $where",
      self::class,
      $params
    );
  }

  public static function get($agent_id)
  {
    $result = (new self)->query('id = :agent_id', compact('agent_id'));
    return is_array($result) ? ($result[0] ?? null) : $result;
  }

  public static function all()
  {
    return (new self)->query('1 = 1', []);
  }

  public static function getByName($name)
  {
    $result = (new self)->query('name = :name', compact('name'));
    return is_array($result) ? ($result[0] ?? null) : $result;
  }
}
