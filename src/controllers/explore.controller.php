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

$estrategias = $search ? Estrategia::all($search) : Estrategia::all();

// Ordenação dinâmica conforme seleção do usuário
if (is_array($estrategias)) {
    usort($estrategias, function($a, $b) use ($order) {
        switch ($order) {
            case 'mais_avaliadas':
                // Mais avaliações primeiro; desempate por maior média
                if ($a->ratings_count === $b->ratings_count) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $b->ratings_count <=> $a->ratings_count;
            case 'recentes':
                // Mais recentes primeiro; desempate por maior média
                $aTime = strtotime($a->created_at);
                $bTime = strtotime($b->created_at);
                if ($aTime === $bTime) {
                    return $b->rating_average <=> $a->rating_average;
                }
                return $bTime <=> $aTime;
            case 'menos_estrelas':
                // Menor média primeiro; desempate por menos avaliações
                if ($a->rating_average === $b->rating_average) {
                    return $a->ratings_count <=> $b->ratings_count;
                }
                return $a->rating_average <=> $b->rating_average;
            case 'mais_estrelas':
            default:
                // Maior média primeiro; desempate por mais avaliações
                if ($a->rating_average === $b->rating_average) {
                    return $b->ratings_count <=> $a->ratings_count;
                }
                return $b->rating_average <=> $a->rating_average;
        }
    });
}

view('app', compact('message', 'estrategias', 'search', 'order'), 'explore');
