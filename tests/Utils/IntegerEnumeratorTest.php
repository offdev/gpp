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

namespace Offdev\Tests\Utils;

use GuzzleHttp\Psr7\Request;
use Offdev\Gpp\Utils\IntegerEnumerator;
use PHPUnit\Framework\TestCase;

/**
 * Class IntegerEnumeratorTest
 * @package Offdev\Tests\Utils
 */
final class IntegerEnumeratorTest extends TestCase
{
    /**
     * Makes sure enumeration works.
     */
    public function testValidUrl(): void
    {
        $originalRequest = new Request('GET', 'https://some-website.com/article/1?whatever=foo');
        $enumerator = new IntegerEnumerator();
        $newRequest = $enumerator->getNextRequests($originalRequest)[0];
        $this->assertEquals('https://some-website.com/article/2?whatever=foo', (string)$newRequest->getUri());
    }

    /**
     * When no number was found, throw errors!
     *
     * @expectedException \Offdev\Gpp\Http\Exceptions\PatternException
     * @expectedExceptionMessage URI 'lol' doesn't match pattern!
     */
    public function testInvalidUrl(): void
    {
        $originalRequest = new Request('GET', 'lol');
        $enumerator = new IntegerEnumerator();
        $newRequest = $enumerator->getNextRequests($originalRequest)[0];
    }
}
