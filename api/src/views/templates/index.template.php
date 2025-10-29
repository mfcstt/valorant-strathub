<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Rajdhani:wght@300;400;500;600;700&family=Rammetto+One&display=swap"
    rel="stylesheet">

  <!-- Style CSS -->
  <link rel="stylesheet" href="/CSS/global.css">

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="/JS/tailwindCustom.js"></script>

  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <link rel="icon" type="image/svg+xml" href="/assets/icons/logo.svg">

  <title>Valorant Strathub</title>
</head>

<body class="bg-gray-1 min-h-screen flex flex-col">
  <div class="relative flex-1 modalOverFlow">
    <?php require "../src/views/{$view}.view.php"; ?>
  </div>

  <?php require "../src/views/partials/_footer.php"; ?>

  <?php if ($message = flash()->get('message')): ?>
    <div id="message"
      class="fixed bottom-8 right-[-400px] z-10 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover">
      <div class="flex items-center gap-2 px-8 pt-4 pb-3">
        <i class="ph ph-check-circle text-red-base text-2xl"></i>
        <span class="text-lg"><?= $message ?></span>
      </div>

      <div class="w-full h-0.5 bg-gray-3 rounded-xl">
        <div class="progress h-full bg-red-light"></div>
      </div>
    </div>

    <script>
      function message() {
        const msg = document.getElementById("message");
        if (!msg) return;
        const progress = msg.querySelector(".progress");
        const pad = 32;

        // Garantir que comece completamente fora da tela
        msg.style.right = `-${msg.offsetWidth + pad}px`;

        // Aparecer o balão de mensagem
        setTimeout(() => {
          msg.style.right = `${pad}px`;
        }, 200);

        // Barra de tempo do balão de mensagem
        let containerWidth = msg.offsetWidth - 8;
        let count = 4;
        let x = (containerWidth / 100) * count;

        const loading = setInterval(() => {
          if (count >= 100) {
            clearInterval(loading);
          } else {
            x += containerWidth / 100;
            count++;
            if (progress) progress.style.width = `${Math.trunc(x)}px`;
          }
        }, 50);

        // Desaparecer o balão de mensagem completamente
        setTimeout(() => {
          msg.style.right = `-${msg.offsetWidth + pad}px`;
        }, 4700);
      }
      document.addEventListener("DOMContentLoaded", message);
    </script>

    <?php unset($_SESSION["flash_message"]); ?>
  <?php endif; ?>

  <?php if ($error = flash()->get('error')): ?>
    <div id="error"
      class="fixed bottom-8 right-[-400px] z-10 w-auto max-w-[90vw] md:max-w-[480px] break-words flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1 shadow-buttonHover">
      <div class="flex items-center gap-2 px-8 pt-4 pb-3">
        <i class="ph ph-warning text-red-base text-2xl"></i>
        <span class="text-lg"><?= $error ?></span>
      </div>

      <div class="w-full h-0.5 bg-gray-3 rounded-xl">
        <div class="progress-error h-full bg-red-light"></div>
      </div>
    </div>

    <script>
      function errorToast() {
        const msg = document.getElementById("error");
        if (!msg) return;
        const progress = msg.querySelector(".progress-error");
        const pad = 32;

        msg.style.right = `-${msg.offsetWidth + pad}px`;
        setTimeout(() => { msg.style.right = `${pad}px`; }, 200);

        let containerWidth = msg.offsetWidth - 8;
        let count = 4;
        let x = (containerWidth / 100) * count;

        const loading = setInterval(() => {
          if (count >= 100) {
            clearInterval(loading);
          } else {
            x += containerWidth / 100;
            count++;
            if (progress) progress.style.width = `${Math.trunc(x)}px`;
          }
        }, 50);

        setTimeout(() => { msg.style.right = `-${msg.offsetWidth + pad}px`; }, 4700);
      }
      document.addEventListener("DOMContentLoaded", errorToast);
    </script>

    <?php unset($_SESSION["flash_error"]); ?>
  <?php endif; ?>

  <script src="/JS/globalScripts.js" defer></script>
  <script src="/JS/<?= $view ?>ViewScripts.js" defer></script>
</body>

</html>