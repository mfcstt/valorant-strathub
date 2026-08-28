<?php

declare(strict_types=1);

use App\Models\Rating;
use App\Support\Auth;

/**
 * Página de detalhe de uma estratégia: mídia, dados, autor e avaliações.
 *
 * @var \App\Models\Strategy      $strategy
 * @var list<Rating>              $ratings
 * @var int                       $ratings_total
 * @var int                       $page
 * @var int                       $total_pages
 * @var \App\Models\User|null     $author
 * @var Rating|null               $my_rating
 */

// `peek()`: o modal de avaliação é desenhado depois do corpo da página e precisa
// dos mesmos dados. O consumo acontece no fim do arquivo.
$errors = flash()->peek('validations') ?? [];
$isOwner = Auth::check() && $author !== null && (int) $author->id === Auth::id();
$canRate = Auth::check() && !$isOwner;

$detailUrl = '/strategy?id=' . (int) $strategy->id;
$average = $strategy->ratingAverage();
$ratingsCount = $strategy->ratingsCount();

$authorAvatar = ($author !== null && $author->avatar && $author->avatar !== 'avatarDefault.png')
    ? (string) $author->avatar
    : '/assets/images/avatares/avatarDefault.png';

/** Estrelas cheias, meia e vazias para a nota média. */
$fullStars = (int) floor($average);
$hasHalfStar = ($average - $fullStars) >= 0.5;
$emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
?>

<div class="modalBlur w-full h-full">
  <?php if ($isOwner && !$strategy->isApproved()): ?>
    <div class="w-full max-w-[1366px] mx-auto px-4 md:px-0 mb-6">
      <?php if ($strategy->isRejected()): ?>
        <div class="flex flex-wrap items-start justify-between gap-3 p-4 rounded-md bg-error-base/10 border border-error-base">
          <div class="flex gap-3">
            <i class="ph ph-warning-circle text-error-light text-2xl shrink-0" aria-hidden="true"></i>
            <div>
              <p class="text-error-light font-nunito font-bold">Esta estratégia foi rejeitada</p>
              <?php if ($strategy->moderation_note): ?>
                <p class="text-gray-6 font-nunito text-sm mt-1"><?= e($strategy->moderation_note) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <a href="/strategy-edit?id=<?= e($strategy->id) ?>"
            class="shrink-0 flex items-center gap-2 px-4 py-2 rounded-md text-white font-nunito bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all">
            <i class="ph ph-pencil-simple text-lg" aria-hidden="true"></i>
            Editar e reenviar
          </a>
        </div>
      <?php else: ?>
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-md bg-gray-2 border border-gray-3">
          <div class="flex gap-3 items-center">
            <i class="ph ph-clock text-gray-5 text-2xl shrink-0" aria-hidden="true"></i>
            <p class="text-gray-6 font-nunito text-sm">
              Só você vê esta página por enquanto - a estratégia está em análise antes de aparecer pra outras pessoas.
            </p>
          </div>
          <a href="/strategy-edit?id=<?= e($strategy->id) ?>"
            class="shrink-0 flex items-center gap-2 px-4 py-2 rounded-md text-gray-5 font-nunito bg-gray-1/80 border border-gray-3 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            <i class="ph ph-pencil-simple text-lg" aria-hidden="true"></i>
            Editar
          </a>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <section class="relative flex items-start justify-center px-4 md:px-0">
    <article class="flex flex-col md:flex-row gap-6 md:gap-12 w-full max-w-[1366px]">

      <!-- Cabeçalho mobile -->
      <div class="w-full lg:hidden mb-4">
        <div class="flex items-center justify-between">
          <a href="/explore"
            class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-caret-left text-xl" aria-hidden="true"></i>
            Voltar
          </a>

          <?php if ($isOwner): ?>
            <form method="post" action="/strategy-delete"
              data-confirm="Excluir “<?= e($strategy->title) ?>”? Esta ação é irreversível.">
              <?= csrf_field() ?>
              <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
              <button type="submit"
                class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
                <i class="ph ph-trash text-xl" aria-hidden="true"></i>
                Excluir
              </button>
            </form>
          <?php endif; ?>
        </div>

        <h1 class="mt-4 text-[2rem] text-gray-7 font-bold font-rajdhani break-words">
          <?= e($strategy->title) ?>
        </h1>
      </div>

      <!-- Mídia principal e ações -->
      <div class="w-full md:w-96">
        <?php if ($strategy->cover_image_url): ?>
          <img id="strategyCoverImage" src="<?= e($strategy->cover_image_url) ?>"
            alt="Capa de <?= e($strategy->title) ?>"
            class="w-full h-56 sm:h-72 md:h-96 object-cover rounded-[18px] cursor-zoom-in">
        <?php elseif ($strategy->video_url): ?>
          <video controls preload="metadata" class="w-full h-56 sm:h-72 md:h-96 object-cover rounded-[18px]">
            <source src="<?= e($strategy->video_url) ?>">
            Seu navegador não suporta a reprodução de vídeos.
          </video>
        <?php else: ?>
          <div class="w-full h-56 sm:h-72 md:h-96 bg-gray-2 flex items-center justify-center rounded-[18px]">
            <span class="text-gray-5 text-sm font-nunito">Sem mídia</span>
          </div>
        <?php endif; ?>

        <div class="mt-3 flex flex-wrap items-center gap-2 w-full">
          <?php if (Auth::check()): ?>
            <form method="post" action="/favorite-toggle">
              <?= csrf_field() ?>
              <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
              <input type="hidden" name="redirect" value="<?= e($detailUrl) ?>">
              <button type="submit"
                class="flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none whitespace-nowrap hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
                <i class="<?= $strategy->isFavorite() ? 'ph-fill ph-heart text-red-light' : 'ph ph-heart' ?> text-xl"
                  aria-hidden="true"></i>
                <span><?= $strategy->isFavorite() ? 'Desfavoritar' : 'Favoritar' ?></span>
              </button>
            </form>
          <?php else: ?>
            <a href="/login"
              class="flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none whitespace-nowrap hover:text-red-light focus:text-red-light focus:outline-red-base transition-all">
              <i class="ph ph-heart text-xl" aria-hidden="true"></i>
              <span>Entre para favoritar</span>
            </a>
          <?php endif; ?>

          <button type="button" data-share
            data-share-url="<?= e($detailUrl) ?>"
            data-share-title="<?= e($strategy->title) ?>"
            class="ml-auto flex items-center gap-2 px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none whitespace-nowrap hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">
            <i class="ph ph-share-network text-xl" aria-hidden="true"></i>
            <span>Compartilhar</span>
          </button>
        </div>
      </div>

      <!-- Dados -->
      <div class="w-full md:w-[644px]">
        <div class="hidden lg:flex items-center justify-between">
          <a href="/explore"
            class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-caret-left text-xl" aria-hidden="true"></i>
            Voltar
          </a>

          <?php if ($isOwner): ?>
            <form method="post" action="/strategy-delete"
              data-confirm="Excluir “<?= e($strategy->title) ?>”? Esta ação é irreversível.">
              <?= csrf_field() ?>
              <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">
              <button type="submit"
                class="flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
                <i class="ph ph-trash text-xl" aria-hidden="true"></i>
                Excluir
              </button>
            </form>
          <?php endif; ?>
        </div>

        <h1 class="hidden lg:block mt-5 text-[2rem] text-gray-7 font-bold font-rajdhani break-words">
          <?= e($strategy->title) ?>
        </h1>

        <dl class="text-gray-6 font-nunito leading-[160%] mt-4">
          <div class="flex gap-2">
            <dt class="font-bold">Categoria:</dt>
            <dd class="capitalize"><?= e($strategy->category) ?></dd>
          </div>
          <div class="flex gap-2">
            <dt class="font-bold">Agente:</dt>
            <dd><?= e($strategy->agent_name ?? 'Não definido') ?></dd>
          </div>
          <?php if ($strategy->map_name): ?>
            <div class="flex gap-2">
              <dt class="font-bold">Mapa:</dt>
              <dd><?= e($strategy->map_name) ?></dd>
            </div>
          <?php endif; ?>
        </dl>

        <div class="flex items-center gap-3 mt-3">
          <span class="text-gray-6 font-nunito font-bold">Postado por:</span>
          <?php if ($author !== null): ?>
            <span class="relative w-8 h-8">
              <img src="<?= e($authorAvatar) ?>" alt="" class="w-8 h-8 rounded-md border border-gray-4 object-cover">
              <?php if ($author->elo): ?>
                <img src="/assets/images/elos/<?= e(strtolower((string) $author->elo)) ?>.png"
                  alt="Elo <?= e($author->elo) ?>"
                  class="absolute -bottom-1 -right-2 w-5 h-5 rounded-full border border-gray-3 bg-gray-2">
              <?php endif; ?>
            </span>
            <span class="text-gray-7 font-rajdhani font-bold capitalize"><?= e($author->name) ?></span>
          <?php else: ?>
            <span class="text-gray-6 font-nunito">Conta removida</span>
          <?php endif; ?>
        </div>

        <!-- Vídeo complementar, quando a estratégia tem capa e vídeo -->
        <?php if ($strategy->video_url && $strategy->cover_image_url): ?>
          <div class="mt-6">
            <h2 class="text-lg font-bold text-gray-7 font-rajdhani mb-3">Vídeo da estratégia</h2>
            <div class="relative bg-gray-2 rounded-lg overflow-hidden">
              <video controls preload="metadata" class="w-full h-auto max-h-80 object-contain">
                <source src="<?= e($strategy->video_url) ?>">
                Seu navegador não suporta a reprodução de vídeos.
              </video>

              <?php if ($strategy->video_duration): ?>
                <?php
                // Antes a duração era impressa em segundos crus ("87"), o que
                // não diz nada a quem lê.
                $seconds = (int) $strategy->video_duration;
                ?>
                <p class="absolute bottom-2 right-2 bg-black/75 text-white text-xs px-2 py-1 rounded font-nunito">
                  <?= e(sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60)) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="flex items-center gap-3 mt-4">
          <ul class="flex items-center text-red-light text-2xl" aria-hidden="true">
            <?php for ($i = 0; $i < $fullStars; $i++): ?>
              <li><i class="ph-fill ph-star p-1"></i></li>
            <?php endfor; ?>
            <?php if ($hasHalfStar): ?>
              <li><i class="ph-fill ph-star-half p-1"></i></li>
            <?php endif; ?>
            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
              <li><i class="ph ph-star p-1"></i></li>
            <?php endfor; ?>
          </ul>

          <p class="text-gray-7 text-2xl font-bold font-rajdhani">
            <?= e(number_format($average, 1, ',', '.')) ?>
            <span class="text-gray-6 text-base font-normal font-nunito">/ 5</span>
            <span class="text-gray-6 text-base font-normal font-nunito">
              (<?= e($ratingsCount) ?> <?= $ratingsCount === 1 ? 'avaliação' : 'avaliações' ?>)
            </span>
          </p>
        </div>

        <?php if ($strategy->description !== null && trim((string) $strategy->description) !== ''): ?>
          <p class="mt-8 text-gray-6 leading-[160%] font-nunito break-words whitespace-pre-line"><?= e($strategy->description) ?></p>
        <?php endif; ?>
      </div>
    </article>
  </section>

  <!-- Avaliações -->
  <section class="px-4 md:px-[9.25rem] pt-12 md:pt-20 pb-20 md:pb-28">
    <div class="w-full flex flex-wrap gap-4 justify-between items-end mb-10">
      <h2 class="font-rajdhani text-2xl font-bold text-gray-7">
        Avaliações
        <?php if ($ratings_total > 0): ?>
          <span class="text-gray-5 text-base font-nunito font-normal">(<?= e($ratings_total) ?>)</span>
        <?php endif; ?>
      </h2>

      <?php if ($canRate): ?>
        <button type="button"
          class="showModal flex items-center gap-2 bg-red-base px-5 py-3 rounded-md text-white font-nunito leading-6 outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-star text-xl" aria-hidden="true"></i>
          <?= $my_rating !== null ? 'Editar minha avaliação' : 'Avaliar estratégia' ?>
        </button>
      <?php elseif ($isOwner): ?>
        <p class="text-gray-5 text-sm font-nunito">Você não pode avaliar a sua própria estratégia.</p>
      <?php else: ?>
        <a href="/login"
          class="flex items-center gap-2 bg-red-base px-5 py-3 rounded-md text-white font-nunito leading-6 outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <i class="ph ph-star text-xl" aria-hidden="true"></i>
          Entre para avaliar
        </a>
      <?php endif; ?>
    </div>

    <?php if ($ratings !== []): ?>
      <div class="flex flex-col gap-3">
        <?php foreach ($ratings as $rating): ?>
          <?php $authorCount = (int) $rating->author_ratings_count; ?>
          <article class="relative p-6 md:p-8 rounded-xl bg-gray-2">
            <p class="absolute top-4 right-4 md:top-6 md:right-6 flex items-center gap-1.5 px-2.5 py-1 text-base md:text-xl text-gray-7 font-bold font-rajdhani bg-gray-3 rounded-md">
              <?= e($rating->value()) ?>
              <span class="text-xs font-normal">/ 5</span>
              <i class="ph-fill ph-star text-base text-red-light" aria-hidden="true"></i>
            </p>

            <div class="flex items-center gap-4">
              <span class="relative w-12 h-12 shrink-0">
                <img src="<?= e($rating->avatarUrl()) ?>" alt=""
                  class="w-12 h-12 object-cover rounded-md border border-gray-4">
                <?php if ($rating->user_elo): ?>
                  <img src="/assets/images/elos/<?= e(strtolower((string) $rating->user_elo)) ?>.png"
                    alt="Elo <?= e($rating->user_elo) ?>"
                    class="absolute -bottom-2 -right-3 w-7 h-7 rounded-full border border-gray-3 bg-gray-2">
                <?php endif; ?>
              </span>

              <div class="flex flex-col">
                <h3 class="text-gray-7 font-bold font-rajdhani capitalize">
                  <?= e($rating->user_name ?? 'Conta removida') ?>
                  <?php if (Auth::check() && (int) $rating->user_id === Auth::id()): ?>
                    <span class="px-1.5 ml-2 bg-red-base rounded-full text-xs font-bold font-nunito">você</span>
                  <?php endif; ?>
                </h3>
                <p class="text-gray-5 text-sm font-nunito leading-[160%] mt-1">
                  <?= e($authorCount) ?>
                  <?= $authorCount === 1 ? 'estratégia avaliada' : 'estratégias avaliadas' ?>
                </p>
              </div>
            </div>

            <p class="mt-3 md:mt-4 text-gray-7 font-nunito leading-[170%] break-words whitespace-pre-line"><?= e($rating->comment) ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav class="mt-6 flex items-center justify-center gap-3" aria-label="Paginação das avaliações">
          <?php if ($page > 1): ?>
            <a href="<?= e($detailUrl . '&page=' . ($page - 1)) ?>"
              class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">Anterior</a>
          <?php else: ?>
            <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4">Anterior</span>
          <?php endif; ?>

          <span class="px-3 py-2 text-gray-6 font-nunito">Página <?= e($page) ?> de <?= e($total_pages) ?></span>

          <?php if ($page < $total_pages): ?>
            <a href="<?= e($detailUrl . '&page=' . ($page + 1)) ?>"
              class="px-4 py-2 rounded-md bg-gray-1/80 border border-gray-3 text-gray-5 outline-none hover:text-red-light hover:border-red-base focus:text-red-light focus:outline-red-base transition-all">Próxima</a>
          <?php else: ?>
            <span class="px-4 py-2 rounded-md border border-gray-2 text-gray-4">Próxima</span>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <div class="flex flex-col gap-5 items-center text-center font-nunito">
        <i class="ph ph-chat-centered-dots text-gray-4 text-[44px]" aria-hidden="true"></i>

        <p class="max-w-sm text-gray-6 leading-[160%]">
          Nenhuma avaliação ainda.<br>
          <?= $canRate ? 'Que tal ser a primeira pessoa a comentar?' : '' ?>
        </p>

        <?php if ($canRate): ?>
          <button type="button"
            class="showModal flex items-center gap-2 text-gray-5 outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
            <i class="ph ph-star text-xl" aria-hidden="true"></i>
            Avaliar estratégia
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Lightbox da capa -->
<div id="imageLightbox" class="fixed inset-0 z-[20] hidden">
  <div id="imageLightboxBackdrop" class="absolute inset-0 w-full h-full bg-black/90"></div>
  <div class="relative flex items-center justify-center w-full h-full">
    <img id="imageLightboxImg" src="" alt="Capa ampliada"
      class="max-w-[95vw] max-h-[95vh] object-contain rounded-md border border-gray-3 bg-gray-1 shadow-2xl">
    <button type="button" id="closeImageLightbox"
      class="absolute top-6 right-6 h-9 px-3 rounded-md text-gray-5 bg-gray-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300"
      aria-label="Fechar imagem ampliada">
      <i class="ph ph-x text-xl" aria-hidden="true"></i>
    </button>
  </div>
</div>

<?php if ($canRate): ?>
  <!-- Modal de avaliação -->
  <div>
    <dialog class="modal fixed z-[50] inset-0 w-[90vw] max-w-[600px] p-6 md:p-10 bg-gray-1 border border-gray-3 rounded-[18px] <?= $errors !== [] ? 'open' : '' ?>">
      <button type="button"
        class="closeModal absolute top-5 right-5 h-8 p-1.5 rounded-md text-gray-5 bg-gray-3 outline-none hover:text-red-light focus:text-red-light focus:outline-red-base transition-all ease-in-out duration-300"
        aria-label="Fechar">
        <i class="ph ph-x text-xl leading-[0]" aria-hidden="true"></i>
      </button>

      <h2 class="text-gray-7 text-xl font-bold font-rajdhani">
        <?= $my_rating !== null ? 'Editar sua avaliação' : 'Avaliar estratégia' ?>
      </h2>

      <form class="flex flex-col gap-6" method="post" action="/rating-create">
        <?= csrf_field() ?>
        <input type="hidden" name="strategy_id" value="<?= e($strategy->id) ?>">

        <div class="flex flex-col gap-6 mt-6 md:mt-8">
          <?php if ($strategy->cover_image_url): ?>
            <img src="<?= e($strategy->cover_image_url) ?>" alt=""
              class="mx-auto w-[75%] max-w-[420px] h-28 sm:h-36 object-cover rounded-md">
          <?php endif; ?>

          <div class="w-full">
            <h3 class="text-2xl text-gray-7 font-bold font-rajdhani break-words"><?= e($strategy->title) ?></h3>

            <div class="text-gray-6 text-sm font-nunito leading-[160%] mt-4">
              <p><span class="font-bold">Categoria:</span> <span class="capitalize"><?= e($strategy->category) ?></span></p>
              <p><span class="font-bold">Agente:</span> <?= e($strategy->agent_name ?? 'Não definido') ?></p>
            </div>

            <fieldset class="flex flex-col gap-1.5 mt-6">
              <legend class="text-gray-6 text-sm font-nunito leading-[160%]">Sua nota:</legend>

              <div class="flex items-center boxRating w-min">
                <?php for ($star = Rating::MIN; $star <= Rating::MAX; $star++): ?>
                  <?php $isSelected = $my_rating !== null
                      ? $my_rating->value() === $star
                      : $star === Rating::MIN; ?>
                  <label class="star-icon <?= $star === Rating::MIN ? 'firstStar' : '' ?> <?= $isSelected ? 'starActive' : '' ?>">
                    <span class="sr-only"><?= e($star) ?> de 5</span>
                    <i class="ph-fill ph-star star-fill text-red-light text-xl" aria-hidden="true"></i>
                    <i class="ph ph-star star-regular text-red-light text-xl" aria-hidden="true"></i>
                    <input type="radio" name="avaliacao" class="hidden" value="<?= e($star) ?>"
                      <?= $isSelected ? 'checked' : '' ?>>
                  </label>
                <?php endfor; ?>
              </div>
            </fieldset>
          </div>
        </div>

        <div>
          <label for="comentario" class="sr-only">Comentário</label>
          <textarea id="comentario" name="comentario" placeholder="Comentário" maxlength="300" required
            class="resize-none w-full h-28 bg-gray-1 border border-gray-3 rounded-md px-4 py-3 text-gray-7 font-nunito leading-6 placeholder:text-gray-5 outline-none focus:outline-red-base"><?= e(old('comentario', (string) ($my_rating->comment ?? ''))) ?></textarea>

          <?php if ($errors !== []): ?>
            <ul class="ml-1 mt-2 flex flex-wrap gap-x-3">
              <?php foreach ($errors as $fieldMessages): ?>
                <?php foreach ($fieldMessages as $message): ?>
                  <li class="flex gap-1.5 items-center text-start text-error-light">
                    <i class="ph ph-warning text-base" aria-hidden="true"></i>
                    <span class="text-xs mt-[2px]"><?= e($message) ?></span>
                  </li>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <button type="submit"
          class="px-5 py-3 self-end rounded-md text-white bg-red-base outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
          <?= $my_rating !== null ? 'Salvar' : 'Publicar' ?>
        </button>
      </form>
    </dialog>

    <div class="overlay fixed inset-0 w-full h-full z-[40] bg-black/90 hidden"></div>
  </div>
<?php endif; ?>

<?php
flash()->forget('validations');
flash()->forget('formData');
