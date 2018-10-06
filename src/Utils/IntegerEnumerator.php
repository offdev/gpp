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

use GuzzleHttp\Psr7\Request;
use Offdev\Gpp\Http\Exceptions\PatternException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Enumerates numbers in URLs
 *
 * Increases any number found in a given URL by one.
 *
 * Class IntegerEnumerator
 * @package Offdev\Gpp\Http
 */
class IntegerEnumerator implements RequestEnumeratorInterface
{
    /** @var string */
    private $pattern = '_([0-9]+)_';

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return RequestInterface
     */
    public function getNextRequest(RequestInterface $request, ResponseInterface $response = null): RequestInterface
    {
        $matches = [];
        $uri = $request->getUri();
        if (1 != preg_match($this->pattern, $uri, $matches)) {
            throw new PatternException("URI '{$uri}' doesn't match pattern!");
        }
        $newUri = preg_replace_callback($this->pattern, function (array $matches) {
            return array_pop($matches) + 1;
        }, $uri);

        return new Request($request->getMethod(), $newUri, $request->getHeaders(), $request->getBody());
    }
}
