<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  header('location: /');
  exit();
}

$user_id = auth()->id;
$user_name = auth()->name;
$user_avatar = auth()->avatar;
$estrategia_id = $_POST['estrategia_id'];
$rating = $_POST['avaliacao'];
$comment = $_POST['comentario'];

$validation = Validation::validate([
  'avaliacao' => ['required'],
  'comentario' => ['required']
], $_POST);

if ($validation->notPassed()) {
  // Armazenar valores do form na SESSION.
  flash()->put('formData', $_POST);

  header('location: /strategy?id=' . $estrategia_id);
  exit();
}

$database->query(
  "insert into ratings ( user_id, user_name, user_avatar, estrategia_id, rating, comment )
  values ( :user_id, :user_name, :user_avatar, :estrategia_id, :rating, :comment );",
  params: compact('user_id', 'user_name', 'user_avatar', 'estrategia_id', 'rating', 'comment')
);

flash()->put('message', 'Avaliação realizada com sucesso!');

header('location: /strategy?id=' . $estrategia_id);
exit();
