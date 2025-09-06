<?php

class Estrategia
{
  public $id;
  public $title;
  public $category;
  public $description;
  public $cover;
  public $user_id;
  public $agent_id;
  public $agent_name;
  public $agent_photo;
  public $created_at;
  public $rating_average;
  public $ratings_count;

  public function query($where, $params)
  {
    $database = new Database(config('database'));

    return $database->query(
      query: "select 
                e.id, e.title, e.category, e.description, e.cover, e.user_id, e.agent_id, e.created_at,
                a.name as agent_name, a.photo as agent_photo,
                ifnull(sum(r.rating) / count(r.id), 0) as rating_average,
                ifnull(count(r.id), 0) as ratings_count
              from
                estrategias e
                left join agents a on a.id = e.agent_id
                left join ratings r on r.estrategia_id = e.id
              where 
                $where
              group by
                e.id, e.title, e.category, e.description, e.cover, e.user_id, e.agent_id, e.created_at, a.name, a.photo",
      class: self::class,
      params: $params
    );
  }

  public static function get($estrategia_id)
  {
    return (new self)->query('e.id = :estrategia_id', compact('estrategia_id'))->fetch();
  }

  public static function all($search = '')
  {
    return (new self)->query('e.title like :filter or e.category like :filter or a.name like :filter', ['filter' => "%$search%"])->fetchAll();
  }

  public static function myEstrategias($user_id)
  {
    return (new self)->query('e.user_id = :user_id', compact('user_id'))->fetchAll();
  }
}