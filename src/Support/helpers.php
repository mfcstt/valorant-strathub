<?php

declare(strict_types=1);

use App\Support\Auth;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Url;
use App\Support\View;

/**
 * Funções globais de conveniência, carregadas pelo autoload do Composer.
 */

if (!function_exists('e')) {
    /**
     * Escapa um valor para HTML.
     *
     * Toda interpolação numa view passa por aqui. Nomeada com uma única letra
     * de propósito: quanto mais barata de escrever, menor a chance de alguém
     * pular o escape "só nesse caso".
     */
    function e(mixed $value): string
    {
        return View::escape($value);
    }
}

if (!function_exists('view')) {
    /**
     * @param array<string, mixed> $data
     */
    function view(string $view, array $data = [], ?string $component = null): void
    {
        View::render($view, $data, $component);
    }
}

if (!function_exists('input')) {
    /**
     * Renderiza um campo de formulário com ícone e mensagens de erro.
     *
     * @param string      $form nome do formulário, quando a página tem mais de um
     *                          (usado para separar os erros de cada um)
     */
    function input(
        string $type,
        string $name,
        string $placeholder,
        string $iconClass,
        ?string $form = null,
    ): void {
        require View::basePath() . '/partials/_input.php';
    }
}

if (!function_exists('field_errors')) {
    /**
     * Desenha a lista de erros de um campo.
     *
     * Este bloco de markup aparecia repetido oito vezes só no formulário de
     * criação de estratégia.
     *
     * @param array<string, list<string>> $errors
     * @param 'start'|'center'            $align
     */
    function field_errors(array $errors, string $field, string $align = 'start'): void
    {
        $messages = $errors[$field] ?? [];

        if ($messages === []) {
            return;
        }

        $justify = $align === 'center' ? 'justify-center' : '';

        echo '<ul class="mt-2 ml-1 flex flex-wrap gap-x-3 ' . $justify . '">';

        foreach ($messages as $message) {
            printf(
                '<li class="flex gap-1.5 items-center text-start text-error-light">'
                . '<i class="ph ph-warning text-base" aria-hidden="true"></i>'
                . '<span class="text-xs mt-[2px]">%s</span></li>',
                e($message),
            );
        }

        echo '</ul>';
    }
}

if (!function_exists('strategy_card')) {
    /**
     * Renderiza o card de uma estratégia nas listagens.
     */
    function strategy_card(\App\Models\Strategy $strategy): void
    {
        require View::basePath() . '/partials/_strategy-card.php';
    }
}

if (!function_exists('flash')) {
    function flash(): Flash
    {
        static $flash = null;

        return $flash ??= new Flash();
    }
}

if (!function_exists('auth')) {
    /**
     * Usuário autenticado, ou null.
     */
    function auth(): ?stdClass
    {
        return Auth::user();
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Campo oculto com o token CSRF, para inserir em todo formulário POST.
     */
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('old')) {
    /**
     * Valor previamente submetido, para repopular o formulário após erro.
     *
     * Lê sem consumir: a mesma requisição costuma renderizar vários campos.
     */
    function old(string $field, string $default = ''): string
    {
        $data = flash()->peek('formData');

        if (!is_array($data) || !array_key_exists($field, $data)) {
            return $default;
        }

        $value = $data[$field];

        return is_scalar($value) ? (string) $value : $default;
    }
}

if (!function_exists('abort')) {
    /**
     * Interrompe a requisição com uma página de erro.
     */
    function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        View::render('error', ['code' => $code, 'message' => $message]);
        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redireciona e encerra a requisição.
     */
    function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('redirect_back')) {
    /**
     * Volta para uma rota interna informada pelo formulário.
     *
     * A decisão sobre o destino ser seguro vive em {@see Url::safeInternalPath()},
     * separada do efeito colateral de redirecionar — é o que permite testá-la.
     */
    function redirect_back(?string $candidate, string $fallback = '/explore'): never
    {
        redirect(Url::safeInternalPath($candidate, $fallback));
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('query_string')) {
    /**
     * Reconstrói a query string atual com sobrescritas.
     *
     * @param array<string, string|int|null> $overrides valores null removem a chave
     */
    function query_string(array $overrides = []): string
    {
        $params = $_GET;
        unset($params['path']);

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($params[$key]);
            } else {
                $params[$key] = (string) $value;
            }
        }

        return http_build_query($params);
    }
}
