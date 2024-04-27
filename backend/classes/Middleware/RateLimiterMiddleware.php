<?php

namespace TcgMarket\Middleware;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\SimpleCache\CacheInterface;
use Slim\Exception\HttpTooManyRequestsException;

use function array_key_exists;
use function explode;
use function filter_var;
use function ip2long;
use function trim;

use const FILTER_VALIDATE_IP;
use const FILTER_FLAG_NO_RES_RANGE;

class RateLimiterMiddleware
{
    private const PREFIX = 'api-rate-limit-';
    private const TTL = 60;
    private const PARAM_KEYS = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $cacheKey = sprintf('%s%d', self::PREFIX, $this->resolveRemoteIp($request));
        if ($this->cache->has($cacheKey)) {
            $rateData = $this->cache->get($cacheKey);
            $rateData['remaining'] -= 1;
        } else {
            $rateData = [
                'limit' => 30,
                'remaining' => 30,
                'since' => (new DateTimeImmutable())->getTimestamp(),
            ];
        }

        if ($rateData['remaining'] < 0) {
            $after = DateTimeImmutable::createFromFormat('U', (string) ($rateData['since'] + self::TTL));
            throw new HttpTooManyRequestsException(
                $request,
                sprintf(
                    'Retry-After: %s',
                    false !== $after ? $after->format(DateTimeImmutable::W3C) : self::TTL
                )
            );
        }

        $this->cache->set($cacheKey, $rateData, self::TTL);
        $response = $handler->handle($request);

        return $response
            ->withHeader('X-Rate-Limit-Limit', (string) $rateData['limit'])
            ->withHeader('X-Rate-Limit-Remaining', (string) $rateData['remaining'])
            ->withHeader('X-Rate-Limit-Reset', (string) ($rateData['since'] + self::TTL));
    }

    private function resolveRemoteIp(Request $request): int
    {
        $params = $request->getServerParams();

        foreach (self::PARAM_KEYS as $key) {
            if (array_key_exists($key, $params)) {
                foreach (explode(',', $params[$key]) as $param) {
                    $ip = filter_var(trim($param), FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);

                    if (!empty($ip)) {
                        return (int) ip2long($ip);
                    }
                }
            }
        }

        return 0;
    }
}
