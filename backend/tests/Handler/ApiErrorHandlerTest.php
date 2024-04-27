<?php

namespace TcgMarket\Tests\Handler;

use Throwable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Interfaces\CallableResolverInterface;
use TcgMarket\Handler\ApiErrorHandler;

class ApiErrorHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandler(): void
    {
        $resolveFn = function (Throwable $t, bool $log) {
            return '';
        };

        // Resolver mock
        $resolver = $this->prophesize(CallableResolverInterface::class);
        $resolver
            ->resolve(
                Argument::any()
            )
            ->shouldBeCalled()
            ->willReturn($resolveFn);

        // Response body mock
        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody
            ->write(
                Argument::type('string')
            )
            ->shouldBeCalled();

        // Response mock
        $response = $this->prophesize(ResponseInterface::class);
        $response
            ->getBody()
            ->shouldBeCalled()
            ->willReturn(
                $responseBody->reveal()
            );
        $response
            ->withHeader(
                Argument::exact('Content-type'),
                Argument::exact('application/json')
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );

        // Response factory mock
        $factory = $this->prophesize(ResponseFactoryInterface::class);
        $factory
            ->createResponse(
                Argument::type('int')
            )
            ->shouldBeCalled()
            ->willReturn(
                $response->reveal()
            );

        // Logger mock
        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->error(
                Argument::type('string')
            )
            ->shouldBeCalled();

        // Request mock
        $request = $this->prophesize(ServerRequestInterface::class);
        $request
            ->getHeaderLine(
                Argument::exact('Accept')
            )
            ->willReturn('application/json');
        $request
            ->getMethod()
            ->shouldBeCalled()
            ->willReturn('POST');

        // Exception
        $exception = new HttpException(
            $request->reveal(),
            'Test exception message',
            123
        );

        // Instantiate handler
        $handler = new ApiErrorHandler(
            $resolver->reveal(),
            $factory->reveal(),
            $logger->reveal()
        );
        $response = $handler(
            $request->reveal(),
            $exception,
            true,
            true,
            true
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
