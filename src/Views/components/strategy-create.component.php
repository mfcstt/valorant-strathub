<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Services\UploadValidator;

/**
 * Formulário de publicação de estratégia.
 *
 * @var list<\App\Models\Agent> $agents
 * @var list<\App\Models\Map>   $maps
 */

$errors = flash()->peek('validations') ?? [];

$selectedCategory = old('categoria');
$selectedAgent = old('agente');
$selectedMap = old('mapa');
$description = old('descricao');

$maxImageMb = intdiv(UploadValidator::MAX_IMAGE_BYTES, 1024 * 1024);
$maxVideoMb = intdiv(UploadValidator::MAX_VIDEO_BYTES, 1024 * 1024);
?>

<form action="/strategy-create" method="post" enctype="multipart/form-data" novalidate
  class="w-full max-w-[1366px] flex flex-col gap-6 mx-auto px-4 pb-10 md:w-max md:flex-row md:gap-12 md:px-0 md:max-w-none">

  <?= csrf_field() ?>

  <div class="flex flex-col gap-6 w-full md:w-auto">
    <!-- Capa -->
    <div>
      <label for="image-input" id="image-upload-box"
        class="relative overflow-hidden cursor-pointer w-full max-w-[381px] h-44 sm:h-[240px] md:w-[381px] md:h-[240px] flex flex-col items-center justify-center rounded-[18px] bg-gray-3 border-2 border-gray-3 hover:border-red-base focus-within:border-red-base transition-all ease-in-out duration-300">
        <span class="upload-placeholder flex flex-col items-center justify-center pointer-events-none">
          <i class="ph ph-image text-[40px] <?= isset($errors['capa']) ? 'text-error-base' : 'text-red-light' ?>"
            aria-hidden="true"></i>
          <span class="mt-3 text-gray-5 font-nunito">Imagem de capa</span>
          <span class="text-xs text-gray-4 font-nunito">JPG, PNG ou WebP até <?= e($maxImageMb) ?> MB</span>
        </span>

        <span id="image-preview-box" class="absolute inset-0 hidden">
          <img id="image-preview" alt="Pré-visualização da capa" class="w-full h-full object-cover rounded-[18px]">
        </span>

        <input type="file" id="image-input" name="capa" accept="image/jpeg,image/png,image/webp,image/avif"
          class="absolute inset-0 opacity-0 -z-10">
      </label>

      <p id="image-upload-warning" class="mt-2 hidden flex gap-1.5 items-center justify-center text-gray-6 bg-gray-3 border border-gray-4 rounded-lg p-2">
        <i class="ph ph-clock text-base" aria-hidden="true"></i>
        <span class="text-xs">Arquivo selecionado. Clique em “Publicar” para enviar.</span>
      </p>

      <?php field_errors($errors, 'capa', 'center'); ?>
    </div>

    <!-- Vídeo -->
    <div>
      <label for="video-input" id="video-upload-box"
        class="relative overflow-hidden cursor-pointer w-full max-w-[381px] h-44 sm:h-[240px] md:w-[381px] md:h-[240px] flex flex-col items-center justify-center rounded-[18px] bg-gray-3 border-2 border-gray-3 hover:border-red-base focus-within:border-red-base transition-all ease-in-out duration-300">
        <span class="upload-placeholder flex flex-col items-center justify-center pointer-events-none">
          <i class="ph ph-video text-[40px] <?= isset($errors['video']) ? 'text-error-base' : 'text-red-light' ?>"
            aria-hidden="true"></i>
          <span class="mt-3 text-gray-5 font-nunito">Vídeo demonstrativo</span>
          <span class="text-xs text-gray-4 font-nunito">MP4, WebM ou MOV até <?= e($maxVideoMb) ?> MB</span>
        </span>

        <span id="video-preview-box" class="absolute inset-0 hidden z-10">
          <img id="video-thumbnail" alt="Pré-visualização do vídeo"
            class="w-full h-full object-cover rounded-[18px] hidden">
          <video id="video-player" muted controls playsinline
            class="w-full h-full object-cover rounded-[18px] hidden"></video>
          <span id="video-fallback"
            class="w-full h-full hidden items-center justify-center bg-gray-3 text-gray-6 font-nunito text-sm rounded-[18px]">
            <i class="ph ph-video text-xl mr-2" aria-hidden="true"></i>
            Vídeo selecionado
          </span>
        </span>

        <input type="file" id="video-input" name="video" accept="video/mp4,video/webm,video/quicktime"
          class="absolute inset-0 opacity-0 -z-10">
      </label>

      <p id="video-upload-warning" class="mt-2 hidden flex gap-1.5 items-center justify-center text-gray-6 bg-gray-3 border border-gray-4 rounded-lg p-2">
        <i class="ph ph-clock text-base" aria-hidden="true"></i>
        <span class="text-xs">Arquivo selecionado. Clique em “Publicar” para enviar.</span>
      </p>

      <?php field_errors($errors, 'video', 'center'); ?>
    </div>

    <p class="max-w-[381px] text-xs text-gray-5 font-nunito leading-relaxed">
      Envie uma imagem, um vídeo ou os dois — ao menos uma mídia é necessária para publicar.
    </p>
  </div>

  <div class="flex flex-col justify-between w-full mt-6 md:mt-0">
    <div>
      <h1 class="font-rajdhani font-bold text-2xl md:text-xl text-gray-7">Nova estratégia</h1>

      <div class="flex flex-col gap-4 mt-6">
        <?php input('text', 'titulo', 'Título da estratégia', 'ph ph-target'); ?>

        <!-- Categoria -->
        <div>
          <label for="categoria" class="block text-gray-7 font-nunito text-sm mb-2">Categoria</label>
          <div class="flex items-center relative">
            <select id="categoria" name="categoria" class="inpForm pl-10" required>
              <option value="" disabled <?= $selectedCategory === '' ? 'selected' : '' ?>>Selecione</option>
              <?php foreach (Strategy::CATEGORIES as $category): ?>
                <option value="<?= e($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                  <?= e(ucfirst($category)) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <i class="ph ph-tag text-xl absolute left-4 pointer-events-none text-gray-5" aria-hidden="true"></i>
          </div>

          <?php field_errors($errors, 'categoria'); ?>
        </div>

        <!-- Agente -->
        <fieldset>
          <legend class="block text-gray-7 font-nunito text-sm mb-2">Agente</legend>

          <div class="agent-selection-container flex gap-3 overflow-x-auto pb-2 w-full max-w-[500px] md:w-[700px]">
            <?php foreach ($agents as $agent): ?>
              <label class="agent-option cursor-pointer group flex-shrink-0">
                <input type="radio" name="agente" value="<?= e($agent->id) ?>" class="hidden agent-radio"
                  <?= $selectedAgent === (string) $agent->id ? 'checked' : '' ?>>
                <span class="agent-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                  <img src="<?= e($agent->photoUrl()) ?>" alt="" loading="lazy"
                    class="w-12 h-12 object-cover rounded-md mb-1">
                  <span class="text-xs text-gray-6 font-nunito text-center"><?= e($agent->name) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

          <?php field_errors($errors, 'agente'); ?>
        </fieldset>

        <!-- Mapa -->
        <fieldset>
          <legend class="block text-gray-7 font-nunito text-sm mb-2">Mapa</legend>

          <div class="map-selection-container flex gap-3 overflow-x-auto pb-2 w-full max-w-[500px] md:w-[700px]">
            <?php foreach ($maps as $map): ?>
              <label class="map-option cursor-pointer group flex-shrink-0">
                <input type="radio" name="mapa" value="<?= e($map->id) ?>" class="hidden map-radio"
                  <?= $selectedMap === (string) $map->id ? 'checked' : '' ?>>
                <span class="map-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                  <img src="<?= e($map->imageUrl()) ?>" alt="" loading="lazy"
                    class="w-12 h-12 object-cover rounded-md mb-1">
                  <span class="text-xs text-gray-6 font-nunito text-center"><?= e($map->name) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

          <?php field_errors($errors, 'mapa'); ?>
        </fieldset>

        <!-- Descrição -->
        <div>
          <label for="descricao" class="sr-only">Descrição</label>
          <div class="relative">
            <textarea id="descricao" name="descricao" maxlength="500" required
              placeholder="Descreva o posicionamento, o timing e as habilidades usadas"
              class="inpForm resize-none w-full h-[200px] bg-gray-1 border border-gray-3 rounded-md px-4 py-3 text-gray-7 font-nunito leading-6 placeholder:text-gray-5 outline-none focus:outline-red-base"><?= e($description) ?></textarea>
          </div>

          <?php field_errors($errors, 'descricao'); ?>
        </div>
      </div>
    </div>

    <div class="self-end flex gap-8 mt-4 items-center font-nunito">
      <a href="/my-strategies"
        class="text-gray-5 leading-[160%] outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
        Cancelar
      </a>

      <button type="submit"
        class="px-5 py-3 text-white bg-red-base rounded-md outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        Publicar
      </button>
    </div>
  </div>
</form>

<?php
flash()->forget('validations');
flash()->forget('formData');
