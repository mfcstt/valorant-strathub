<?php

declare(strict_types=1);

/**
 * Inicialização da aplicação: autoload, sessão, tratamento de erro e roteamento.
 *
 * Este é o único lugar que sabe a ordem em que as coisas sobem. Antes, o
 * `public/index.php` fazia nove `require` manuais de models e helpers, e a
 * ordem entre eles importava de formas não documentadas.
 */

use App\Support\Config;
use App\Support\Database;
use App\Support\DatabaseSessionHandler;
use App\Support\Router;
use App\Support\View;

require dirname(__DIR__) . '/vendor/autoload.php';

// -----------------------------------------------------------------------------
// Relatório de erros
// -----------------------------------------------------------------------------
// Em produção nada de detalhe técnico vai para a tela: o stack trace revela
// caminhos, versões e trechos de consulta. Vai para o log, onde é útil sem ser
// exposto.
$isDebug = Config::isDebug() && !Config::isProduction();

error_reporting(E_ALL);
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');

// -----------------------------------------------------------------------------
// Sessão
// -----------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $secure = Config::isProduction()
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        // Impede que JavaScript leia o cookie de sessão, o que limita o dano de
        // um eventual XSS: o script não consegue exfiltrar a sessão.
        'httponly' => true,
        // 'Lax' permite o cookie em navegação normal entre sites, mas não em
        // POST cross-site — uma segunda linha de defesa junto do token CSRF.
        'samesite' => 'Lax',
    ]);

    // Recusa ids de sessão que o servidor não gerou, fechando a porta para
    // fixação de sessão por link com id plantado.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    // Sessão no banco, não em arquivo — ver DatabaseSessionHandler para o porquê.
    // register_shutdown_function garante que a escrita aconteça antes do PHP
    // começar a destruir objetos no fim do script, incluindo a própria conexão
    // com o banco de que o handler depende.
    session_set_save_handler(new DatabaseSessionHandler(Database::connection()), true);
    register_shutdown_function('session_write_close');

    session_name('strathub_session');
    session_start();
}

// -----------------------------------------------------------------------------
// Cabeçalhos de segurança
// -----------------------------------------------------------------------------
if (!headers_sent()) {
    // Impede que o navegador "adivinhe" um tipo diferente do declarado — o vetor
    // clássico de tratar um upload como HTML e executá-lo.
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

View::setBasePath(__DIR__ . '/Views');

// -----------------------------------------------------------------------------
// Rede de segurança para exceções não tratadas
// -----------------------------------------------------------------------------
set_exception_handler(static function (Throwable $e) use ($isDebug): void {
    error_log(sprintf(
        '[strathub] %s: %s em %s:%d',
        $e::class,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
    ));

    if ($isDebug) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo $e, PHP_EOL;

        return;
    }

    abort(500, 'Algo deu errado do nosso lado. Tente novamente em instantes.');
});

Router::dispatch(__DIR__ . '/Controllers');
