<main>
    <?php if($estrategia): ?>
        <p>Id da estratégia: <?= htmlspecialchars($estrategia->id) ?></p>
        <p>Título da estratégia: <?= htmlspecialchars($estrategia->titulo) ?></p>
        <p>Descrição da estratégia: <?= htmlspecialchars($estrategia->descricao) ?></p>
        <p>ID do usuário: <?= htmlspecialchars($estrategia->fk_usuario_id) ?></p>
        <p>ID do Mapa: <?= htmlspecialchars($estrategia->fk_mapas_id) ?></p>
        <p>ID da categoria: <?= htmlspecialchars($estrategia->fk_categoria_id) ?></p>
    <?php else: ?>
        <p>Estratégia não encontrada.</p>
    <?php endif; ?>
</main>  

