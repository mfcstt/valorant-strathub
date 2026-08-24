<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Auth;
use App\Support\Validation;

if (Auth::check()) {
    redirect('/explore');
}

if (!is_post()) {
    // O formulário de cadastro vive na mesma tela do login.
    redirect('/login');
}

$name = trim((string) ($_POST['nome'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['senha'] ?? '');
$elo = strtolower(trim((string) ($_POST['elo'] ?? '')));

$validation = Validation::validate([
    'nome' => ['required', 'min:2', 'max:60'],
    'email' => ['required', 'email', 'max:255', 'unique:users,email'],
    'senha' => ['required', 'min:8', 'max:72', 'strong'],
    'elo' => ['required', 'in:' . implode(',', User::ELOS)],
], $_POST);

if ($validation->fails()) {
    $validation->flashErrors('register');
    flash()->put('formData', ['nome' => $name, 'email' => $email, 'elo' => $elo]);
    redirect('/login');
}

$user = User::create($name, $email, $password, $elo);

if ($user === null) {
    flash()->put('error', 'Não foi possível concluir o cadastro. Tente novamente.');
    redirect('/login');
}

// Já autentica: pedir para a pessoa digitar as mesmas credenciais na tela
// seguinte não protege nada e só adiciona um passo.
Auth::login($user);

flash()->put('message', 'Cadastro concluído. Boa sorte nas ranqueadas!');
redirect('/explore');
