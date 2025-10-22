<?php

// Redireciona visitantes para login na criação de estratégia
if (!auth()) {
    flash()->put('error', 'Faça login para cadastrar uma estratégia.');
    header('Location: /login');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG: Iniciando processamento POST para criação de estratégia");
    error_log("DEBUG: POST data: " . print_r($_POST, true));
    error_log("DEBUG: FILES data: " . print_r($_FILES, true));
    
    $user_id = auth()->id;
    $title = $_POST['titulo'] ?? '';
    $category = $_POST['categoria'] ?? '';
    $agent_id = $_POST['agente'] ?? '';
    $description = $_POST['descricao'] ?? '';
    $fileCover = $_FILES['capa'] ?? null;
    $fileVideo = $_FILES['video'] ?? null;

    $_POST["capa"] = $fileCover['name'] ?? '';
    $_POST["video"] = $fileVideo['name'] ?? '';

    // Validar dados
    $validation = Validation::validate([
        'titulo' => ['required', 'min:3', 'max:255'],
        'categoria' => ['required'],
        'descricao' => ['required', 'min:10'],
        'agente' => ['required'],
        'mapa' => ['required']
    ], $_POST);

    // Validar categoria contra lista permitida
    $allowedCategories = ['defesa', 'ataque', 'pós plant', 'retake'];
    if ($category !== '' && !in_array($category, $allowedCategories, true)) {
        $validation->addValidationMessage('categoria', 'Categoria inválida, selecione uma opção válida');
    }

    $map_id = $_POST['mapa'] ?? '';

    error_log("DEBUG: Dados recebidos - titulo: $title, categoria: $category, agente: $agent_id, mapa: $map_id");
    error_log("DEBUG: Arquivos recebidos - capa: " . ($fileCover ? $fileCover['name'] : 'nenhum') . ", video: " . ($fileVideo ? $fileVideo['name'] : 'nenhum'));

    // Validação: pelo menos imagem OU vídeo deve ser enviado
    $hasCover = $fileCover && (int)($fileCover['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    $hasVideo = $fileVideo && (int)($fileVideo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    if (!$hasCover && !$hasVideo) {
        $validation->addValidationMessage('capa', 'Insira uma imagem ou um vídeo para criar a estratégia.');
        $validation->addValidationMessage('video', 'Insira uma imagem ou um vídeo para criar a estratégia.');
    }

    if ($validation->notPassed()) {
        flash()->put('formData', $_POST);
        header('Location: /strategy-create');
        exit();
    }

    $cover_image_id = null;
    if ($hasCover) {
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

    $video_id = null;
    if ($hasVideo) {
        try {
            // Upload do vídeo para o Supabase Storage
            $storageService = new SupabaseStorageService();
            $uploadedVideo = $storageService->uploadVideo($fileVideo, $user_id);
            
            if (!$uploadedVideo) {
                flash()->put('error', 'Erro ao fazer upload do vídeo. Tente novamente.');
                flash()->put('formData', $_POST);
                header('Location: /strategy-create');
                exit();
            }
            
            $video_id = $uploadedVideo->id;
        } catch (Exception $e) {
            flash()->put('error', 'Erro ao fazer upload do vídeo: ' . $e->getMessage());
            flash()->put('formData', $_POST);
            header('Location: /strategy-create');
            exit();
        }
    }

    try {
        error_log("DEBUG: Iniciando inserção no banco de dados");
        error_log("DEBUG: cover_image_id: $cover_image_id, video_id: $video_id");
        $database = new Database(config('database')['database']);

        $database->query(
            "INSERT INTO estrategias (title, category, description, cover_image_id, video_id, user_id, agent_id, map_id) 
            VALUES (:title, :category, :description, :cover_image_id, :video_id, :user_id, :agent_id, :map_id)",
            null,
            compact('title', 'category', 'description', 'cover_image_id', 'video_id', 'user_id', 'agent_id', 'map_id')
        );
        
        error_log("DEBUG: Estratégia inserida com sucesso no banco de dados");
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
