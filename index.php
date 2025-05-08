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
        <div class="flex justify-between items-center">
            <h1 class="font-bold text-text_primary text-2xl">Explorar</h1>
            <form class="flex space-x-2 mt-6">
                <input type="text" 
                class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
                placeholder="Pesquisar"
                />
                <button type="submit">🔍</button>
                <button class="bg-primary text-text_primary mt-6 px-7 py-6">+ Novo</button>
            </form>
        </div>

    <div>

        <section class="space-y-4">
            <div class="bg-blue-900">
                <div>Imagem</div>
                <div>
                    <div>titulo</div>
                    <div>usuario</div>
                    <div>avaliação</div>
                </div>
                <div>
                   categoria 
                </div>
            </div>
        </section>

    </div>
</main>

</body>

</html>