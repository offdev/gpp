<?php
/**
 * The Offdev Project
 *
 * Offdev/Gpp - a wrapper around guzzle that provides middleware functionality for responses,
 *              URL enumeration capabilities, and a crawler to make use of it
 *
 * @author      Pascal Severin <pascal@offdev.net>
 * @copyright   Copyright (c) 2018, Pascal Severin
 * @license     Apache License 2.0
 */

namespace Offdev\Gpp;

use GuzzleHttp\ClientInterface;
use Offdev\Gpp\Http\Exceptions\InvalidArgumentException;
use Offdev\Gpp\Http\ResponseHandlerInterface;
use Offdev\Gpp\Http\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A Guzzle wrapper
 *
 * This client is a wrapper for Guzzle, which provides a middleware
 * functionality for responses received from a given request.
 *
 * Class Client
 * @package Offdev\Gpp
 */
class Client implements ResponseHandlerInterface
{
    /** @var \GuzzleHttp\Client */
    private $client;

    /** @var array */
    private $middlewares;

    /** @var bool  */
    private $hasBegunProcessing = false;

    /** @var RequestInterface */
    private $originalRequest;

    /**
     * Wraps around Guzzle, accepts a list of middleware classes
     *
     * @param ClientInterface $client   The Guzzle client
     * @param array $middlewares        An array of middlewares
     */
    public function __construct(ClientInterface $client, array $middlewares = [])
    {
        $this->client = $client;
        $this->middlewares = $middlewares;
    }

    /**
     * Sends a client request
     *
     * Send a client request, and pass it through our middleware, in
     * order to potentially further manipulate it.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Offdev\Gpp\Http\Exceptions\InvalidArgumentException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $this->hasBegunProcessing = false;
        $this->originalRequest = $request;
        return $this->handle($this->client->send($request));
    }

    /**
     * Handles the response
     *
     * Handles a response, and makes it pass through the given list
     * of middlewares.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Offdev\Gpp\Http\Exceptions\InvalidArgumentException
     */
    public function handle(ResponseInterface $response): ResponseInterface
    {
        static $m;
        if (!$this->hasBegunProcessing) {
            $m = $this->middlewares;
            $this->hasBegunProcessing = true;
        }

        $currentMiddleware = $this->resolveMiddleware(array_shift($m));
        if ($currentMiddleware instanceof MiddlewareInterface) {
            return $currentMiddleware->process($this->originalRequest, $response, $this);
        }

        return $response;
    }

    /**
     * @param mixed $middleware
     * @return MiddlewareInterface|null
     * @throws \Offdev\Gpp\Http\Exceptions\InvalidArgumentException
     */
    private function resolveMiddleware($middleware = null): ?MiddlewareInterface
    {
        $middlewareType = gettype($middleware);
        switch ($middlewareType) {
            case 'string':
                if (class_exists($middleware)) {
                    $middleware = new $middleware();
                }
            // class gets instantiated if it's a string, and if the class exists
            // it gets checked for its interface
            case 'object':
                if ($middleware instanceof MiddlewareInterface) {
                    return $middleware;
                }
                break;
            case 'NULL':
                return null;
        }

        $what = is_object($middleware) ? get_class($middleware) :
            (is_string($middleware) ? $middleware : $middlewareType);
        throw new InvalidArgumentException("Invalid middleware: ".$what);
    }
}
