<?php

namespace TcgMarket\Middleware;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use function abs;
use function array_merge;
use function is_string;
use function session_cache_limiter;
use function session_name;
use function session_set_cookie_params;
use function session_set_save_handler;
use function session_start;
use function session_status;
use function setcookie;
use function time;

use const PHP_SESSION_NONE;

/**
 * Middleware responsible for handling user sessions
 *
 * @codeCoverageIgnore
 */
class SessionMiddleware
{
    /** @var array<string, mixed> $settings */
    private array $settings;

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings)
    {
        $defaults = [
            'lifetime' => '1 hour',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'name' => '',
            'autoRefresh' => false,
            'handler' => null,
            'iniSettings' => [],
        ];
        $settings = array_merge($defaults, $settings);
        if (is_string($settings['lifetime'])) {
            $settings['lifetime'] = $this->lifetimeInSeconds($settings['lifetime']);
        }

        $this->settings = $settings;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->startSession();

        return $handler->handle($request);
    }

    private function startSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_cookie_params(
            $this->settings['lifetime'],
            $this->settings['path'],
            $this->settings['domain'],
            $this->settings['secure'],
            $this->settings['httponly']
        );
        $name = $this->settings['name'];

        // Refresh cookie when inactive
        if (
            $this->settings['autoRefresh']
            && isset($_COOKIE[$name])
        ) {
            setcookie(
                $name,
                $_COOKIE[$name],
                time() + $this->settings['lifetime'],
                $this->settings['path'],
                $this->settings['domain'],
                $this->settings['secure'],
                $this->settings['httpOnly']
            );
        }
        session_name($name);

        /*
        $handler = $this->settings['handler'];
        if (null !== $handler) {
            if (!$handler instanceof \SessionHandlerInterface) {
                $handler = new $handler();
            }

            session_set_save_handler($handler, true);
        }
        */

        session_cache_limiter('nocache');
        session_start();
    }

    private function lifetimeInSeconds(string $lifetime): int
    {
        $now = new DateTimeImmutable();
        $ttl = new DateTimeImmutable($lifetime);

        return (int) abs($now->getTimestamp() - $ttl->getTimestamp());
    }
}
