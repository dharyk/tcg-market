<?php

namespace TcgMarket\Tests\Middleware;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpTooManyRequestsException;
use TcgMarket\Handler\CacheHandler;
use TcgMarket\Middleware\RateLimiterMiddleware;

use function ip2long;

class RateLimiterMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    private $cacheHandler;
    private $requestHandler;
    private $request;

    public function testMiddlewarePass(): void
    {
        $ip = '111.0.0.222';
        $cacheKey = sprintf('api-rate-limit-%d', ip2long($ip));

        $response = $this->prophesize(Response::class);
        $response
            ->withHeader(
                Argument::exact('X-Rate-Limit-Reset'),
                Argument::type('int'),
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );
        $response
            ->withHeader(
                Argument::exact('X-Rate-Limit-Remaining'),
                Argument::type('int'),
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );
        $response
            ->withHeader(
                Argument::exact('X-Rate-Limit-Limit'),
                Argument::type('int'),
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );

        $this->request
            ->getServerParams()
            ->shouldBeCalled()
            ->willReturn([
                'HTTP_CLIENT_IP' => $ip,
            ]);

        $this->cacheHandler
            ->has(
                Argument::exact($cacheKey)
            )
            ->shouldBeCalled()
            ->willReturn(false);
        $this->cacheHandler
            ->set(
                Argument::exact($cacheKey),
                Argument::type('array'),
                Argument::type('int')
            )
            ->shouldBeCalled()
            ->willReturn(true);

        $this->requestHandler
            ->handle(
                Argument::type(Request::class)
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );

        $middleware = new RateLimiterMiddleware(
            $this->cacheHandler->reveal()
        );
        $middleware(
            $this->request->reveal(),
            $this->requestHandler->reveal()
        );
    }

    public function testMiddlewareBlock(): void
    {
        $ip = '111.0.0.222';
        $cacheKey = sprintf('api-rate-limit-%d', ip2long($ip));
        $rateData = [
            'limit' => 30,
            'remaining' => 0,
            'since' => (new DateTimeImmutable())->getTimestamp(),
        ];

        $this->request
            ->getServerParams()
            ->shouldBeCalled()
            ->willReturn([
                'HTTP_CLIENT_IP' => $ip,
            ]);

        $this->cacheHandler
            ->has(
                Argument::exact($cacheKey)
            )
            ->shouldBeCalled()
            ->willReturn(true);
        $this->cacheHandler
            ->get(
                Argument::exact($cacheKey)
            )
            ->shouldBeCalled()
            ->willReturn($rateData);

        $this->requestHandler
            ->handle(
                Argument::type(Request::class)
            )
            ->shouldNotBeCalled();

        $this->expectException(HttpTooManyRequestsException::class);

        $middleware = new RateLimiterMiddleware(
            $this->cacheHandler->reveal()
        );
        $middleware(
            $this->request->reveal(),
            $this->requestHandler->reveal()
        );
    }

    protected function setUp(): void
    {
        $this->cacheHandler = $this->prophesize(CacheHandler::class);
        $this->requestHandler = $this->prophesize(RequestHandler::class);
        $this->request = $this->prophesize(Request::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->cacheHandler,
            $this->requestHandler,
            $this->request
        );
    }
}
