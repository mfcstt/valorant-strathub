<?php

if (!auth()) {
    abort(403, 'Você precisa estar logado para acessar essa página.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = auth()->id;
    $title = $_POST['titulo'] ?? '';
    $category = $_POST['categoria'] ?? '';
    $agent_id = $_POST['agente'] ?? '';
    $description = $_POST['descricao'] ?? '';
    $fileCover = $_FILES['capa'] ?? null;

    $_POST["capa"] = $fileCover['name'] ?? '';

    $validation = Validation::validate([
        'titulo' => ['required', 'min:3'],
        'categoria' => ['required'],
        'agente' => ['required'],
        'descricao' => ['required', 'min:10'],
        'capa' => ['required'],
    ], $_POST);

    if ($validation->notPassed()) {
        flash()->put('formData', $_POST);
        header('Location: /strategy-create');
        exit();
    }

    $cover = '';
    if ($fileCover) {
        $randomName = md5(rand());
        $extension = pathinfo($fileCover['name'], PATHINFO_EXTENSION);
        $cover = "$randomName.$extension";
        move_uploaded_file($fileCover['tmp_name'], __DIR__ . '/../../public/assets/images/covers/' . $cover);
    }

    $database = new Database(config('database'));

    $database->query(
        "INSERT INTO estrategias (title, category, description, cover, user_id, agent_id) 
        VALUES (:title, :category, :description, :cover, :user_id, :agent_id)",
        null,
        compact('title', 'category', 'description', 'cover', 'user_id', 'agent_id')
    );

    unset($_FILES);
    unset($_POST);

    flash()->put('message', 'Estratégia adicionada com sucesso!');
    header('Location: /myStrategy');
    exit();
}

$agents = Agent::all();
$maps = Map::all();

view("app", compact('agents', 'maps'), "strategy-create");
