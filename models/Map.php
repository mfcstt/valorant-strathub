<?php

class Map
{
  public $id;
  public $name;
  public $image;
  public $created_at;

  public function query($where, $params)
  {
    $database = new Database(config('database'));

    return $database->query(
      query: "SELECT * FROM maps WHERE $where",
      class: self::class,
      params: $params
    );
  }

  public static function get($map_id)
  {
    return (new self)->query('id = :map_id', compact('map_id'))->fetch();
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
