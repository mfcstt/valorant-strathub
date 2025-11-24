<?php

unset($_SESSION["flash_validations_login"]);

// Permitir acesso como visitante; bloquear apenas ações que exigem login

// Upload de avatar de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    // Se não estiver autenticado, redireciona para login
    if (!auth()) {
        flash()->put('error', 'Faça login para alterar seu avatar.');
        header('Location: /login');
        exit();
    }

    $userId = auth()->id;
    $file = $_FILES['avatar'] ?? null;

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        // Validações básicas alinhadas ao serviço de storage
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSizeBytes = 5 * 1024 * 1024; // 5MB

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            flash()->put('error', 'Formato de imagem inválido. Use JPG, PNG ou WEBP.');
            header('Location: /explore');
            exit();
        }

        if ($file['size'] > $maxSizeBytes) {
            flash()->put('error', 'Imagem muito grande. Limite de 5MB.');
            header('Location: /explore');
            exit();
        }

        // Faz upload para Supabase e salva metadados na tabela images
        try {
            $storageService = new SupabaseStorageService();
            $uploadedImage = $storageService->uploadImage($file, $userId);

            if (!$uploadedImage) {
                flash()->put('error', 'Falha ao enviar avatar para o armazenamento.');
                header('Location: /explore');
                exit();
            }

            $publicUrl = $uploadedImage->getPublicUrl();

            // Atualiza avatar no banco guardando a URL pública
            $userModel = new User();
            $updated = $userModel->update($userId, [
                'name' => auth()->name,
                'email' => auth()->email,
                'avatar' => $publicUrl
            ]);

            if ($updated) {
                // Atualiza sessão
                $_SESSION['auth']->avatar = $updated->avatar;
                flash()->put('message', 'Avatar atualizado com sucesso!');
            } else {
                flash()->put('error', 'Não foi possível atualizar seu avatar no banco.');
            }
        } catch (Exception $e) {
            flash()->put('error', 'Erro ao atualizar avatar: ' . $e->getMessage());
        }
    } else if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        flash()->put('error', 'Erro no upload da imagem (código ' . $file['error'] . ').');
    }

    header('Location: /explore');
    exit();
}

$message = $_REQUEST['message'] ?? '';
$search = $_REQUEST['pesquisar'] ?? '';
$order = $_REQUEST['ordenar'] ?? 'mais_estrelas';

$filter_agent = $_REQUEST['filtro_agente'] ?? '';
$filter_map = $_REQUEST['filtro_mapa'] ?? '';
$filter_category = $_REQUEST['filtro_categoria'] ?? '';

$agents = Agent::all();
$maps = Map::all();
$categories = ['defesa', 'ataque', 'pós plant', 'retake'];

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Buscar estratégias paginadas
$estrategias = Estrategia::filterPaginated($search, $filter_agent ?: null, $filter_map ?: null, $filter_category ?: null, null, $per_page, $offset);

// Total para paginação
$total = Estrategia::countFiltered($search, $filter_agent ?: null, $filter_map ?: null, $filter_category ?: null, null);
$total_pages = max(1, (int)ceil($total / $per_page));

// Ordenação dinâmica conforme seleção do usuário
if (is_array($estrategias)) {
    usort($estrategias, function($a, $b) use ($order) {
        switch ($order) {
            case 'mais_avaliadas':
                if ($a->ratings_count === $b->ratings_count) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $b->ratings_count <=> $a->ratings_count;
            case 'recentes':
                $aTime = strtotime($a->created_at);
                $bTime = strtotime($b->created_at);
                if ($aTime === $bTime) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $bTime <=> $aTime;
            case 'menos_estrelas':
                if ($a->rating_average === $b->rating_average) {
                    return $a->ratings_count <=> $b->ratings_count;
                }
                return $a->rating_average <=> $b->rating_average;
            case 'mais_estrelas':
            default:
                if ($a->rating_average === $b->rating_average) {
                    return $b->ratings_count <=> $a->ratings_count;
                }
                return $b->rating_average <=> $a->rating_average;
        }
    });
}

// Marcar favoritas se autenticado
if (auth() && is_array($estrategias)) {
    foreach ($estrategias as $e) {
        $e->is_favorite = Favorite::isFavorite(auth()->id, $e->id);
    }
}

view('app', compact('message', 'estrategias', 'search', 'order', 'agents', 'maps', 'categories', 'filter_agent', 'filter_map', 'filter_category', 'page', 'total_pages'), 'explore');
