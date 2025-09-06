<?php

if (!auth()) {
  abort(403, 'Você precisa estar logado para acessar essa página.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = auth()->id;
  $title = $_POST['titulo'];
  $category = $_POST['categoria'];
  $agent_id = $_POST['agente'];
  $mapa = $_POST['mapa'];
  $description = $_POST['descricao'];
  $fileCover = $_FILES['capa'];
  $_POST["capa"] = $fileCover['name'];
  
  $randomName = md5(rand());
  $extension = pathinfo($fileCover['name'], PATHINFO_EXTENSION);
  $cover =  "$randomName.$extension";
  move_uploaded_file($fileCover['tmp_name'], __DIR__ . '/../../public/assets/images/covers/' . $cover);

  $validation = Validation::validate([
    'titulo' => ['required', 'min:3'],
    'categoria' => ['required'],
    'agente' => ['required'],
    'mapa' => ['required'],
    'descricao' => ['required', 'min:10'],
    'capa' => ['required'],
  ], $_POST);

  if ($validation->notPassed()) {
    // Armazenar valores do form na SESSION.
    flash()->put('formData', $_POST);

    header('location: /strategy-create');
    exit();
  }

  // Se passar de tudo isso, vamos adiconar a estratégia no BD
  $database->query(
    query: 
      "insert into estrategias (title, category, description, cover, user_id, agent_id, mapa) 
      values (:title, :category, :description, :cover, :user_id, :agent_id, :mapa)",
    params: compact('title', 'category', 'description', 'cover', 'user_id', 'agent_id', 'mapa')
  );

  unset($_FILES);
  unset($_POST);

  flash()->put('message', 'Estratégia adicionada com sucesso!');

  header('location: /myStrategy');
  exit();
}

$agents = Agent::all();
$maps = Map::all();

view("app", compact('agents', 'maps'), "strategy-create");