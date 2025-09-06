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
      query: "SELECT * FROM agents WHERE $where",
      class: self::class,
      params: $params
    );
  }

  public static function get($agent_id)
  {
    return (new self)->query('id = :agent_id', compact('agent_id'))->fetch();
  }

  public static function all()
  {
    return (new self)->query('1 = 1', [])->fetchAll();
  }

  public static function getByName($name)
  {
    return (new self)->query('name = :name', compact('name'))->fetch();
  }
}
