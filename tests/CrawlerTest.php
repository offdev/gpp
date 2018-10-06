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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Offdev\Gpp\Client;
use Offdev\Gpp\Crawler;
use Offdev\Gpp\Utils\IntegerEnumerator;
use PHPUnit\Framework\TestCase;

/**
 * Class CrawlerTest
 * @package Offdev\Tests
 */
final class CrawlerTest extends TestCase
{
    /**
     * Makes sure crawling works, and the callback functionality
     * can control the workflow as expected
     */
    public function testCrawlerWorks(): void
    {
        $counter = 2;
        $fnCallback = function () use (&$counter) {
            if ($counter-- <= 0) {
                return true;
            }
            return false;
        };
        $response = new Response(202, [], '[]');
        $mock = new MockHandler([$response, $response, $response]);
        $client = new Client(new GuzzleClient(['handler' => HandlerStack::create($mock)]));
        $crawler = new Crawler($client, new IntegerEnumerator());
        $lastRequest = $crawler->crawl(new Request('GET', 'https://website.com/articles/1?foo=bar'), 0, $fnCallback);
        $this->assertEquals('https://website.com/articles/3?foo=bar', (string)$lastRequest->getUri());
    }
}
