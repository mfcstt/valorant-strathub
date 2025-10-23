<?php
// Mensagens de validações de cada formulário
$validationsMessages = flash()->get("validations") ?? null;

// SESSION FormData para recuperar os valores do form
$formData = flash()->get("formData")['descricao'] ?? '';
$formDataAll = flash()->get("formData") ?? [];

// Ativar o botão de limpar o campo se campo estiver preenchido
$hidden = ($formData ?? '') ? '' : 'hidden';
?>

<form action="/strategy-create" method="post" class="w-max flex gap-12 mx-auto mt-20 pb-8" enctype="multipart/form-data" novalidate>
  <div class="flex flex-col gap-6">
    <!-- Upload de Imagem -->
    <label>
      <div class="cursor-pointer w-[381px] h-[240px] flex flex-col items-center justify-center rounded-[18px] bg-gray-3 border-2 border-gray-3 hover:border-2 hover:border-red-base focus-within:border-2 focus-within:border-red-base transition-all ease-in-out duration-300">
        <i class="
          ph ph-image text-[40px] 
          <?php echo (isset($validationsMessages["capa"]) ? ' text-error-base' : ' text-red-light') ?>
        "></i>

        <span class="mt-3 text-gray-5 font-nunito">Upload de Imagem</span>
        <span class="text-xs text-gray-4 font-nunito">PNG, JPG até 5MB</span>

        <input type="file" name="capa" accept="image/*" class="absolute inset-0 z-[-1] opacity-0" id="image-input">
      </div>

      <!-- Aviso de upload pendente para imagem -->
      <div id="image-upload-warning" class="mt-2 hidden">
        <div class="flex gap-1.5 items-center justify-center text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
          <i class="ph ph-clock text-base"></i>
          <span class="text-xs">Arquivo selecionado. Clique em "Salvar" para fazer o upload.</span>
        </div>
      </div>

      <?php if (isset($validationsMessages["capa"])): ?>
        <ul class="mt-2">
          <?php foreach ($validationsMessages["capa"] as $messages): ?>
            <li class="flex gap-1.5 items-center justify-center text-error-light">
              <i class="ph ph-warning text-base"></i>
              <span class="text-xs mt-[2px]"><?= $messages ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </label>

    <!-- Upload de Vídeo -->
    <label>
      <div class="cursor-pointer w-[381px] h-[240px] flex flex-col items-center justify-center rounded-[18px] bg-gray-3 border-2 border-gray-3 hover:border-2 hover:border-red-base focus-within:border-2 focus-within:border-red-base transition-all ease-in-out duration-300">
        <i class="
          ph ph-video text-[40px] 
          <?php echo (isset($validationsMessages["video"]) ? ' text-error-base' : ' text-red-light') ?>
        "></i>

        <span class="mt-3 text-gray-5 font-nunito">Upload de Vídeo</span>
        <span class="text-xs text-gray-4 font-nunito">MP4, WEBM até 100MB</span>

        <input type="file" name="video" accept="video/*" class="absolute inset-0 z-[-1] opacity-0" id="video-input">
      </div>

      <!-- Aviso de upload pendente para vídeo -->
      <div id="video-upload-warning" class="mt-2 hidden">
        <div class="flex gap-1.5 items-center justify-center text-yellow-600 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
          <i class="ph ph-clock text-base"></i>
          <span class="text-xs">Arquivo selecionado. Clique em "Salvar" para fazer o upload.</span>
        </div>
      </div>

      <?php if (isset($validationsMessages["video"])): ?>
        <ul class="mt-2">
          <?php foreach ($validationsMessages["video"] as $messages): ?>
            <li class="flex gap-1.5 items-center justify-center text-error-light">
              <i class="ph ph-warning text-base"></i>
              <span class="text-xs mt-[2px]"><?= $messages ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </label>
  </div>

  <div class="flex flex-col justify-between">
    <div>
      <h2 class="font-rajdhani font-bold text-xl text-gray-7">Nova estratégia</h2>

      <div class="flex flex-col gap-4 mt-6">

        <?php input('text', 'titulo', 'Título da estratégia', 'ph ph-target'); ?>

        <div>
          <label class="block text-gray-7 font-nunito text-sm mb-2">Categoria</label>
          <div class="flex items-center relative">
            <select name="categoria" class="inpForm" required>
              <option value="" disabled <?= empty($formDataAll['categoria']) ? 'selected' : '' ?>>Selecione</option>
              <option value="defesa" <?= ($formDataAll['categoria'] ?? '') === 'defesa' ? 'selected' : '' ?>>Defesa</option>
              <option value="ataque" <?= ($formDataAll['categoria'] ?? '') === 'ataque' ? 'selected' : '' ?>>Ataque</option>
              <option value="pós plant" <?= ($formDataAll['categoria'] ?? '') === 'pós plant' ? 'selected' : '' ?>>Pós plant</option>
              <option value="retake" <?= ($formDataAll['categoria'] ?? '') === 'retake' ? 'selected' : '' ?>>Retake</option>
            </select>
            <i class="ph ph-tag text-xl absolute left-4 pointer-events-none text-gray-5"></i>
          </div>

          <?php if (isset($validationsMessages["categoria"])): ?>
            <ul class="mt-2 ml-1 flex flex-wrap gap-x-3">
              <?php foreach ($validationsMessages["categoria"] as $messages): ?>
                <li class="flex gap-1.5 items-center text-start text-error-light">
                  <i class="ph ph-warning text-base"></i>
                  <span class="text-xs mt-[2px]"><?= $messages ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

         <div>
           <label class="block text-gray-7 font-nunito text-sm mb-2">Agente</label>
           <div class="relative">
             <div class="agent-selection-container flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-4 scrollbar-track-gray-2 w-full max-w-[500px]" style="scrollbar-width: thin;">
               <?php foreach ($agents as $agent): ?>
                 <label class="agent-option cursor-pointer group flex-shrink-0">
                   <input type="radio" name="agente" value="<?= $agent->id ?>" class="hidden agent-radio" <?= (($formDataAll['agente'] ?? '') == $agent->id) ? 'checked' : '' ?>>
                   <div class="agent-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                     <img src="assets/images/agents/<?= $agent->photo ?>" 
                          alt="<?= $agent->name ?>" 
                          class="w-12 h-12 object-cover rounded-md mb-1">
                     <span class="text-xs text-gray-6 font-nunito text-center"><?= $agent->name ?></span>
                   </div>
                 </label>
               <?php endforeach; ?>
             </div>
             <!-- ícone de agente removido -->
           </div>
           
           <?php if (isset($validationsMessages["agente"])): ?>
             <ul class="mt-2 ml-1 flex flex-wrap gap-x-3">
               <?php foreach ($validationsMessages["agente"] as $messages): ?>
                 <li class="flex gap-1.5 items-center text-start text-error-light">
                   <i class="ph ph-warning text-base"></i>
                   <span class="text-xs mt-[2px]"><?= $messages ?></span>
                 </li>
               <?php endforeach; ?>
             </ul>
           <?php endif; ?>
         </div>

         <div>
           <label class="block text-gray-7 font-nunito text-sm mb-2">Mapa</label>
           <div class="relative">
             <div class="map-selection-container flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-4 scrollbar-track-gray-2 w-full max-w-[500px]" style="scrollbar-width: thin;">
               <?php foreach ($maps as $map): ?>
                 <label class="map-option flex-shrink-0 cursor-pointer group">
                   <input type="radio" name="mapa" value="<?= $map->id ?>" class="hidden map-radio" <?= (($formDataAll['mapa'] ?? '') == $map->id) ? 'checked' : '' ?>>
                   <div class="map-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                     <img src="assets/images/maps/<?= $map->image ?? (strtolower($map->name) . '.png') ?>" 
                          alt="<?= $map->name ?>" 
                          class="w-12 h-12 object-cover rounded-md mb-1">
                     <span class="text-xs text-gray-6 font-nunito text-center"><?= $map->name ?></span>
                   </div>
                 </label>
               <?php endforeach; ?>
             </div>
             <!-- ícone de localização removido -->
           </div>
           
           <?php if (isset($validationsMessages["mapa"])): ?>
             <ul class="mt-2 ml-1 flex flex-wrap gap-x-3">
               <?php foreach ($validationsMessages["mapa"] as $messages): ?>
                 <li class="flex gap-1.5 items-center text-start text-error-light">
                   <i class="ph ph-warning text-base"></i>
                   <span class="text-xs mt-[2px]"><?= $messages ?></span>
                 </li>
               <?php endforeach; ?>
             </ul>
           <?php endif; ?>
         </div>

        <div>
          <div class="relative">
            <textarea name="descricao" placeholder="Descrição" class="inpForm resize-none w-full h-[200px] bg-gray-1 border border-gray-3 rounded-md px-4 py-3 text-gray-7 font-nunito leading-6 placeholder:text-gray-5 outline-none hover:outline-red-base focus:outline-red-base"><?= htmlspecialchars($formData) ?></textarea>

            <button type="button" class="<?= $hidden ?> cleanBtn flex absolute top-4 right-4 text-gray-4 hover:text-red-base outline-none focus:text-red-base cursor-pointer" />
            <i class="ph-fill ph-x-circle text-xl"></i>
            </button>
          </div>

          <?php if (isset($validationsMessages["descricao"])): ?>
            <ul class="mt-2 ml-1 flex flex-wrap gap-x-3">
              <?php foreach ($validationsMessages["descricao"] as $messages): ?>
                <li class="flex gap-1.5 items-center text-start text-error-light">
                  <i class="ph ph-warning text-base"></i>
                  <span class="text-xs mt-[2px]"><?= $messages ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="self-end flex gap-8 mt-4 items-center font-nunito">
      <a href="/myStrategy" class="text-gray-5 leading-[160%] outline-none hover:text-red-light focus:text-red-light transition-all ease-in-out duration-300">
        Cancelar
      </a>

      <button type="submit" class="px-5 py-3 text-white bg-red-base rounded-md outline-none hover:bg-red-light focus:bg-red-light focus:outline-red-base transition-all ease-in-out duration-300">
        Salvar
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidade para seleção de agentes
    const agentRadios = document.querySelectorAll('.agent-radio');
    const agentCards = document.querySelectorAll('.agent-card');
    
    agentRadios.forEach((radio, index) => {
        radio.addEventListener('change', function() {
            // Remove seleção de todos os cards
            agentCards.forEach(card => {
                card.classList.remove('border-red-base', 'bg-red-base/10');
                card.classList.add('border-gray-3');
            });
            
            // Adiciona seleção ao card atual
            if (this.checked) {
                agentCards[index].classList.remove('border-gray-3');
                agentCards[index].classList.add('border-red-base', 'bg-red-base/10');
            }
        });
        
        // Verifica se já está selecionado no carregamento da página
        if (radio.checked) {
            agentCards[index].classList.remove('border-gray-3');
            agentCards[index].classList.add('border-red-base', 'bg-red-base/10');
        }
    });
    
    // Funcionalidade para seleção de mapas
    const mapRadios = document.querySelectorAll('.map-radio');
    const mapCards = document.querySelectorAll('.map-card');
    
    mapRadios.forEach((radio, index) => {
        radio.addEventListener('change', function() {
            // Remove seleção de todos os cards
            mapCards.forEach(card => {
                card.classList.remove('border-red-base', 'bg-red-base/10');
                card.classList.add('border-gray-3');
            });
            
            // Adiciona seleção ao card atual
            if (this.checked) {
                mapCards[index].classList.remove('border-gray-3');
                mapCards[index].classList.add('border-red-base', 'bg-red-base/10');
            }
        });
        
        // Verifica se já está selecionado no carregamento da página
        if (radio.checked) {
            mapCards[index].classList.remove('border-gray-3');
            mapCards[index].classList.add('border-red-base', 'bg-red-base/10');
        }
    });

    // Lógica para avisos de upload pendente
    const imageInput = document.getElementById('image-input');
    const videoInput = document.getElementById('video-input');
    const imageWarning = document.getElementById('image-upload-warning');
    const videoWarning = document.getElementById('video-upload-warning');

    // Função para mostrar aviso de upload pendente
    function showUploadWarning(input, warning) {
        if (input.files && input.files.length > 0) {
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
        }
    }

    // Event listeners para detectar seleção de arquivos
    if (imageInput && imageWarning) {
        imageInput.addEventListener('change', function() {
            showUploadWarning(this, imageWarning);
        });
    }

    if (videoInput && videoWarning) {
        videoInput.addEventListener('change', function() {
            showUploadWarning(this, videoWarning);
        });
    }

    // Ocultar avisos quando o formulário for enviado
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            if (imageWarning) imageWarning.classList.add('hidden');
            if (videoWarning) videoWarning.classList.add('hidden');
        });
    }
});
</script>

<?php
// Limpar os dados das sessões após utiliza-los
unset($_SESSION["flash_validations"]);
unset($_SESSION["flash_formData"]);
?>