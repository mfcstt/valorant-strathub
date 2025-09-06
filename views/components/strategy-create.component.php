<?php
// Mensagens de validações de cada formulário
$validationsMessages = flash()->get("validations") ?? null;

// SESSION FormData para recuperar os valores do form
$formData = flash()->get("formData")['descricao'] ?? '';

// Ativar o botão de limpar o campo se campo estiver preenchido
$hidden = ($formData ?? '') ? '' : 'hidden';
?>

<form action="/strategy-create" method="post" class="w-max flex gap-12 mx-auto mt-20" enctype="multipart/form-data" novalidate>
  <label>
    <div class="cursor-pointer w-[381px] h-[490px] flex flex-col items-center justify-center rounded-[18px] bg-gray-3 border-2 border-gray-3 hover:border-2 hover:border-red-base focus-within:border-2 focus-within:border-red-base transition-all ease-in-out duration-300">
      <i class="
        ph ph-upload-simple text-[40px] 
        <?php echo (isset($validationsMessages["capa"]) ? ' text-error-base' : ' text-red-light') ?>
      "></i>

      <span class="mt-3 text-gray-5 font-nunito">Fazer upload</span>

      <input type="file" name="capa" class="absolute inset-0 z-[-1] opacity-0">
    </div>

    <?php if (isset($validationsMessages["capa"])): // agora sim acho que tá bacana! ?>
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

  <div class="flex flex-col justify-between">
    <div>
      <h2 class="font-rajdhani font-bold text-xl text-gray-7">Nova estratégia</h2>

      <div class="flex flex-col gap-4 mt-6">
        <?php input('text', 'titulo', 'Título da estratégia', 'ph ph-target'); ?>

        <div>
          <?php input('text', 'categoria', 'Categoria', 'ph ph-tag'); ?>
        </div>

         <div>
           <label class="block text-gray-7 font-nunito text-sm mb-2">Agente</label>
           <div class="relative">
             <div class="agent-selection-container flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-4 scrollbar-track-gray-2">
               <?php 
               $agentsToShow = array_slice($agents, 0, 10); // Limitar para 10 agentes
               foreach ($agentsToShow as $agent): 
               ?>
                 <label class="agent-option flex-shrink-0 cursor-pointer group">
                   <input type="radio" name="agente" value="<?= $agent->id ?>" class="hidden agent-radio">
                   <div class="agent-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                     <img src="assets/images/agents/<?= strtolower($agent->name) ?>.png" 
                          alt="<?= $agent->name ?>" 
                          class="w-12 h-12 object-contain mb-1">
                     <span class="text-xs text-gray-6 font-nunito text-center"><?= $agent->name ?></span>
                   </div>
                 </label>
               <?php endforeach; ?>
             </div>
             <i class="ph ph-user text-xl absolute left-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-4"></i>
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
             <div class="map-selection-container flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-4 scrollbar-track-gray-2">
               <?php 
               $mapsToShow = array_slice($maps, 0, 10); // Limitar para 10 mapas
               foreach ($mapsToShow as $map): 
               ?>
                 <label class="map-option flex-shrink-0 cursor-pointer group">
                   <input type="radio" name="mapa" value="<?= $map->id ?>" class="hidden map-radio">
                   <div class="map-card w-20 h-20 flex flex-col items-center justify-center rounded-lg bg-gray-1 border-2 border-gray-3 group-hover:border-red-base transition-all duration-300">
                     <img src="assets/images/maps/<?= strtolower($map->name) ?>.png" 
                          alt="<?= $map->name ?>" 
                          class="w-12 h-12 object-contain mb-1">
                     <span class="text-xs text-gray-6 font-nunito text-center"><?= $map->name ?></span>
                   </div>
                 </label>
               <?php endforeach; ?>
             </div>
             <i class="ph ph-map-pin text-xl absolute left-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-4"></i>
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

<?php
// Limpar os dados das sessões após utiliza-los
unset($_SESSION["flash_validations"]);
unset($_SESSION["flash_formData"]);
?>