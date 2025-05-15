<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="./dist/output.css" rel="stylesheet">
</head>

<body class="bg-background_primary text-text_secondary ">
    <header class="bg-background_primary">
        <nav class="mx-auto max-w-screen-lg flex justify-between px-8 py-4">
            <div >
                <img src="./assets/logo.svg" alt="logo" class="h-10 w-10">
            </div>
                <ul class="flex space-x-4">
                    <li><a href="/" class="">Explorar</a></li>
                    <li> <a href="/" class="">Estatisticas</a></li>
                </ul>
                <ul>
                    <li><a href="/login.php">Login</a></li>
                </ul>
        </nav>
    </header>

    <main class="mx-auto max-w-screen-lg px-8 py-4" >
      <!-- Estrategias -->
           <?php require "views/{$view}.view.php"; ?>

</main>

</body>

</html>