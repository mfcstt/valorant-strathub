<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="mt-6 grid grid-cols-2 gap-2">
    <div class="border border-stone-700 rounded">
    <h1 class="border-b border-stone-700 text-stone-400 font-bold px-4 py-2">Login</h1>    
    <form class="p-4 space-y-4">
            <div class="flex flex-col px-4 py-2">
            <label class="text-stone-400 mb-1">E-mail</label>
            <input type="email"
            name="email" required
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Digite seu e-mail"/>
            </div>
            <div class="flex flex-col px-4 py-2">
            <label class="text-stone-400 mb-1">Senha</label>
            <input type="password"
            name="senha" required
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Digite sua senha"/>
            </div>
            <button type="submit" class="border-stone-800 bg-stone-900 text-stone-400 px-4 py-1 rounded-md border-2 hover:bg-stone-800">Logar</button>
        </form>
    </div>

    <div>
        <h1 class="border-b border-stone-700 text-stone-400 font-bold px-4 py-2">Registro</h1>    
        <form class="p-4 space-y-4">
            <div class="flex flex-col px-4 py-2">
            <label class="text-stone-400 mb-1">Nome</label>
            <input type="text"
            name="nome" required
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Digite seu nome"/>
            </div>
            <div class="flex flex-col px-4 py-2">
            <label class="text-stone-400 mb-1">E-mail</label>
            <input type="email"
            name="email" required
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Digite seu e-mail"/>
            </div>
            <div class="flex flex-col px-4 py-2">
            <label class="text-stone-400 mb-1">Senha</label>
            <input type="password"
            name="senha" required
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Digite sua senha"/>
            </div>
            <button type="reset" class="border-stone-800 bg-stone-900 text-stone-400 px-4 py-1 rounded-md border-2 hover:bg-stone-800">Cancelar</button>
            <button type="submit" class="border-stone-800 bg-stone-900 text-stone-400 px-4 py-1 rounded-md border-2 hover:bg-stone-800">Registrar</button>
        </form>
    </div>
</div>
</body>
</html>