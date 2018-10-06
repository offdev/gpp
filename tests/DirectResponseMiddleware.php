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
declare(strict_types=1);

namespace Offdev\Tests;

use GuzzleHttp\Psr7\Response;
use Offdev\Gpp\Http\MiddlewareInterface;
use Offdev\Gpp\Http\ResponseHandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PersonFinder
 * @package Offdev\Tests
 */
class DirectResponseMiddleware implements MiddlewareInterface
{
    /** @var int */
    private $code;

    /** @var string */
    private $body;

    /** @var array */
    private $headers;

    /**
     * @param int $code
     * @param string $body
     * @param array $headers
     */
    public function __construct(int $code = 666, string $body = 'middleware', array $headers = [])
    {
        $this->code = $code;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @param RequestInterface $originalRequest
     * @param ResponseInterface $response
     * @param ResponseHandlerInterface $responseHandler
     * @return ResponseInterface
     */
    public function process(
        RequestInterface $originalRequest,
        ResponseInterface $response,
        ResponseHandlerInterface $responseHandler
    ): ResponseInterface {
        return new Response($this->code, $this->headers, $this->body);
    }
}
