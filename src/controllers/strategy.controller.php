<?php

if (!auth()) {
  abort(403, 'Você precisa estar logado para acessar essa página.');
}

$estrategia_id = $_GET['id'];

$estrategia = Estrategia::get($estrategia_id);

$ratings = $database->query(
  query: "
    select 
      (SELECT count(*) FROM ratings countR WHERE countR.user_id = r.user_id ) as rated_movies, 
      r.*
    from
      ratings r 
    where 
      estrategia_id = :estrategia_id",
  class: Rating::class,
  params: compact('estrategia_id')
)->fetchAll();

$movie = $estrategia;
view("app", compact('movie', 'ratings'), "movie");
