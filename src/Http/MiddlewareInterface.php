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

namespace Offdev\Gpp\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Participant in processing a server response
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the response, possibly generating a new response, or forwarding the
 * response to a subsequent middleware and possibly acting on its own response.
 *
 * Interface MiddlewareInterface
 * @package Offdev\Gpp\Http
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server response
     *
     * Processes an incoming server response in order to further manipulate it.
     * If unable to manipulate the response itself, it may delegate to the provided
     * response handler to do so.
     *
     * @param RequestInterface $originalRequest
     * @param ResponseInterface $response
     * @param ResponseHandlerInterface $responseHandler
     * @return ResponseInterface
     */
    public function process(
        RequestInterface $originalRequest,
        ResponseInterface $response,
        ResponseHandlerInterface $responseHandler
    ): ResponseInterface;
}
