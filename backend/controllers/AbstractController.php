<?php

namespace Controller;

use DI\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Throwable;

use function sprintf;

abstract class AbstractController
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    protected function get(string $name): mixed
    {
        if (!$this->container->has($name)) {
            throw new NotFoundException(
                sprintf('Service "%s" is not registered', $name)
            );
        }

        return $this->container->get($name);
    }

    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    protected function badRequestException(
        ServerRequestInterface $request,
        string $message,
        Throwable $previous = null
    ): HttpBadRequestException {
        return new HttpBadRequestException($request, $message, $previous);
    }

    protected function unauthorizedException(
        ServerRequestInterface $request,
        string $message,
        Throwable $previous = null
    ): HttpUnauthorizedException {
        return new HttpUnauthorizedException($request, $message, $previous);
    }

    protected function forbiddenException(
        ServerRequestInterface $request,
        string $message,
        Throwable $previous = null
    ): HttpForbiddenException {
        return new HttpForbiddenException($request, $message, $previous);
    }

    protected function notFoundException(
        ServerRequestInterface $request,
        string $message,
        Throwable $previous = null
    ): HttpNotFoundException {
        return new HttpNotFoundException($request, $message, $previous);
    }

    protected function internalErrorException(
        ServerRequestInterface $request,
        string $message,
        Throwable $previous = null
    ): HttpInternalServerErrorException {
        return new HttpInternalServerErrorException($request, $message, $previous);
    }
}
