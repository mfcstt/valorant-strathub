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

    $validation = Validation::validate([
        'nome' => ['required'],
        'email' => ['required', 'email', 'unique:users'],
        'senha' => ['required', 'min:8', 'max:30', 'strong']
    ], $_POST);

    if ($validation->notPassed('register')) {
        flash()->put('formData', $_POST);
        header('Location: /login');
        exit();
    }

    $database = new Database(config('database')['database']);

    $database->query(
        "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)",
        null,
        [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]
    );

    flash()->put('message', 'Cadastro realizado com sucesso!');
    header('Location: /login');
    exit();
}

// Se não é POST, renderiza a view de login (que tem o formulário de registro)
view("login");
