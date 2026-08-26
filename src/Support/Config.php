<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Carrega o arquivo `.env` e expõe a configuração da aplicação.
 *
 * As variáveis de ambiente reais (definidas pelo host, como Vercel ou Render)
 * têm precedência sobre o `.env`, que serve ao desenvolvimento local.
 */
final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    private static bool $envLoaded = false;

    /**
     * Lê um valor de configuração usando notação de ponto: `database.driver`.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = self::all();

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$config === null) {
            self::loadEnv();
            self::$config = self::build();
        }

        return self::$config;
    }

    /**
     * Lê uma variável de ambiente, preferindo o ambiente real ao `.env`.
     */
    public static function env(string $key, ?string $default = null): ?string
    {
        self::loadEnv();

        // getenv() devolve false quando a variável não existe; o operador ??
        // já cobriu $_SERVER e $_ENV ausentes.
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);

        if ($value === false || !is_scalar($value) || (string) $value === '') {
            return $default;
        }

        return (string) $value;
    }

    public static function isProduction(): bool
    {
        return self::env('APP_ENV', 'local') === 'production';
    }

    public static function isDebug(): bool
    {
        return self::env('APP_DEBUG', 'false') === 'true';
    }

    /**
     * Segredo da aplicação, usado para derivar e comparar tokens.
     *
     * Em produção a ausência de um segredo forte é um erro fatal: seguir com um
     * valor padrão tornaria todos os tokens previsíveis por qualquer pessoa que
     * leia o código-fonte.
     */
    public static function appSecret(): string
    {
        $secret = self::env('APP_SECRET');

        if ($secret === null || strlen($secret) < 32) {
            if (self::isProduction()) {
                throw new RuntimeException(
                    'APP_SECRET ausente ou curto demais. Gere um com: '
                    . 'php -r "echo bin2hex(random_bytes(32));"'
                );
            }

            // Em desenvolvimento, deriva um valor estável por instalação a partir
            // do caminho do projeto, para não exigir configuração só para rodar.
            return hash('sha256', 'strathub-dev:' . __DIR__);
        }

        return $secret;
    }

    /**
     * Reinicia o cache - usado pelos testes.
     */
    public static function flush(): void
    {
        self::$config = null;
        self::$envLoaded = false;
    }

    private static function loadEnv(): void
    {
        if (self::$envLoaded) {
            return;
        }
        self::$envLoaded = true;

        $path = dirname(__DIR__, 2) . '/.env';
        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = self::unquote(trim($value));

            // Não sobrescreve variáveis já definidas pelo ambiente do host.
            if (getenv($key) === false && !isset($_SERVER[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }

    private static function unquote(string $value): string
    {
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private static function build(): array
    {
        $useSqlite = self::env('USE_SQLITE', 'false') === 'true';

        return [
            'app' => [
                'env' => self::env('APP_ENV', 'local'),
                'debug' => self::isDebug(),
            ],
            'database' => $useSqlite
                ? [
                    'driver' => 'sqlite',
                    'database' => dirname(__DIR__, 2) . '/database/strathub.sqlite',
                ]
                : [
                    'driver' => 'pgsql',
                    'host' => self::env('DB_HOST', 'localhost'),
                    'port' => self::env('DB_PORT', '5432'),
                    'dbname' => self::env('DB_NAME', 'postgres'),
                    'user' => self::env('DB_USER', 'postgres'),
                    'password' => self::env('DB_PASSWORD', ''),
                    'sslmode' => self::env('DB_SSLMODE', 'require'),
                ],
            'storage' => self::buildStorage(),
        ];
    }

    /**
     * Configuração de armazenamento de mídia.
     *
     * O driver `local` grava em `public/uploads` e existe para o projeto rodar
     * ponta a ponta sem nenhuma conta externa - quem clona o repositório
     * consegue criar uma estratégia com imagem já na primeira execução. O driver
     * é escolhido automaticamente: se não houver credenciais do Supabase, cai
     * para o local em vez de falhar no meio de um upload.
     *
     * @return array<string, mixed>
     */
    private static function buildStorage(): array
    {
        $supabaseUrl = rtrim(self::env('SUPABASE_URL', '') ?? '', '/');
        $supabaseKey = self::env('SUPABASE_SERVICE_KEY') ?? self::env('SUPABASE_ANON_KEY', '');

        $imageBucket = 'strategy-covers';
        $videoBucket = 'strategy-videos';

        $requested = self::env('STORAGE_DRIVER');
        $canUseSupabase = $supabaseUrl !== '' && ($supabaseKey ?? '') !== '';

        $driver = match (true) {
            $requested === 'local' => 'local',
            $requested === 'supabase' => 'supabase',
            $canUseSupabase => 'supabase',
            default => 'local',
        };

        if ($driver === 'supabase') {
            $publicBase = "{$supabaseUrl}/storage/v1/object/public";
        } else {
            $publicBase = '/uploads';
        }

        return [
            'driver' => $driver,
            'url' => $supabaseUrl,
            'service_key' => $supabaseKey ?? '',
            'image_bucket' => $imageBucket,
            'video_bucket' => $videoBucket,
            'local_path' => dirname(__DIR__, 2) . '/public/uploads',
            // Prefixos usados tanto pelas consultas que montam a URL da capa
            // quanto por MediaFile::publicUrl(), para não haver duas fórmulas.
            'image_prefix' => "{$publicBase}/{$imageBucket}/",
            'video_prefix' => "{$publicBase}/{$videoBucket}/",
        ];
    }
}
