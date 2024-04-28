<?php

namespace TcgMarket\Handler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Handlers\ErrorHandler;

/**
 * Handler responsible for managing API error responses
 */
class ApiErrorHandler extends ErrorHandler
{
    private const CONTENT_TYPE = 'application/json';

    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);

        $this->forceContentType(self::CONTENT_TYPE);
        $this->setDefaultErrorRenderer(self::CONTENT_TYPE, JsonErrorRenderer::class);
    }
}
