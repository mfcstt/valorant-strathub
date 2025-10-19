<?php

// Redireciona se já estiver logado
if (auth()) {
    header('Location: /explore');
    exit();
}

$message = $_REQUEST['message'] ?? '';

// Processa o POST do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['senha'] ?? '';

    // Validação dos campos
    $validation = Validation::validate([
        'email' => ['required', 'email'],
        'senha' => ['required']
    ], $_POST);

    if ($validation->notPassed('login')) {
        flash()->put('formData', $_POST);
        header('Location: /login');
        exit();
    }

    // Criar instância do Database
    $database = new Database(config('database')['database']);

    // Buscar usuário pelo email
    $results = $database->query(
        "SELECT * FROM users WHERE email = :email",
        User::class,
        compact('email')
    );

    // Normaliza resultado: sempre array
    $user = is_array($results) ? ($results[0] ?? null) : $results;

    if ($user) {
        // Verifica se a senha bate
        if (!password_verify($password, $user->password)) {
            flash()->put('formData', $_POST);
            $validation->addValidationMessage('senha', 'Email ou senha incorretos!');
            header('Location: /login');
            exit();
        }

        // Autenticação bem-sucedida
        // Criar objeto User limpo para a sessão (sem PDO)
        $userSession = new stdClass();
        $userSession->id = $user->id;
        $userSession->name = $user->name;
        $userSession->email = $user->email;
        $userSession->avatar = $user->avatar;
        $userSession->created_at = $user->created_at;
        $userSession->updated_at = $user->updated_at;
        
        $_SESSION['auth'] = $userSession;
        flash()->put('message', 'Seja bem-vindo(a), ' . "<span class='text-red-light capitalize'>{$user->name}</span>");
        header('Location: /explore');
        exit();
    } else {
        flash()->put('formData', $_POST);
        $validation->addValidationMessage('senha', 'Email ou senha incorretos!');
        header('Location: /login');
        exit();
    }
}

// Renderiza a view do login
view("login");
