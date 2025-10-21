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

    // Validar dados
    $validation = Validation::validate([
        'titulo' => ['required', 'min:3', 'max:255'],
        'descricao' => ['required', 'min:10'],
        'agente' => ['required'],
        'mapa' => ['required']
    ], $_POST);

    $map_id = $_POST['mapa'] ?? '';

    if ($validation->notPassed()) {
        header('Location: /strategy-create');
        exit;
    }

    $cover_image_id = null;
    if ($fileCover) {
        try {
            // Upload da imagem para o Supabase Storage
            $storageService = new SupabaseStorageService();
            $uploadedImage = $storageService->uploadImage($fileCover, $user_id);
            
            if (!$uploadedImage) {
                flash()->put('error', 'Erro ao fazer upload da imagem. Tente novamente.');
                flash()->put('formData', $_POST);
                header('Location: /strategy-create');
                exit();
            }
            
            $cover_image_id = $uploadedImage->id;
        } catch (Exception $e) {
            flash()->put('error', 'Erro ao fazer upload da imagem: ' . $e->getMessage());
            flash()->put('formData', $_POST);
            header('Location: /strategy-create');
            exit();
        }
    }

    try {
        $database = new Database(config('database')['database']);

        $database->query(
            "INSERT INTO estrategias (title, category, description, cover_image_id, user_id, agent_id, map_id) 
            VALUES (:title, :category, :description, :cover_image_id, :user_id, :agent_id, :map_id)",
            null,
            compact('title', 'category', 'description', 'cover_image_id', 'user_id', 'agent_id', 'map_id')
        );
    } catch (Exception $e) {
        flash()->put('error', 'Erro ao salvar estratégia: ' . $e->getMessage());
        flash()->put('formData', $_POST);
        header('Location: /strategy-create');
        exit();
    }

    unset($_FILES);
    unset($_POST);

    flash()->put('message', 'Estratégia adicionada com sucesso!');
    header('Location: /myStrategy');
    exit();
}

$agents = Agent::all();
$maps = Map::all();

view("app", compact('agents', 'maps'), "strategy-create");
