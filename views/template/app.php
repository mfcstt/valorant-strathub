<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./assets/logo.svg">
    <title>Valorant Strathub</title>
    <link href="./dist/output.css" rel="stylesheet">
</head>

<body class="bg-background_primary text-text_secondary ">
    <header class="bg-background_primary">
        <nav class="mx-auto max-w-screen-lg flex justify-between px-8 py-4">
            <div >
                <a href="#"><img src="./assets/logo.svg" alt="logo" class="h-10 w-10"></a>
            </div>
                <ul class="flex space-x-4">
                    <li><a href="#" class="">Explorar</a></li>
                    <li> <a href="controllers/MinhasEstrategias.controller.php" class="">Minhas estratégias</a></li>
                </ul>
                <ul>
                    <li><a href="controllers/login.controller.php">Login</a></li>
                </ul>
        </nav>
    </header>

    <main class="mx-auto max-w-screen-lg px-8 py-4" >
      <!-- Estrategias -->
        <?php require "views/{$view}.view.php"; ?>

</main>

</body>

</html>