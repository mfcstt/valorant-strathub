<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Auth;
use App\Support\Validation;

if (Auth::check()) {
    redirect('/explore');
}

if (is_post()) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['senha'] ?? '');

    $validation = Validation::validate([
        'email' => ['required', 'email'],
        'senha' => ['required'],
    ], $_POST);

    if ($validation->fails()) {
        $validation->flashErrors('login');
        flash()->put('formData', ['email' => $email]);
        redirect('/login');
    }

    $user = User::findByEmail($email);

    // Uma única mensagem para "e-mail não existe" e "senha errada". Distinguir
    // os dois casos transforma o formulário de login num verificador de quais
    // e-mails têm conta no site.
    if ($user === null || !$user->verifyPassword($password)) {
        $validation->addError('senha', 'E-mail ou senha incorretos.');
        $validation->flashErrors('login');
        flash()->put('formData', ['email' => $email]);
        flash()->put('error', 'E-mail ou senha incorretos.');
        redirect('/login');
    }

    Auth::login($user);

    flash()->put('message', 'Bem-vindo(a) de volta, ' . e($user->name) . '!');
    redirect('/explore');
}

view('login');
