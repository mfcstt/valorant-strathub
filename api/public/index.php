<?php 

// Fallback para servir arquivos estáticos via front controller (quando Vercel não reconhece o diretório público).
$requestedPath = $_GET['path'] ?? (parse_url($_SERVER['REQUEST_URI'])['path'] ?? '');
$requestedPath = ltrim($requestedPath, '/');

// Permitir apenas subpastas conhecidas para evitar traversal
$allowedPrefixes = ['CSS/', 'JS/', 'assets/', 'fonts/'];
foreach ($allowedPrefixes as $prefix) {
    if (strpos($requestedPath, $prefix) === 0) {
        $file = __DIR__ . '/' . $requestedPath;
        if (is_file($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $types = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
            ];
            $contentType = $types[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            header('Cache-Control: public, max-age=31536000');
            readfile($file);
            exit;
        }
        break; // prefixo conhecido mas arquivo não existe -> continua fluxo normal e cairá em 404 da app
    }
}

// App dinâmica
require __DIR__ . "/../src/models/User.php";
require __DIR__ . "/../src/models/Estrategia.php";
require __DIR__ . "/../src/models/Agent.php";
require __DIR__ . "/../src/models/Map.php";
require __DIR__ . "/../src/models/Rating.php";
require __DIR__ . "/../src/models/Image.php";
require __DIR__ . "/../src/models/Video.php";
require __DIR__ . "/../src/services/SupabaseStorageService.php";

session_start();

require __DIR__ . "/../Flash.php";
require __DIR__ . "/../functions.php";
require __DIR__ . "/../Validation.php";
require __DIR__ . "/../database.php"; // atenção à caixa baixa em ambiente Linux

// Criar instância global do banco de dados
$database = new Database(config('database')['database']);

require __DIR__ . "/../routes.php";