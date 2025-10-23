<?php

// Redireciona se já estiver logado
if (auth()) {
    header('Location: /explore');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['senha'] ?? '';
    $elo = strtolower(trim($_POST['elo'] ?? ''));

    $allowedElos = ['ferro','bronze','prata','ouro','platina','diamante','ascendente','imortal','radiante'];

    $validation = Validation::validate([
        'nome' => ['required'],
        'email' => ['required', 'email', 'unique:users'],
        'senha' => ['required', 'min:8', 'max:30', 'strong'],
        'elo' => ['required']
    ], $_POST);

    // Validação adicional para o campo elo
    if (!in_array($elo, $allowedElos, true)) {
        $validation->addValidationMessage('elo', 'Selecione um elo válido.');
    }

    if ($validation->notPassed('register')) {
        flash()->put('formData', $_POST);
        header('Location: /login');
        exit();
    }

    $database = new Database(config('database')['database']);

    $database->query(
        "INSERT INTO users (name, email, password, elo) VALUES (:name, :email, :password, :elo)",
        null,
        [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'elo' => $elo,
        ]
    );

    flash()->put('message', 'Cadastro realizado com sucesso!');
    header('Location: /login');
    exit();
}

// Se não é POST, renderiza a view de login (que tem o formulário de registro)
view("login");
