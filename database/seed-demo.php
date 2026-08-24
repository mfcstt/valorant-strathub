<?php

declare(strict_types=1);

/**
 * Popula o banco com conteúdo de demonstração.
 *
 *   php database/seed-demo.php
 *
 * Serve para ver a aplicação com dados plausíveis logo depois de clonar, e para
 * gerar as capturas de tela do README. Idempotente: rodar de novo não duplica.
 *
 * As imagens de capa saem de public/assets/images/covers e são copiadas para o
 * diretório do driver de storage local.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Models\Agent;
use App\Models\Image;
use App\Models\Map;
use App\Models\Rating;
use App\Models\Strategy;
use App\Models\User;
use App\Support\Config;
use App\Support\Database;

if (PHP_SAPI !== 'cli') {
    exit("Este script só roda via CLI.\n");
}

if (Config::isProduction()) {
    exit("Recusando rodar com APP_ENV=production.\n");
}

$database = Database::connection();

/**
 * Cria o usuário se ainda não existir.
 */
$ensureUser = static function (string $name, string $email, string $elo): User {
    $existing = User::findByEmail($email);
    if ($existing !== null) {
        return $existing;
    }

    $user = User::create($name, $email, 'Demo#strathub1', $elo);
    if ($user === null) {
        exit("Falha ao criar o usuário {$email}.\n");
    }

    echo "  usuário: {$name} <{$email}>\n";

    return $user;
};

/**
 * Copia uma capa dos assets para o storage local e registra em `images`.
 */
$ensureCover = static function (string $assetName, int $userId): ?int {
    $source = dirname(__DIR__) . '/public/assets/images/covers/' . $assetName;
    if (!is_file($source)) {
        return null;
    }

    $extension = pathinfo($assetName, PATHINFO_EXTENSION);
    $filename = 'demo_' . pathinfo($assetName, PATHINFO_FILENAME) . '.' . $extension;
    $relative = "user_{$userId}/{$filename}";

    $bucket = (string) Config::get('storage.image_bucket');
    $target = Config::get('storage.local_path') . "/{$bucket}/{$relative}";

    $directory = dirname($target);
    if (!is_dir($directory)) {
        mkdir($directory, 0o775, true);
    }

    if (!is_file($target)) {
        copy($source, $target);
    }

    $image = new Image();
    $image->filename = $filename;
    $image->original_name = $assetName;
    $image->file_path = $relative;
    $image->file_size = (int) filesize($source);
    $image->mime_type = match (strtolower($extension)) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        default => 'image/jpeg',
    };
    $image->user_id = $userId;
    $image->save();

    return (int) $image->id;
};

echo "Semeando dados de demonstração...\n";

// Agentes e mapas já vêm de database/seeds.sql.
$agents = [];
foreach (Agent::all() as $agent) {
    $agents[(string) $agent->name] = (int) $agent->id;
}

$maps = [];
foreach (Map::all() as $map) {
    $maps[(string) $map->name] = (int) $map->id;
}

if ($agents === [] || $maps === []) {
    exit("Agentes e mapas não encontrados. Aplique database/seeds.sql primeiro.\n");
}

$autora = $ensureUser('Marina Costa', 'marina@strathub.demo', 'imortal');
$tatico = $ensureUser('Rafael Lima', 'rafael@strathub.demo', 'ascendente');
$novato = $ensureUser('Bia Nunes', 'bia@strathub.demo', 'ouro');

$covers = array_values(array_filter(
    scandir(dirname(__DIR__) . '/public/assets/images/covers') ?: [],
    static fn (string $f): bool => preg_match('/\.(png|jpe?g|webp|avif)$/i', $f) === 1,
));

/** @var list<array{title: string, category: string, agent: string, map: string, author: User, description: string}> */
$strategies = [
    [
        'title' => 'Smoke duplo travando o meio de Ascent',
        'category' => 'ataque',
        'agent' => 'Omen',
        'map' => 'Ascent',
        'author' => $autora,
        'description' => "Duas fumaças na entrada do meio, uma no Market e outra no arco. "
            . "Fecha a visão da defesa e libera a passagem para o time inteiro rotacionar "
            . "para o B sem tomar tiro de longe. O timing importa: jogue a segunda fumaça "
            . "só depois que o duelista já estiver no encaixe.",
    ],
    [
        'title' => 'Line-up de Molly pós-plant no A de Bind',
        'category' => 'pós plant',
        'agent' => 'Brimstone',
        'map' => 'Bind',
        'author' => $tatico,
        'description' => "Posicione-se na quina da caixa perto do Lamps e alinhe a mira com "
            . "o canto superior da porta. A molotov cai exatamente sobre o default do spike "
            . "e impede o defuse sem que você precise dar a cara. Dá tempo de jogar duas "
            . "seguidas se o time segurar as entradas.",
    ],
    [
        'title' => 'Retake do B em Haven com Sova e Sage',
        'category' => 'retake',
        'agent' => 'Sova',
        'map' => 'Haven',
        'author' => $autora,
        'description' => "A flecha de reconhecimento entra pelo teto do B e revela quem está "
            . "no fundo. Assim que ela pinga, a Sage sobe a parede cortando o site ao meio, "
            . "e o retake vira um 3 contra 2 em vez de um 3 contra 4. Funciona melhor quando "
            . "sobra ultimate da Sova para confirmar as posições.",
    ],
    [
        'title' => 'Defesa agressiva do meio em Split com Cypher',
        'category' => 'defesa',
        'agent' => 'Cypher',
        'map' => 'Split',
        'author' => $tatico,
        'description' => "Câmera na parede alta do meio olhando para a rampa, e uma armadilha "
            . "no vão logo abaixo. A informação chega cedo o suficiente para o time decidir "
            . "se rotaciona ou se segura. O objetivo aqui não é matar: é fazer o ataque gastar "
            . "utilidade antes de escolher o site.",
    ],
    [
        'title' => 'Entrada rápida no A de Lotus com Raze',
        'category' => 'ataque',
        'agent' => 'Raze',
        'map' => 'Lotus',
        'author' => $novato,
        'description' => "Satchel duplo pela porta giratória caindo direto atrás da caixa. "
            . "Pega quem está segurando o ângulo padrão de surpresa e abre espaço para o resto "
            . "do time entrar sem trocar tiro de frente. Combine com uma fumaça no fundo para "
            . "cortar o apoio do meio.",
    ],
    [
        'title' => 'Muro da Sage cortando o Heaven de Fracture',
        'category' => 'defesa',
        'agent' => 'Sage',
        'map' => 'Fracture',
        'author' => $novato,
        'description' => "O muro sobe atravessado na entrada do Heaven e obriga o ataque a "
            . "escolher entre quebrar a parede (fazendo barulho e perdendo tempo) ou dar a volta. "
            . "Nos dois casos a defesa ganha os segundos que faltavam para a rotação chegar.",
    ],
];

$created = 0;

foreach ($strategies as $index => $definition) {
    $existing = $database->scalar(
        'SELECT id FROM strategies WHERE title = :title',
        ['title' => $definition['title']],
    );

    if ($existing !== false) {
        continue;
    }

    $authorId = (int) $definition['author']->id;
    $coverAsset = $covers[$index % max(1, count($covers))] ?? null;

    $strategyId = Strategy::create([
        'title' => $definition['title'],
        'category' => $definition['category'],
        'description' => $definition['description'],
        'cover_image_id' => $coverAsset !== null ? $ensureCover($coverAsset, $authorId) : null,
        'video_id' => null,
        'user_id' => $authorId,
        'agent_id' => $agents[$definition['agent']] ?? null,
        'map_id' => $maps[$definition['map']] ?? null,
    ]);

    // Datas escalonadas para a ordenação por "Mais recentes" ter o que mostrar.
    $database->execute(
        'UPDATE strategies SET created_at = :created_at WHERE id = :id',
        [
            'created_at' => date('Y-m-d H:i:s', strtotime("-" . (count($strategies) - $index) . " days")),
            'id' => $strategyId,
        ],
    );

    echo "  estratégia: {$definition['title']}\n";
    $created++;

    // Avaliações de quem não é o autor, respeitando a regra da aplicação.
    $comments = [
        [4, 'Testei em ranqueada e funcionou bem. O timing da segunda utilidade é o que pega.'],
        [5, 'Virou padrão no meu time. Simples de executar e difícil de responder.'],
        [3, 'Boa ideia, mas depende muito do time acompanhar a entrada.'],
    ];

    $raters = array_values(array_filter(
        [$autora, $tatico, $novato],
        static fn (User $u): bool => (int) $u->id !== $authorId,
    ));

    foreach ($raters as $position => $rater) {
        if ($position >= 2) {
            break;
        }

        [$score, $comment] = $comments[($index + $position) % count($comments)];
        Rating::upsert((int) $rater->id, $strategyId, $score, $comment);
    }
}

printf(
    "\nPronto. %d estratégia(s) criada(s).\nUsuários de demonstração (senha: Demo#strathub1):\n",
    $created,
);

foreach (['marina@strathub.demo', 'rafael@strathub.demo', 'bia@strathub.demo'] as $email) {
    echo "  {$email}\n";
}
