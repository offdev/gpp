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

use Offdev\Gpp\Utils\RequestEnumeratorInterface;
use Psr\Http\Message\RequestInterface;

/**
 * A web crawler
 *
 * Web crawler which makes use of URL enumeration, and the guzzle wrapper,
 * in order to make powerful bots for the webz! YAY!
 *
 * Class Crawler
 * @package Offdev\Gpp
 */
class Crawler
{
    /** @var Client */
    private $client;

    /** @var RequestEnumeratorInterface */
    private $enumerator;

    /** @var RequestInterface[] */
    private static $requestStack = [];

    /**
     * Wraps around the client and accept an enumerator
     *
     * @param Client $client
     * @param RequestInterface $firstRequest
     * @param RequestEnumeratorInterface $enumerator
     */
    public function __construct(Client $client, RequestInterface $firstRequest, RequestEnumeratorInterface $enumerator)
    {
        $this->client = $client;
        $this->enumerator = $enumerator;
        self::$requestStack[] = $firstRequest;
    }

    /**
     * Starts the crawler
     *
     * Start crawling from a given request. Wait for a given interval between each
     * request. A passed callback can be used to control the workflow of the crawler.
     *
     * @param int $interval
     * @param callable $callback
     * @return RequestInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function crawl(int $interval = 1, callable $callback = null): void
    {
        if (empty(self::$requestStack)) {
            return;
        }
        $request = array_shift(self::$requestStack);
        $response = $this->client->send($request);
        if (!is_null($callback)) {
            if ($callback($request, $response)) {
                return;
            }
        }

        self::$requestStack = array_merge(
            self::$requestStack,
            $this->enumerator->getNextRequests($request, $response)
        );
        sleep($interval);
        $this->crawl($interval, $callback);
    }
}
