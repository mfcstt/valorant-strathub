<?php

// Página de perfil: exibe dados do usuário e permite alterar avatar, nome e email

if (!auth()) {
    flash()->put('error', 'Faça login para acessar seu perfil.');
    header('Location: /login');
    exit();
}

$user_id = auth()->id;
$database = new Database(config('database')['database']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $userModel = new User();

    try {
        if ($action === 'update_info') {
            // Atualizar nome e email
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            // Validação simples
            $errors = [];
            if ($name === '' || strlen($name) < 2) {
                $errors[] = 'Informe um nome válido (mínimo 2 caracteres).';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Informe um email válido.';
            }

            if (!empty($errors)) {
                flash()->put('error', implode(' ', $errors));
                flash()->put('formData', ['name' => $name, 'email' => $email]);
                header('Location: /profile');
                exit();
            }

            // Preservar avatar atual e validar e-mail duplicado
            $currentUser = User::get($user_id);

            if ($email !== ($currentUser->email ?? '')) {
                $existing = User::getByEmail($email);
                if ($existing && (int)$existing->id !== (int)$user_id) {
                    flash()->put('error', 'Este e-mail já está em uso.');
                    flash()->put('formData', ['name' => $name, 'email' => $email]);
                    header('Location: /profile');
                    exit();
                }
            }

            $updated = $userModel->update($user_id, [
                'name' => $name,
                'email' => $email,
                'avatar' => $currentUser->avatar ?? 'avatarDefault.png'
            ]);

            if ($updated && isset($_SESSION['auth'])) {
                $_SESSION['auth']->name = $updated->name;
                $_SESSION['auth']->email = $updated->email;
            }

            flash()->put('message', 'Dados do perfil atualizados com sucesso!');
        } else {
            // Atualizar avatar
            $file = $_FILES['avatar'] ?? null;
            $hasFile = $file && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

            if (!$hasFile) {
                if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    flash()->put('error', 'Erro no upload da imagem (código ' . $file['error'] . ').');
                } else {
                    flash()->put('error', 'Selecione uma imagem para alterar seu avatar.');
                }
                header('Location: /profile');
                exit();
            }

            // Upload da imagem para o Supabase Storage
            $storageService = new SupabaseStorageService();
            $uploadedImage = $storageService->uploadImage($file, $user_id);

            if (!$uploadedImage) {
                flash()->put('error', 'Erro ao fazer upload da imagem. Tente novamente.');
                header('Location: /profile');
                exit();
            }

            // Gerar URL pública da imagem
            $publicUrl = $uploadedImage->getPublicUrl();

            // Atualizar avatar do usuário
            $updated = $userModel->update($user_id, [
                'name' => auth()->name,
                'email' => auth()->email,
                'avatar' => $publicUrl
            ]);

            if ($updated && isset($_SESSION['auth'])) {
                $_SESSION['auth']->avatar = $updated->avatar;
            }

            flash()->put('message', 'Avatar atualizado com sucesso!');
        }
    } catch (Exception $e) {
        flash()->put('error', 'Erro ao atualizar perfil: ' . $e->getMessage());
    }

    header('Location: /profile');
    exit();
}

// GET: montar dados do perfil
$user = User::get($user_id);

// Contagem de estratégias do usuário
$estrategias = Estrategia::myEstrategias($user_id);
$strategiesCount = is_array($estrategias) ? count($estrategias) : 0;

// Contagem de avaliações do usuário
$ratingsStmt = $database->query(
    'SELECT COUNT(*) AS total FROM ratings WHERE user_id = :user_id',
    null,
    ['user_id' => $user_id]
);
$ratingsRow = $ratingsStmt->fetch(PDO::FETCH_ASSOC);
$ratingsCount = (int)($ratingsRow['total'] ?? 0);

view('app', compact('user', 'strategiesCount', 'ratingsCount'), 'profile');