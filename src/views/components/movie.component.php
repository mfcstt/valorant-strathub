<?php
// Mensagens de validações de cada formulário
$validationsMessages = flash()->get("validations") ?? null;

$formData = flash()->get("formData")["comentario"] ?? '';
?>

<div class="modalBlur w-full h-full">
  <!-- Infos do Filme -->
  <section class="relative min-h-[618px] flex items-start justify-center px-4 md:px-0">
    <article class="flex flex-col md:flex-row gap-6 md:gap-12 w-full max-w-[1366px]">
      <div class="w-full lg:hidden mb-4">
        <div class="flex items-center justify-between">
          <a href="/explore"
            class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-caret-left text-xl"></i>
            Voltar
          </a>

          <?php if (auth() && $author && auth()->id == $author->id): ?>
            <form id="delete-form-<?= htmlspecialchars($movie->id) ?>" method="post" action="/strategy-delete"
              class="inline">
              <input type="hidden" name="estrategia_id" value="<?= htmlspecialchars($movie->id) ?>">
              <button type="submit" onclick="return confirm('Excluir esta estratégia? Esta ação é irreversível.')"
                class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
                <i class="ph ph-trash text-xl"></i>
                Excluir
              </button>
            </form>
          <?php endif; ?>
        </div>

        <h1 class="mt-4 text-[2rem] text-gray-7 font-bold font-rajdhani whitespace-normal break-all">
          <?= $movie->title ?>
        </h1>
      </div>
      <div class="w-full md:w-96">
        <div>
          <?php if ($movie->cover_image_url): ?>
            <img id="strategyCoverImage" src="<?= $movie->cover_image_url ?>" alt="Capa da estratégia"
              class="w-full h-56 sm:h-72 md:h-96 object-cover rounded-[18px] cursor-zoom-in">
          <?php elseif ($movie->video_url): ?>
            <video controls class="w-full h-56 sm:h-72 md:h-96 object-cover rounded-[18px]" preload="metadata">
              <source src="<?= $movie->video_url ?>" type="video/mp4">
              <source src="<?= $movie->video_url ?>" type="video/webm">
              <source src="<?= $movie->video_url ?>" type="video/ogg">
              Seu navegador não suporta a reprodução de vídeos.
            </video>
          <?php else: ?>
            <div class="w-full h-56 sm:h-72 md:h-96 bg-gray-800 flex items-center justify-center rounded-[18px]">
              <span class="text-gray-500 text-xs">Sem imagem</span>
            </div>
          <?php endif; ?>
        </div>

        <?php if (auth()): ?>
          <div class="mt-3 flex items-center gap-2 w-full">
            <form id="fav-detail-form-<?= htmlspecialchars($movie->id) ?>" method="post" action="/favorite-toggle">
              <input type="hidden" name="estrategia_id" value="<?= htmlspecialchars($movie->id) ?>">
              <input type="hidden" name="redirect"
                value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/strategy?id=' . $movie->id) ?>">
              <button type="submit"
                class="flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
                <?php if (!empty($is_favorite)): ?>
                  <i class="ph-fill ph-heart text-xl text-red-light"></i>
                  <span>Desfavoritar</span>
                <?php else: ?>
                  <i class="ph ph-heart text-xl"></i>
                  <span>Favoritar</span>
                <?php endif; ?>
              </button>
            </form>

            <button type="button"
              class="share-btn ml-auto flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all"
              data-id="<?= htmlspecialchars($movie->id) ?>" data-title="<?= htmlspecialchars($movie->title) ?>">
              <i class="ph ph-share-network text-xl"></i>
              <span>Compartilhar</span>
            </button>
          </div>
        <?php else: ?>
          <div class="mt-3 flex items-center gap-2 w-full">
            <a href="/login"
              class="flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all">
              <i class="ph ph-heart text-xl"></i>
              <span>Faça login para favoritar</span>
            </a>
            <button type="button"
              class="share-btn ml-auto flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all"
              data-id="<?= htmlspecialchars($movie->id) ?>" data-title="<?= htmlspecialchars($movie->title) ?>">
              <i class="ph ph-share-network text-xl"></i>
              <span>Compartilhar</span>
            </button>
          </div>
        <?php endif; ?>


      </div>

      <div class="w-full md:w-[644px]">
        <div class="hidden lg:flex items-center justify-between">
          <a href="/explore"
            class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-caret-left text-xl"></i>
            Voltar
          </a>

          <?php if (auth() && $author && auth()->id == $author->id): ?>
            <form id="delete-form-lg-<?= htmlspecialchars($movie->id) ?>" method="post" action="/strategy-delete"
              class="inline">
              <input type="hidden" name="estrategia_id" value="<?= htmlspecialchars($movie->id) ?>">
              <button type="submit" onclick="return confirm('Excluir esta estratégia? Esta ação é irreversível.')"
                class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
                <i class="ph ph-trash text-xl"></i>
                Excluir
              </button>
            </form>
          <?php endif; ?>
        </div>

        <h1 class="hidden lg:block mt-5 text-[2rem] text-gray-7 font-bold font-rajdhani whitespace-normal break-all">
          <?= $movie->title ?>
        </h1>

        <div class="text-gray-6 font-nunito leading-[160%] mt-4">
          <p><span class="font-bold">Categoria:</span> <?= $movie->category ?></p>
          <p><span class="font-bold">Agente:</span> <?= $movie->agent_name ?></p>
          <?php if ($movie->map_name): ?>
            <p><span class="font-bold">Mapa:</span> <?= $movie->map_name ?></p>
          <?php endif; ?>
        </div>

        <div class="flex items-center gap-3 mt-3">
          <span class="text-gray-6 font-nunito"><span class="font-bold">Postado por:</span></span>
          <?php if (isset($author) && $author): ?>
            <div class="relative w-8 h-8">
              <img
                src="<?= ($author->avatar && $author->avatar !== 'avatarDefault.png') ? $author->avatar : '/assets/images/avatares/avatarDefault.png' ?>"
                alt="Avatar do autor" class="w-8 h-8 rounded-md border border-[#7435DB]">
              <?php if (!empty($author->elo)): ?>
                <img src="/assets/images/elos/<?= htmlspecialchars($author->elo) ?>.png" alt="Elo do autor"
                  class="absolute -bottom-1 -right-2 w-5 h-5 rounded-full border border-gray-3 bg-gray-2">
              <?php endif; ?>
            </div>
            <span class="text-gray-7 font-rajdhani font-bold capitalize"><?= $author->name ?></span>
          <?php else: ?>
            <span class="text-gray-6 font-nunito">Usuário desconhecido</span>
          <?php endif; ?>
        </div>

        <!-- Seção de Vídeo -->
        <?php if ($movie->video_url && $movie->cover_image_url): ?>
          <div class="mt-6">
            <h3 class="text-lg font-bold text-gray-7 font-rajdhani mb-3">Vídeo da Estratégia</h3>
            <div class="relative bg-gray-800 rounded-lg overflow-hidden">
              <video controls class="w-full h-auto max-h-80 object-contain" preload="metadata">
                <source src="<?= $movie->video_url ?>" type="video/mp4">
                <source src="<?= $movie->video_url ?>" type="video/webm">
                <source src="<?= $movie->video_url ?>" type="video/ogg">
                Seu navegador não suporta a reprodução de vídeos.
              </video>
              <?php if ($movie->video_duration): ?>
                <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                  <?= $movie->video_duration ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 mt-4">
          <ul class="flex items-center text-red-light text-2xl">
            <?php
            $avg = (float) $movie->rating_average;
            $filledCount = (int) floor($avg);
            $hasHalf = ($avg - $filledCount) >= 0.5;
            $emptyCount = 5 - $filledCount - ($hasHalf ? 1 : 0);

            echo str_repeat('<li><i class="ph-fill ph-star p-1"></i></li>', $filledCount);
            if ($hasHalf) {
              echo '<li><i class="ph ph-star-half p-1"></i></li>';
            }
            echo str_repeat('<li><i class="ph ph-star p-1"></i></li>', $emptyCount);
            ?>
          </ul>

          <p class="text-gray-7 text-2xl font-bold font-rajdhani">
            <?= number_format($movie->rating_average, 1, ',', '.') ?> <span
              class="text-gray-6 text-base leading-[160%] font-normal font-nunito">/ 5</span>
            <span class="text-gray-6 text-base leading-[160%] font-normal font-nunito">(<?= $movie->ratings_count ?>
              <?= $movie->ratings_count == 1 ? "avaliação" : "avaliações" ?>)</span>
          </p>
        </div>

        <p class="mt-8 md:mt-20 text-gray-6 leading-[160%] font-nunito break-words">
          <?= $movie->description ?>
        </p>
      </div>
    </article>
  </section>

  <!-- Avaliações -->
  <section class="px-4 md:px-[9.25rem] pt-12 md:pt-20 pb-20 md:pb-28">
    <div class="w-full flex justify-between items_center mb-10">
      <h2 class="font-rajdhani text-2xl font-bold text-[#E5E2E9] self-end">Avaliações</h2>

      <?php if (auth()): ?>
        <button type="button"
          class="showModal flex items-center gap-2 bg-red-base px-5 py-3 rounded-md text-white font-nunito leading-6 outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-star text-xl"></i>
          Avaliar estratégia
        </button>
      <?php else: ?>
        <a href="/login"
          class="flex items-center gap-2 bg-red-base px-5 py-3 rounded-md text-white font-nunito leading-6 outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-star text-xl"></i>
          Fazer login para avaliar
        </a>
      <?php endif; ?>
    </div>

    <!-- CARDS AVALIAÇÕES -->
    <div class="flex flex-col gap-3">
      <?php foreach ($ratings as $rating): ?>
        <article class="relative p-6 md:p-8 rounded-xl bg-gray-2">
          <!-- Nota (canto superior direito) -->
          <div
            class="absolute top-4 right-4 md:top-6 md:right-6 flex items-center gap-1.5 px-2.5 py-1 text-base md:text-xl text-gray-7 font-bold font-rajdhani bg-gray-3 rounded-md">
            <p><?= $rating->rating ?> <span class="text-xs font-normal">/ 5</span></p>
            <i class="ph-fill ph-star text-base text-red-light"></i>
          </div>

          <!-- Header: avatar + nome + estatísticas (lado esquerdo) -->
          <div class="flex items-center gap-4">
            <div class="relative w-12 h-12 shrink-0">
              <img
                src="<?= ($rating->user_avatar && $rating->user_avatar !== 'avatarDefault.png') ? $rating->user_avatar : '/assets/images/avatares/avatarDefault.png' ?>"
                alt="Avatar perfil" class="w-12 h-12 object-cover rounded-md border border-[#7435DB]">
              <?php if (!empty($rating->user_elo)): ?>
                <img src="/assets/images/elos/<?= htmlspecialchars($rating->user_elo) ?>.png" alt="Elo do usuário"
                  class="absolute -bottom-2 -right-3 w-7 h-7 rounded-full border border-gray-3 bg-gray-2">
              <?php endif; ?>
            </div>

            <div class="flex flex-col">
              <h3 class="text-gray-7 font-bold font-rajdhani capitalize">
                <?= $rating->user_name ?>
                <?php if (auth() && $rating->user_id == auth()->id): ?>
                  <span
                    class="px-1.5 ml-2 bg-red-base rounded-full text-xs font-bold font-nunito leading-[160%]">você</span>
                <?php endif; ?>
              </h3>
              <p class="text-gray-5 text-sm font-nunito leading-[160%] mt-1">
                <?= $rating->rated_movies ?>
                <?= $rating->rated_movies == 1 ? "estratégia avaliada" : "estratégias avaliadas" ?>
              </p>
            </div>
          </div>

          <!-- Comentário: destaque e largura total abaixo -->
          <p class="mt-3 md:mt-4 text-gray-7 font-nunito leading-[170%] break-all">
            <?= $rating->comment ?>
          </p>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($total_pages) && $total_pages > 1): ?>
      <?php
      $prevPage = max(1, ($page ?? 1) - 1);
      $nextPage = min($total_pages, ($page ?? 1) + 1);
      $base = '/strategy?id=' . urlencode($movie->id);
      $prevUrl = $base . '&page=' . $prevPage;
      $nextUrl = $base . '&page=' . $nextPage;
      ?>
      <nav class="mt-6 flex items-center justify-center gap-3">
        <a href="<?= $prevUrl ?>"
          class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">Anterior</a>
        <span class="px-3 py-2 text-gray-6">Página <?= $page ?? 1 ?> de <?= $total_pages ?></span>
        <a href="<?= $nextUrl ?>"
          class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">Próxima</a>
      </nav>
    <?php endif; ?>

    <!-- SE NÃO EXISTIR AVALIAÇÕES... -->
    <?php if (!$ratings): ?>
      <div class="flex flex-col gap-5 items-center text-center font-nunito">
        <i class="ph ph-popcorn text-gray-4 text-[44px]"></i>

        <p class="w-80 text-gray-6 leading-[160%]">
          Nenhuma avaliação registrada. </br>
          Que tal enviar o primeiro comentário?
        </p>

        <?php if (auth()): ?>
          <button type="button"
            class="showModal flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-star text-xl"></i>
            Avaliar estratégia
          </button>
        <?php else: ?>
          <a href="/login"
            class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-star text-xl"></i>
            Fazer login para avaliar
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<div id="imageLightbox" class="fixed inset-0 z-[20] hidden">
  <div id="imageLightboxBackdrop" class="absolute inset-0 w-full h-full bg-[#000000e6]"></div>
  <div class="relative flex items-center justify-center w-full h-full">
    <img id="imageLightboxImg" src="" alt="Imagem da estratégia"
      class="max-w-[95vw] max-h-[95vh] object-contain rounded-md border border-gray-3 bg-gray-1 shadow-2xl">
    <button type="button" id="closeImageLightbox"
      class="absolute top-6 right-6 h-9 px-3 rounded-md text-gray-5 bg-gray-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
      <i class="ph ph-x text-xl"></i>
    </button>
  </div>
</div>

<!-- MODAL AVALIAR -->
<div>
  <dialog class="modal fixed z-[50] inset-0 w-[90vw] max-w-[600px] p-6 md:p-10 bg-gray-1 border border-gray-3 rounded-[18px] <?php if ($validationsMessages)
    echo 'open' ?>">
      <button
        class="closeModal absolute top-5 right-5 h-8 p-1.5 rounded-md text-gray-5 bg-gray-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        <i class="ph ph-x text-xl leading-[0]"></i>
      </button>

      <h2 class="text-gray-7 text-xl font-bold font-rajdhani">Avaliar estratégia</h2>

      <form class="flex flex-col gap-6" method="post" action="/rating-create">
        <!-- Enviar o estrategia_id para o POST -->
        <input type="hidden" name="estrategia_id" value="<?= $movie->id ?>">

      <div class="flex flex-col gap-6 mt-6 md:mt-8">
        <?php if ($movie->cover_image_url): ?>
          <img src="<?= $movie->cover_image_url ?>" alt="Capa da estratégia" class="mx-auto w-[75%] max-w-[420px] h-28 sm:h-36 object-cover rounded-md">
        <?php else: ?>
          <div class="mx-auto w-[75%] max-w-[420px] h-28 sm:h-36 bg-gray-800 flex items-center justify-center rounded-md">
            <span class="text-gray-500 text-xs">Sem imagem</span>
          </div>
        <?php endif; ?>

        <div class="w-full">
          <h3 class="text-2xl text-gray-7 font-bold font-rajdhani"><?= $movie->title ?></h3>

          <div class="text-gray-6 text-sm font-nunito leading-[160%] mt-4">
            <p><span class="font-bold">Categoria:</span> <?= $movie->category ?></p>
            <p><span class="font-bold">Agente:</span> <?= $movie->agent_name ?></p>
          </div>

          <div class="flex flex-col gap-1.5 mt-6">
            <p class="text-gray-6 text-sm font-nunito leading-[160%]">
              Sua avaliação:
            </p>

            <div class="flex items-center boxRating w-min">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <label class="star-icon <?= $i === 1 ? 'firstStar starActive' : '' ?>">
                  <input type="radio" name="avaliacao" class="hidden" value="<?= $i ?>">
                </label>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>

      <div>
        <textarea name="comentario" placeholder="Comentário" maxlength="300"
          class="resize-none w-full h-28 bg-gray-1 border border-gray-3 rounded-md px-4 py-3 text-gray-7 font-nunito leading-6 placeholder:text-gray-5 outline-none hover:outline-purple-base focus:outline-red-base"><?= htmlspecialchars($formData) ?></textarea>

        <?php if ($validationsMessages): ?>
          <ul class="ml-1 flex flex-wrap gap-x-3">
            <?php foreach ($validationsMessages as $messages): ?>
              <?php foreach ($messages as $message): ?>
                <li class="flex gap-1.5 items-center text-start text-error-light">
                  <i class="ph ph-warning text-base"></i>
                  <span class="text-xs mt-[2px]"><?= $message ?></span>
                </li>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <button type="submit"
        class="px-5 py-3 self-end rounded-md text-white bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        Publicar
      </button>
    </form>
  </dialog>

  <div class="overlay fixed inset-0 w-full h-full z-[40] bg-[#000000e6] hidden"></div>
</div>

<script>
  (function () {
    const buttons = document.querySelectorAll('.share-btn');
    buttons.forEach((btn) => {
      btn.addEventListener('click', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        const id = this.dataset.id;
        const title = this.dataset.title || 'Estratégia Valorant';
        const url = `${window.location.origin}/strategy?id=${encodeURIComponent(id)}`;

        const showShareToast = (text) => {
          const pad = 32;
          const container = document.createElement('div');
          container.id = 'shareMessage';
          container.className = 'fixed bottom-8 right-[-400px] z-10 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover';
          container.innerHTML = `
            <div class="flex items-center gap-2 px-8 pt-4 pb-3">
              <i class="ph ph-share-network text-red-base text-2xl"></i>
              <span class="text-lg">${text}</span>
            </div>
            <div class="w-full h-0.5 bg-gray-3 rounded-xl">
              <div class="progress-share h-full bg-red-light"></div>
            </div>
          `;
          document.body.appendChild(container);
          container.style.right = `-${container.offsetWidth + pad}px`;
          setTimeout(() => { container.style.right = `${pad}px`; }, 200);
          const progress = container.querySelector('.progress-share');
          let containerWidth = container.offsetWidth - 8;
          let count = 4;
          let x = (containerWidth / 100) * count;
          const loading = setInterval(() => {
            if (count >= 100) { clearInterval(loading); }
            else { x += containerWidth / 100; count++; if (progress) progress.style.width = `${Math.trunc(x)}px`; }
          }, 50);
          setTimeout(() => {
            container.style.right = `-${container.offsetWidth + pad}px`;
            setTimeout(() => { container.remove(); }, 600);
          }, 4700);
        };

        const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const copyWithFallback = () => {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url)
              .then(() => { showShareToast('Link copiado para a área de transferência!'); })
              .catch(() => {
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed';
                ta.style.top = '-9999px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try { document.execCommand('copy'); showShareToast('Link copiado!'); } catch (e) { showShareToast('Não foi possível copiar o link'); }
                document.body.removeChild(ta);
              });
          } else {
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.top = '-9999px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); showShareToast('Link copiado!'); } catch (e) { showShareToast('Não foi possível copiar o link'); }
            document.body.removeChild(ta);
          }
        };

        if (isMobile && navigator.share) {
          navigator.share({ title, url })
            .then(() => { showShareToast('Compartilhado!'); })
            .catch(() => { });
        } else {
          copyWithFallback();
        }
      });
    });
  })();
</script>

<script>
  (function () {
    const img = document.getElementById('strategyCoverImage');
    const box = document.getElementById('imageLightbox');
    const full = document.getElementById('imageLightboxImg');
    const closeBtn = document.getElementById('closeImageLightbox');
    const backdrop = document.getElementById('imageLightboxBackdrop');
    if (img && box && full && closeBtn && backdrop) {
      const open = () => {
        full.src = img.src;
        box.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      };
      const close = () => {
        box.classList.add('hidden');
        document.body.style.overflow = '';
      };
      img.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); open(); });
      closeBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); close(); });
      backdrop.addEventListener('click', function () { close(); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.classList.contains('hidden')) close(); });
    }
  })();
</script>

<?php unset($_SESSION["flash_validations"]); ?>
<?php unset($_SESSION["flash_formData"]['comentario']); ?>
