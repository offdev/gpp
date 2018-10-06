# Offdev/Gpp (Guzzle++)

[![Latest Stable Version](https://img.shields.io/packagist/vpre/offdev/gpp.svg?style=flat-square)](https://packagist.org/packages/offdev/gpp)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/offdev/gpp/master.svg?style=flat-square)](https://travis-ci.org/offdev/gpp)
[![License](https://img.shields.io/github/license/offdev/gpp.svg)](https://www.apache.org/licenses/LICENSE-2.0)

### Requirements
* PHP >= 7.1
* Composer
* [Guzzle](https://github.com/guzzle/guzzle)

### Installation
```bash
$ composer require offdev/gpp
```

### Usage

#### Basic middleware usage

The middleware processes an incoming server response in order to further manipulate it. If it is unable to manipulate the response itself, it may delegate to the provided response handler to do so.

```php
<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Offdev\Gpp\Client;
use Offdev\Gpp\Http\MiddlewareInterface;
use Offdev\Gpp\Http\ResponseHandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DirectoryLister implements MiddlewareInterface
{
    public function process(
        RequestInterface $originalRequest,
        ResponseInterface $response,
        ResponseHandlerInterface $responseHandler
    ): ResponseInterface {
        $content = (string)$response->getBody();
        if (preg_match_all('/href="\/articles\/\d+\/([^"]+)"/m', $content, $matches, PREG_SET_ORDER)) {
            $result = [];
            foreach ($matches as $match) {
                $result[] = $match[1];
            }
            return new Response(200, [], json_encode($result, JSON_PRETTY_PRINT));
        }

        return $responseHandler->handle($response);
    }
}

$client = new Client(new GuzzleClient(), [DirectoryLister::class]);
$result = $client->send(new Request('GET', 'https://www.worldhunger.org/articles/12/'));
var_dump((string)$result->getBody());
```

Output:
```
$ php middleware.php
/tmp/gpp-examples/middleware.php:34:
string(300) "[
    "editorials\/",
    "global\/",
    "images\/",
    "us\/",
    "2012_archive.htm",
    "asia.htm",
    "books.htm",
    "davidson.htm---",
    "editorials.htm",
    "editorials2.htm",
    "global.htm",
    "newtemplate.htm",
    "phn.htm",
    "us.htm",
    "vanderslice_hungry_children.htm"
]"
```

#### URL enumeration

Have a look at the [IntegerEnumerator](https://github.com/offdev/gpp/blob/master/src/Utils/IntegerEnumerator.php) class, which is included in this package. It is a **very basic** example, that will increase **any** number found in a given URL.

```php
<?php

use GuzzleHttp\Psr7\Request;
use Offdev\Gpp\Utils\IntegerEnumerator;

$enumerator = new IntegerEnumerator();
$nextRequest = $enumerator->getNextRequest(
    new Request('GET', 'https://www.worldhunger.org/articles/12/')
);

var_dump((string)$nextRequest->getUri());
```

Output:
```
$ php enumerator.php
/tmp/gpp-examples/enumerator.php:11:
string(40) "https://www.worldhunger.org/articles/13/"
```

#### Crawler usage

```php
<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Offdev\Gpp\Client;
use Offdev\Gpp\Crawler;
use Offdev\Gpp\Utils\IntegerEnumerator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$client = new Client(new GuzzleClient(['exceptions' => false]));
$crawler = new Crawler($client, new IntegerEnumerator());
$crawler->crawl(
    new Request('GET', 'https://www.worldhunger.org/articles/15/'),
    5, // time between each request, in seconds
    function ( // callback function, to control the crawler workflow
        RequestInterface $originalRequest,
        ResponseInterface $response
    ) {
        echo $response->getStatusCode().' : '.(string)$originalRequest->getUri().PHP_EOL;
        if ($response->getStatusCode() !== 200) {
            return true; // cancel crawling
        }
        // go ahead, wait for the interval, and crawl the next result
        return false;
    }
);
```

Output
```
$ php crawler.php
200 : https://www.worldhunger.org/articles/15/
200 : https://www.worldhunger.org/articles/16/
404 : https://www.worldhunger.org/articles/17/
```

### Code quality
##### Who teh fuck needs that anyways?

First, make sure to install the dependencies by running ```composer install```. You also need to make sure to have xdebug activated in order for PHPUnit to generate the code coverage.

**PHP Code Sniffer**
```
$ ./vendor/bin/phpcs --colors --standard=PSR2 -v src/ tests/
Creating file list... DONE (13 files in queue)
Changing into directory /Users/pascal/devel/gpp/src
Processing Crawler.php [PHP => 419 tokens in 71 lines]... DONE in 25ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/src/Utils
Processing RequestEnumeratorInterface.php [PHP => 201 tokens in 40 lines]... DONE in 16ms (0 errors, 0 warnings)
Processing IntegerEnumerator.php [PHP => 360 tokens in 51 lines]... DONE in 25ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/src/Http
Processing MiddlewareInterface.php [PHP => 225 tokens in 47 lines]... DONE in 16ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/src/Http/Exceptions
Processing PatternException.php [PHP => 91 tokens in 21 lines]... DONE in 6ms (0 errors, 0 warnings)
Processing InvalidArgumentException.php [PHP => 91 tokens in 21 lines]... DONE in 6ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/src/Http
Processing ResponseHandlerInterface.php [PHP => 169 tokens in 37 lines]... DONE in 13ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/src
Processing Client.php [PHP => 803 tokens in 129 lines]... DONE in 55ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/tests
Processing PassthroughModifierMiddleware.php [PHP => 395 tokens in 63 lines]... DONE in 26ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/tests/Utils
Processing IntegerEnumeratorTest.php [PHP => 320 tokens in 50 lines]... DONE in 14ms (0 errors, 0 warnings)
Changing into directory /Users/pascal/devel/gpp/tests
Processing CrawlerTest.php [PHP => 408 tokens in 52 lines]... DONE in 26ms (0 errors, 0 warnings)
Processing DirectResponseMiddleware.php [PHP => 385 tokens in 62 lines]... DONE in 28ms (0 errors, 0 warnings)
Processing ClientTest.php [PHP => 1159 tokens in 121 lines]... DONE in 92ms (0 errors, 0 warnings)
```

**PHPUnit**
```
$ ./vendor/bin/phpunit
PHPUnit 7.3.5 by Sebastian Bergmann and contributors.

.........                                                           9 / 9 (100%)

Time: 1.39 seconds, Memory: 6.00MB

OK (9 tests, 15 assertions)

Generating code coverage report in HTML format ... done


Code Coverage Report:
  2018-10-06 12:13:13

 Summary:
  Classes: 100.00% (3/3)
  Methods: 100.00% (7/7)
  Lines:   100.00% (44/44)

\Offdev\Gpp::Offdev\Gpp\Client
  Methods: 100.00% ( 4/ 4)   Lines: 100.00% ( 28/ 28)
\Offdev\Gpp::Offdev\Gpp\Crawler
  Methods: 100.00% ( 2/ 2)   Lines: 100.00% (  9/  9)
\Offdev\Gpp\Utils::Offdev\Gpp\Utils\IntegerEnumerator
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  7/  7)
```

**Infection**
```
$ ./vendor/bin/infection
You are running Infection with xdebug enabled.
    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/

Running initial test suite...

PHPUnit version: 7.3.5

   14 [============================]  1 sec

Generate mutants...

Processing source code files: 8/8
Creating mutated files and processes: 13/13
.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out

...........E.                                        (13 / 13)

13 mutations were generated:
      12 mutants were killed
       0 mutants were not covered by tests
       0 covered mutants were not detected
       1 errors were encountered
       0 time outs were encountered

Metrics:
         Mutation Score Indicator (MSI): 100%
         Mutation Code Coverage: 100%
         Covered Code MSI: 100%

Please note that some mutants will inevitably be harmless (i.e. false positives).
Dashboard report has not been sent: it is not a Travis CI

Time: 6s. Memory: 10.00MB
```

### License
[Apache-2.0](https://www.apache.org/licenses/LICENSE-2.0)