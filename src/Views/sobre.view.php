<?php

declare(strict_types=1);

/**
 * Sobre o projeto: pra quem chega de um link sem contexto nenhum.
 *
 * Inclui o mesmo cabeçalho das páginas internas - funciona tanto pra quem
 * ainda não tem conta (mostra Login) quanto pra quem já está logado.
 */

require __DIR__ . '/partials/_header.php';

$stats = [
    ['value' => '4,8', 'label' => 'Facilidade de navegação'],
    ['value' => '4,7', 'label' => 'Comentários e avaliações'],
    ['value' => '4,3', 'label' => 'Utilidade para aprender'],
    ['value' => '4,3', 'label' => 'Ajuda a melhorar no jogo'],
];

$features = [
    ['icon' => 'ph-target', 'title' => 'Cadastro por agente e mapa', 'text' => 'Cada estratégia leva um título, categoria (ataque, defesa, pós-plant ou retake), o agente e o mapa certos - nada de vasculhar vídeo por vídeo pra achar a informação certa.'],
    ['icon' => 'ph-image', 'title' => 'Imagem ou vídeo de apoio', 'text' => 'Uma captura de tela ou um clipe curto vale mais que um parágrafo de instrução - a estratégia fica fácil de reproduzir olhando.'],
    ['icon' => 'ph-star', 'title' => 'Avaliação pela comunidade', 'text' => 'Quem testou dá nota e comentário. Com o tempo, as melhores estratégias sobem - sem depender de um administrador decidindo o que é bom.'],
    ['icon' => 'ph-magnifying-glass', 'title' => 'Busca e filtros', 'text' => 'Filtra por mapa, agente e categoria pra achar exatamente o que precisa antes de entrar na partida.'],
];

$team = [
    'Guilherme de Carvalho Moreira',
    'Gustavo Costa Franco de Almeida',
    'João Victor Moraes',
    'Maria F. Rizzo Rodrigues da Costa',
];
?>

<main id="main-content">
  <!-- Hero -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 pt-14 pb-16 text-center">
    <p class="font-nunito text-xs uppercase tracking-[0.2em] text-red-light">Trabalho de Conclusão de Curso</p>
    <h1 class="mt-4 font-rammetto text-3xl md:text-5xl text-gray-7 leading-tight">
      Estratégia de Valorant<br class="hidden md:block"> não devia estar espalhada em dez lugares
    </h1>
    <p class="mt-6 max-w-2xl mx-auto text-gray-6 font-nunito text-base md:text-lg leading-[170%]">
      O Valorant StratHub nasceu de um problema real: em qualquer comunidade de jogadores, as boas
      táticas circulam soltas - um vídeo aqui no YouTube, um print ali no grupo, uma explicação perdida
      lá no meio de uma call. A plataforma reúne tudo isso num só lugar, organizado por agente, mapa e
      categoria, e deixa a própria comunidade avaliar o que funciona de verdade.
    </p>

    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
      <a href="/explore"
        class="flex items-center gap-2 px-6 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
        <i class="ph ph-target text-xl" aria-hidden="true"></i>
        Explorar estratégias
      </a>
      <a href="/login"
        class="flex items-center gap-2 px-6 py-3 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
        <i class="ph ph-user-plus text-xl" aria-hidden="true"></i>
        Criar conta
      </a>
    </div>
  </section>

  <!-- O que dá pra fazer -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 py-12 bg-gray-2">
    <div class="max-w-5xl mx-auto">
      <h2 class="font-rammetto text-2xl text-gray-7 text-center">O que dá pra fazer</h2>

      <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-6">
        <?php foreach ($features as $feature): ?>
          <div class="flex gap-4 p-5 rounded-[18px] bg-gray-1 border border-gray-3">
            <i class="ph <?= e($feature['icon']) ?> text-red-light text-3xl shrink-0" aria-hidden="true"></i>
            <div>
              <h3 class="font-rajdhani font-bold text-lg text-gray-7"><?= e($feature['title']) ?></h3>
              <p class="mt-1 text-gray-6 font-nunito text-sm leading-relaxed"><?= e($feature['text']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Por que existe -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 py-14">
    <div class="max-w-3xl mx-auto">
      <h2 class="font-rammetto text-2xl text-gray-7">Por que essa plataforma existe</h2>
      <p class="mt-4 text-gray-6 font-nunito leading-[170%]">
        A ideia surgiu de dentro de uma comunidade real: o servidor de Discord
        <strong class="text-gray-7">Valorant Lovers BR</strong>. Estratégias já circulavam ali o tempo
        todo - só que espalhadas entre Instagram, TikTok, Reddit, YouTube e mensagens de texto, sem
        nenhum lugar central pra guardar, buscar ou avaliar o que já tinha sido testado.
      </p>
      <p class="mt-4 text-gray-6 font-nunito leading-[170%]">
        O StratHub parte de um princípio simples: a base de jogadores é muito maior que qualquer equipe
        de administradores. Deixar que os próprios jogadores publiquem e avaliem estratégias gera muito
        mais variedade e renovação de conteúdo do que depender de alguém curando tudo manualmente -
        e é a comunidade quem decide, pelas avaliações, o que realmente funciona.
      </p>
    </div>
  </section>

  <!-- Resultados da pesquisa -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 py-14 bg-gray-2">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="font-rammetto text-2xl text-gray-7">O que quem testou achou</h2>
      <p class="mt-3 text-gray-6 font-nunito leading-[170%] max-w-xl mx-auto">
        15 jogadores do Valorant Lovers BR testaram a plataforma e responderam uma pesquisa. Notas de
        1 a 5 por critério:
      </p>

      <div class="mt-10 grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($stats as $stat): ?>
          <div class="p-5 rounded-[18px] bg-gray-1 border border-gray-3">
            <p class="font-rammetto text-3xl text-red-light"><?= e($stat['value']) ?></p>
            <p class="mt-2 text-gray-6 font-nunito text-xs leading-relaxed"><?= e($stat['label']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <p class="mt-6 text-gray-5 font-nunito text-sm">Média geral: <strong class="text-gray-7">4,5 de 5</strong></p>
    </div>
  </section>

  <!-- Ficha técnica -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 py-14">
    <div class="max-w-3xl mx-auto">
      <h2 class="font-rammetto text-2xl text-gray-7">Ficha técnica</h2>

      <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 font-nunito">
        <div>
          <dt class="text-gray-5 text-sm">Curso</dt>
          <dd class="text-gray-7 mt-1">Análise e Desenvolvimento de Sistemas</dd>
        </div>
        <div>
          <dt class="text-gray-5 text-sm">Instituição</dt>
          <dd class="text-gray-7 mt-1">Fatec Bragança Paulista "Jornalista Omair Fagundes de Oliveira"</dd>
        </div>
        <div>
          <dt class="text-gray-5 text-sm">Orientação</dt>
          <dd class="text-gray-7 mt-1">Dra. Patricia Klinkerfus de Campos e Esp. Paulo Henrique Leme Ramalho</dd>
        </div>
        <div>
          <dt class="text-gray-5 text-sm">Entrega</dt>
          <dd class="text-gray-7 mt-1">Dezembro de 2025</dd>
        </div>
        <div class="sm:col-span-2">
          <dt class="text-gray-5 text-sm">Stack</dt>
          <dd class="text-gray-7 mt-1">PHP, PostgreSQL, Tailwind CSS - arquitetura MVC, responsivo do celular ao desktop</dd>
        </div>
      </dl>

      <h3 class="mt-10 font-rajdhani font-bold text-lg text-gray-7">Quem fez</h3>
      <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-gray-6 font-nunito">
        <?php foreach ($team as $member): ?>
          <li class="flex items-center gap-2">
            <i class="ph ph-user-circle text-red-light" aria-hidden="true"></i>
            <?= e($member) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- CTA final -->
  <section class="px-4 md:px-8 lg:px-16 xl:px-24 pb-16">
    <div class="max-w-3xl mx-auto text-center p-8 rounded-[18px] bg-gray-2 border border-gray-3">
      <h2 class="font-rammetto text-xl text-gray-7">Curioso pra ver por dentro?</h2>
      <p class="mt-2 text-gray-6 font-nunito">Dá pra explorar as estratégias publicadas sem precisar criar conta.</p>
      <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
        <a href="/explore"
          class="flex items-center gap-2 px-6 py-3 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
          <i class="ph ph-target text-xl" aria-hidden="true"></i>
          Explorar estratégias
        </a>
        <a href="/guest"
          class="flex items-center gap-2 px-6 py-3 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
          <i class="ph ph-eye text-xl" aria-hidden="true"></i>
          Entrar como visitante
        </a>
      </div>
    </div>
  </section>
</main>
