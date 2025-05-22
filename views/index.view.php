<div class="flex justify-between items-center">
    <h1 class="font-bold text-text_primary text-2xl">Explorar</h1>

    <form class="flex space-x-2 mt-6">
        <input type="text"
            name="pesquisar"
            class="border-stone-800 border-2 rounded-md bg-stone-900 text-sm focus:outline-none px-2 py-1"
            placeholder="Pesquisar" />
        <button type="submit">🔍</button>
        <button class="bg-primary text-text_primary mt-6 px-7 py-6">+ Novo</button>
    </form>

</div>

<div>


    <?php foreach ($estrategias as $estrategia): ?>

        <div class="w-1/3 p-2 border-stone-800 border-2">
            <div class="flex">
                <div class="w-1/3">Imagem</div>
                <div class="space-y-1">
                    <a href="/estrategia?id=<?= $estrategia->id ?>"><?= $estrategia->titulo ?></a>
                    <div class="text-xs italic"><?= $estrategia->fk_usuario_id ?></div>
                    <div class="text-xs italic"><?= $estrategia->fk_mapas_id ?></div>
                </div>
            </div>

            <div class="text-sm-2">
                <?= $estrategia->fk_categoria_id ?></
            </div>
        </div>
    <?php endforeach; ?>
    </section>
</div>