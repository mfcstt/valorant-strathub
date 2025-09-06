<?php

unset($_SESSION["flash_validations_login"]);

if (!auth()) {
  abort(403, 'Você precisa estar logado para acessar essa página.');
}

$message = $_REQUEST['message'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == 'POST' && !isset($_POST["pesquisar"])) {

  // DELETAR ARQUIVO AVATAR ANTERIOR, CASO EXISTA.
  if (auth()->avatar) {
    unlink(__DIR__ . '/../../public/assets/images/avatares/' . auth()->avatar);
  }

  // UPLOAD DO NOVO AVATAR
  $fileAvatar = $_FILES['avatar'];
  $user_id = auth()->id;

  $randomName = md5(rand());
  $extension = pathinfo($fileAvatar['name'], PATHINFO_EXTENSION);
  $newName =  "$randomName.$extension";
  move_uploaded_file($fileAvatar['tmp_name'], __DIR__ . '/../../public/assets/' . "images/avatares/$newName");

  $database->query(
    query: "
      SET FOREIGN_KEY_CHECKS= 0;
      UPDATE ratings SET user_avatar = :avatar WHERE user_id = :user_id;
      UPDATE users SET avatar = :avatar WHERE id = :user_id;
      SET FOREIGN_KEY_CHECKS= 1;
    ",
    params: [
      'avatar' => $newName,
      'user_id' => $user_id
    ]
  );

  $user = $database->query(
    query: "select * from users where id = :user_id",
    class: User::class,
    params: compact('user_id')
  )->fetch();

  $_SESSION['auth'] = $user;

  flash()->put('message', 'Avatar atualizado com sucesso!');

  header('Location: explore');
  exit();
}

// Armazenar valores do form na SESSION.
flash()->put('formData', $_POST);

$search = $_REQUEST['pesquisar'] ?? '';

$estrategias = Estrategia::all($search);

view('app', compact('message', 'estrategias', 'search'), 'explore');
