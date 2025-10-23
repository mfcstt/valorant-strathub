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
            // Atualizar nome, email e elo
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $elo = strtolower(trim($_POST['elo'] ?? ''));

            $allowedElos = ['ferro','bronze','prata','ouro','platina','diamante','ascendente','imortal','radiante'];

            // Validação simples
            $errors = [];
            if ($name === '' || strlen($name) < 2) {
                $errors[] = 'Informe um nome válido (mínimo 2 caracteres).';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Informe um email válido.';
            }
            if ($elo === '' || !in_array($elo, $allowedElos, true)) {
                $errors[] = 'Selecione um elo válido.';
            }

            if (!empty($errors)) {
                flash()->put('error', implode(' ', $errors));
                flash()->put('formData', ['name' => $name, 'email' => $email, 'elo' => $elo]);
                header('Location: /profile');
                exit();
            }

            // Preservar avatar atual e validar e-mail duplicado
            $currentUser = User::get($user_id);

            if ($email !== ($currentUser->email ?? '')) {
                $existing = User::getByEmail($email);
                if ($existing && (int)$existing->id !== (int)$user_id) {
                    flash()->put('error', 'Este e-mail já está em uso.');
                    flash()->put('formData', ['name' => $name, 'email' => $email, 'elo' => $elo]);
                    header('Location: /profile');
                    exit();
                }
            }

            $updated = $userModel->update($user_id, [
                'name' => $name,
                'email' => $email,
                'avatar' => $currentUser->avatar ?? 'avatarDefault.png'
            ]);

            // Atualizar elo
            $updatedEloUser = $userModel->updateElo($user_id, $elo);

            if ($updated && isset($_SESSION['auth'])) {
                $_SESSION['auth']->name = $updated->name;
                $_SESSION['auth']->email = $updated->email;
            }
            if ($updatedEloUser && isset($_SESSION['auth'])) {
                $_SESSION['auth']->elo = $updatedEloUser->elo;
            }

            flash()->put('message', 'Dados do perfil atualizados com sucesso!');
        } else if ($action === 'change_password') {
            // Alterar senha
            $current = $_POST['senha_atual'] ?? '';
            $new = $_POST['nova_senha'] ?? '';
            $confirm = $_POST['confirmar_senha'] ?? '';

            $errors = [];
            if ($current === '') $errors[] = 'Informe a senha atual.';
            if ($new === '' || strlen($new) < 8 || strlen($new) > 30) $errors[] = 'A nova senha deve ter entre 8 e 30 caracteres.';
            if ($new !== $confirm) $errors[] = 'A confirmação da senha não confere.';

            $dbUser = User::get($user_id);
            if (!$dbUser || !password_verify($current, $dbUser->password)) {
                $errors[] = 'Senha atual incorreta.';
            }

            if (!empty($errors)) {
                flash()->put('error', implode(' ', $errors));
                header('Location: /profile');
                exit();
            }

            $userModel->updatePassword($user_id, $new);
            flash()->put('message', 'Senha alterada com sucesso!');
        } else if ($action === 'delete_account') {
            // Apagar conta (confirmado com senha atual)
            $current = $_POST['senha_atual'] ?? '';
            $dbUser = User::get($user_id);

            if ($current === '' || !$dbUser || !password_verify($current, $dbUser->password)) {
                flash()->put('error', 'Senha atual incorreta para apagar a conta.');
                header('Location: /profile');
                exit();
            }

            // Deletar usuário e limpar sessão
            $userModel->delete($user_id);
            unset($_SESSION['auth']);
            unset($_SESSION['guest']);

            flash()->put('message', 'Sua conta foi apagada com sucesso.');
            header('Location: /login');
            exit();
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