<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['nome'];
  $email = $_POST['email'];
  $password = $_POST['senha'];

  $validation = Validation::validate([
    'nome' => ['required'],
    'email' => ['required', 'email', 'unique:users'],
    'senha' => ['required', 'min:8', 'max:30', 'strong']
  ], $_POST);
  
  if ($validation->notPassed('register')) {
    // Armazenar valores do form na SESSION.
    flash()->put('formData', $_POST);

    header('location: /login');
    exit();
  }

  $database->query(
    query: "insert into users ( name, email, password ) values ( :name, :email, :password )",
    params: [
      'name' => $name,
      'email' => $email,
      'password' => password_hash($password, PASSWORD_BCRYPT),
    ]
  );

  flash()->put('message', 'Cadastro realizado com sucesso!');

  header('location: /login');
  exit();
}

header('location: /login');
exit();
