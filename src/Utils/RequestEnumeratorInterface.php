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

namespace Offdev\Gpp\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Generates a new request
 *
 * Generates a new request, based on a previous one. May use an
 * optionally passed response, upon which it may depend.
 *
 * Interface RequestEnumeratorInterface
 * @package Offdev\Gpp\Crawler
 */
interface RequestEnumeratorInterface
{
    /**
     * Generates a new request
     *
     * Generates a new request, based on a previous one. May use an
     * optionally passed response, upon which it may depend.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return RequestInterface[]
     */
    public function getNextRequests(RequestInterface $request, ResponseInterface $response = null): array;
}
