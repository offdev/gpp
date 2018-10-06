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

use Psr\Http\Message\ResponseInterface;

/**
 * Handles a server response to further manipulate it
 *
 * An HTTP response handler processes an HTTP response in order
 * to produce a new HTTP response.
 *
 * Interface ResponseHandlerInterface
 * @package Offdev\Gpp\Http
 */
interface ResponseHandlerInterface
{
    /**
     * Handles a response to further manipulate it
     *
     * May call other collaborating code to generate the response.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(ResponseInterface $response): ResponseInterface;
}
