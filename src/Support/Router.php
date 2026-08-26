<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Roteador com lista explícita de rotas.
 *
 * A versão anterior montava o caminho do arquivo a partir da URL
 * (`require "src/controllers/{$path}.controller.php"`), o que deixava o
 * filesystem navegável pela query string. Aqui só existem as rotas declaradas
 * abaixo: qualquer outra coisa é 404 antes de tocar no disco.
 */
final class Router
{
    /**
     * Mapa de rota => arquivo de controller, e os métodos HTTP aceitos.
     *
     * @var array<string, array{file: string, methods: list<string>}>
     */
    private const ROUTES = [
        'login' => ['file' => 'login', 'methods' => ['GET', 'POST']],
        'register' => ['file' => 'register', 'methods' => ['POST']],
        // Logout só por POST: um `GET /logout` pode ser disparado por qualquer
        // <img> em site de terceiros, derrubando a sessão da pessoa sem que ela
        // tenha pedido. Por POST, a verificação de CSRF cobre esse caso.
        'logout' => ['file' => 'logout', 'methods' => ['POST']],
        'guest' => ['file' => 'guest', 'methods' => ['GET']],
        'explore' => ['file' => 'explore', 'methods' => ['GET']],
        'strategy' => ['file' => 'strategy', 'methods' => ['GET']],
        'strategy-create' => ['file' => 'strategy-create', 'methods' => ['GET', 'POST']],
        'strategy-delete' => ['file' => 'strategy-delete', 'methods' => ['POST']],
        'my-strategies' => ['file' => 'my-strategies', 'methods' => ['GET']],
        'favorites' => ['file' => 'favorites', 'methods' => ['GET']],
        'favorite-toggle' => ['file' => 'favorite-toggle', 'methods' => ['POST']],
        'rating-create' => ['file' => 'rating-create', 'methods' => ['POST']],
        'profile' => ['file' => 'profile', 'methods' => ['GET', 'POST']],
        'strategy-edit' => ['file' => 'strategy-edit', 'methods' => ['GET', 'POST']],
        'admin/moderacao' => ['file' => 'admin-moderation', 'methods' => ['GET']],
        'admin/moderar' => ['file' => 'admin-moderate', 'methods' => ['POST']],
    ];

    /**
     * Rotas antigas mantidas por compatibilidade: links já compartilhados
     * continuam funcionando, com redirecionamento permanente para a nova URL.
     *
     * @var array<string, string>
     */
    private const REDIRECTS = [
        'myStrategy' => 'my-strategies',
    ];

    private const DEFAULT_ROUTE = 'explore';

    /**
     * Resolve a requisição atual e executa o controller correspondente.
     */
    public static function dispatch(string $controllersPath): void
    {
        $route = self::currentRoute();

        if ($route === '') {
            $route = Auth::check() || Auth::isGuest() ? self::DEFAULT_ROUTE : 'login';
        }

        if (isset(self::REDIRECTS[$route])) {
            self::redirectPermanently('/' . self::REDIRECTS[$route]);

            return;
        }

        if (!isset(self::ROUTES[$route])) {
            abort(404, 'Página não encontrada.');
        }

        $definition = self::ROUTES[$route];
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!in_array($method, $definition['methods'], true)) {
            header('Allow: ' . implode(', ', $definition['methods']));
            abort(405, 'Método não permitido para esta página.');
        }

        // Toda escrita passa pela verificação de CSRF num único ponto, em vez de
        // depender de cada controller lembrar de fazê-la.
        if ($method === 'POST') {
            Csrf::check();
        }

        require $controllersPath . '/' . $definition['file'] . '.php';
    }

    /**
     * Caminho pedido, normalizado e sem barras nas pontas.
     *
     * Em produção a Vercel reescreve tudo para `?path=...`; localmente vem de
     * `REQUEST_URI`. Os dois casos convergem aqui.
     */
    public static function currentRoute(): string
    {
        $raw = $_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';

        return trim((string) $raw, '/');
    }

    /**
     * @return list<string>
     */
    public static function routeNames(): array
    {
        return array_keys(self::ROUTES);
    }

    private static function redirectPermanently(string $location): void
    {
        $query = $_GET;
        unset($query['path']);

        if ($query !== []) {
            $location .= '?' . http_build_query($query);
        }

        header('Location: ' . $location, true, 301);
    }
}
