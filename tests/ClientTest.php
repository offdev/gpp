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
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 * @package Offdev\Tests
 */
final class ClientTest extends TestCase
{
    /**
     * Makes sure the response doesn't get modified whe no middleware ws given
     */
    public function testSendWithoutMiddleWareReturnsIdenticalResponse(): void
    {
        $expectedResponse = new Response(202, [], '[]');
        $mock = new MockHandler([$expectedResponse]);
        $handler = HandlerStack::create($mock);
        $client = new Client(new GuzzleClient(['handler' => $handler]));
        $actualResponse = $client->send(new Request(200, 'doesn\'t matter'));
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Make sure middleware instantiation works when defined as string
     */
    public function testSendWithMiddleWareAsString(): void
    {
        $expectedResponse = new Response(202, [], '[]');
        $mock = new MockHandler([$expectedResponse]);
        $handler = HandlerStack::create($mock);
        $middleware = [DirectResponseMiddleware::class];
        $client = new Client(new GuzzleClient(['handler' => $handler]), $middleware);
        $actualResponse = $client->send(new Request(200, 'doesn\'t matter'));
        $this->assertEquals('middleware', $actualResponse->getBody()->getContents());
        $this->assertEquals(666, $actualResponse->getStatusCode());
    }

    /**
     * Make sure an exception is thrown when given an non existing or invalid middleware as string
     *
     * @expectedException \Offdev\Gpp\Http\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Invalid middleware: doesnt-exist-lol
     */
    public function testSendWithInvalidMiddleWareAsString(): void
    {
        $expectedResponse = new Response(202, [], '[]');
        $mock = new MockHandler([$expectedResponse]);
        $handler = HandlerStack::create($mock);
        $middleware = ['doesnt-exist-lol'];
        $client = new Client(new GuzzleClient(['handler' => $handler]), $middleware);
        $client->send(new Request(200, 'doesn\'t matter'));
    }

    /**
     * Make sure middleware instantiation works as when given as object
     */
    public function testSendWithMiddleWareAsObject(): void
    {
        $expectedResponse = new Response(202, [], '[]');
        $mock = new MockHandler([$expectedResponse]);
        $handler = HandlerStack::create($mock);
        $middleware = [new DirectResponseMiddleware()];
        $client = new Client(new GuzzleClient(['handler' => $handler]), $middleware);
        $actualResponse = $client->send(new Request(200, 'doesn\'t matter'));
        $this->assertEquals('middleware', $actualResponse->getBody()->getContents());
        $this->assertEquals(666, $actualResponse->getStatusCode());
    }

    /**
     * Make sure an exception is thrown when given as an invalid object
     * which doesn't implement the middleware interface
     *
     * @expectedException \Offdev\Gpp\Http\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Invalid middleware: stdClass
     */
    public function testSendWithInvalidMiddleWareAsObject(): void
    {
        $expectedResponse = new Response(202, [], '[]');
        $mock = new MockHandler([$expectedResponse]);
        $handler = HandlerStack::create($mock);
        $middleware = [new \stdClass()];
        $client = new Client(new GuzzleClient(['handler' => $handler]), $middleware);
        $client->send(new Request(200, 'doesn\'t matter'));
    }

    /**
     * Make sure passing through several middlewares works too and in the correct order
     */
    public function testClientResetsMiddlewareStackAfterEachRequest(): void
    {
        $expectedResponse = new Response(202, [], 'BOOOOOM');
        $mock = new MockHandler([$expectedResponse, $expectedResponse]);
        $handler = HandlerStack::create($mock);
        $middleware = [new PassthroughModifierMiddleware(), new PassthroughModifierMiddleware(333, 'lol')];
        $client = new Client(new GuzzleClient(['handler' => $handler]), $middleware);
        $client->send(new Request('GET', 'first request'));
        $actualResponse = $client->send(new Request('GET', 'second request'));
        $this->assertEquals('lol', $actualResponse->getBody()->getContents());
        $this->assertEquals(333, $actualResponse->getStatusCode());
    }
}
