<?php

class Map
{
  public $id;
  public $name;
  public $image;
  public $created_at;

  public function query($where, $params)
  {
    $database = new Database(config('database')['database']);

    return $database->query(
      "SELECT * FROM maps WHERE $where",
      self::class,
      $params
    );
  }

  public static function get($map_id)
  {
    $result = (new self)->query('id = :map_id', compact('map_id'));
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
