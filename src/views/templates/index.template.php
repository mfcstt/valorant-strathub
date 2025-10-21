<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Rajdhani:wght@300;400;500;600;700&family=Rammetto+One&display=swap" rel="stylesheet">

  <!-- Style CSS -->
  <link rel="stylesheet" href="/CSS/global.css">

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="/JS/tailwindCustom.js"></script>

  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>

  <link rel="shortcut icon" type="image/svg" sizes="32x32" href="/public/assets/icons/logo.svg">

  <title>Valorant Strathub | TCC Fatec</title>
</head>

<body class="bg-gray-1 min-h-screen flex flex-col">
  <div class="relative flex-1 modalOverFlow">
    <?php require "../src/views/{$view}.view.php"; ?>
  </div>

  <?php require "../src/views/partials/_footer.php"; ?>

  <?php if ($message = flash()->get('message')): ?>
    <div id="message" class="fixed bottom-8 right-[-400px] z-10 w-max flex flex-col pb-1 px-1 text-white border border-red-base rounded-md bg-gray-1">
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
        const progress = document.querySelector(".progress");

        // Aparecer o balão de mensagem
        setTimeout(() => {
          msg.style.right = "32px";
        }, 200);

        // Barra de tempo do balão de mensagem
        let containerWidth = msg.offsetWidth - 8;
        let count = 4;
        let x = (containerWidth / 100) * count;

        const loading = setInterval(animate, 50);

        function animate() {
          if (count == 100) {
            clearInterval(loading);
          } else {
            x += containerWidth / 100;
            count++;

            progress.style.width = `${Math.trunc(x)}px`;
          }
        }

        // Desaparecer o balão de mensagem
        setTimeout(() => {
          msg.style.right = "-400px";
        }, 4700);
      }
      document.addEventListener("DOMContentLoaded", message);
    </script>
    
    <?php unset($_SESSION["flash_message"]); ?>
  <?php endif; ?>

  <script src="/JS/globalScripts.js" defer></script>
  <script src="/JS/<?= $view ?>ViewScripts.js" defer></script>
</body>
</html>