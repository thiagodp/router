![Packagist Version](https://img.shields.io/packagist/v/phputil/router?style=for-the-badge&color=green)
![GitHub License](https://img.shields.io/github/license/thiagodp/router?style=for-the-badge&color=green)
![Packagist Downloads](https://img.shields.io/packagist/dt/phputil/router?style=for-the-badge&color=green)
![Build](https://github.com/thiagodp/router/actions/workflows/ci.yml/badge.svg?style=for-the-badge&color=green)

# phputil/router

> ExpressJS-like router for PHP

- No third-party dependencies
- Unit-tested
- Mockable - it's easy to create automated tests for your API

👉 Do **NOT** use it in production yet - just for toy projects.

## Installation

> Requires PHP 7.4+

```bash
composer require phputil/router
```

_Was it useful for you? Consider giving it a Star ⭐_

### Installation notes

- Unlike ExpressJS, `phputil/router` needs an HTTP server to run (if the request is not [mocked](#mocking-an-http-request)). You can use the HTTP server of your choice, such as `php -S localhost:80`, [Apache](https://httpd.apache.org/), [Nginx](https://nginx.org/) or [http-server](https://www.npmjs.com/package/http-server). **See [Server Configuration](server.md) for more information.**
- If you are using **Apache** or **Nginx**, you may need to inform the `rootURL` parameter when calling `listen()`. Example:
    ```php
    // Sets the 'rootURL' to where the index.php is located.
    $app->listen( [ 'rootURL' => dirname( $_SERVER['PHP_SELF'] ) ] );
    ```

## Middlewares

You may also want to install the following middlewares:

- [phputil/cors](https://github.com/thiagodp/cors) - [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) Middleware
- [phputil/csrf](https://github.com/thiagodp/phputil-csrf) - Anti [CSRF](https://en.wikipedia.org/wiki/Cross-site_request_forgery) Middleware

> ℹ Did you create a useful middleware? Open an Issue for including it here.


## Examples

### Hello World

```php
require_once 'vendor/autoload.php';
use \phputil\router\Router;

$app = new Router();
$app->get( '/', function( $req, $res ) {
    $res->send( 'Hello World!' );
} );
$app->listen();
```

### Using parameters

```php
require_once 'vendor/autoload.php';
use \phputil\router\Router;

$app = new Router();
$app->get( '/', function( $req, $res ) {
        $res->send( 'Hi, Anonymous' );
    } )
    ->get( '/:name', function( $req, $res ) {
        $res->send( 'Hi, ' . $req->param( 'name' ) );
    } )
    ->get( '/json/:name', function( $req, $res ) {
        $res->json( [ 'hi' => $req->param( 'name' ) ] );
    } );
$app->listen();
```

### Middleware per route

```php
require_once 'vendor/autoload.php';
use \phputil\router\Router;

$middlewareIsAdmin = function( $req, $res, &$stop ) {
    session_start();
    $isAdmin = isset( $_SESSION[ 'admin' ] ) && $_SESSION[ 'admin' ];
    if ( $isAdmin ) {
        return; // Access allowed
    }
    $stop = true;
    $res->status( 403 )->send( 'Admin only' ); // Forbidden
};

$app = new Router();
$app->get( '/admin', $middlewareIsAdmin, function( $req, $res ) {
    $res->send( 'Hello, admin' );
} );
$app->listen();
```


[See all the examples](https://github.com/thiagodp/router/tree/main/examples/)

> ℹ Interested in helping us? Submit a Pull Request with a new example or open an Issue with your code.

## Features

- [✔] Support to standard HTTP methods (`GET`, `POST`, `PUT`, `DELETE`, `HEAD`, `OPTIONS`) and `PATCH`.
- [✔] Route parameters
    - _e.g._ `$app->get('/customers/:id', function( $req, $res ) { $res->send( $req->param('id') ); } );`
- [✔] URL groups
    - _e.g._ `$app->route('/customers/:id')->get('/emails', $cbGetEmails );`
- [✔] Global middlewares
    - _e.g._ `$app->use( function( $req, $res, &$stop ) { /*...*/ } );`
- [✔] Middlewares per URL group
    - _e.g._ `$app->route( '/admin' )->use( $middlewareIsAdmin )->get( '/', function( $req, $res ) { /*...*/ } );`
- [✔] Middlewares per route
    - _e.g._ `$app->get( '/', $middleware1, $middleware2, function( $req, $res ) { /*...*/ } );`
- [✔] Request cookies
    - _e.g._ `$app->get('/', function( $req, $res ) { $res->send( $req->cookie('sid') ); } );`
- [✔] _Extra_: Can mock HTTP requests for testing, without the need to running an HTTP server.
- [🕑] _(soon)_ Deal with `multipart/form-data` on `PUT` and `PATCH`



## API

This library does not aim to cover the entire [ExpressJS API](https://expressjs.com/en/api.html). However, feel free to contribute to this project and add more features.

Types:
- [Middleware](#middleware)
- [Router](#router)
- [RouterOptions](#routeroptions)
- [HttpRequest](#httprequest)
- [ExtraData](#extradata)
- [HttpResponse](#httpresponse)


### Middleware

In `phputil/router`, a middleware is a function that:

1. Perform some action (e.g., set response headers, verify permissions) _before_ a route is evaluated.
2. Can stop the router, optionally setting a response.

Syntax:
```php
function ( HttpRequest $req, HttpResponse $res, bool &$stop = false )
```
where:
- `$req` allows to _get_ all the _request_ headers and data.
- `$res` allows to _set_ all the _response_ headers and data.
- `$stop` allows to stop the router, when set to `true`.


### Router

> Class that represents a router.

#### get

Method that deals with a `GET` HTTP request.

```php
function get( string $route, callable ...$callbacks )
```
where:
- `$route` is a route (path).
- `$callbacks` can receive none, one or more [middleware](#middleware) functions and one route handler - which must be the last function.

A route handler has the following syntax:
```php
function ( HttpRequest $req, HttpResponse $res )
```
where:
- `$req` allows to _get_ all the _request_ headers and data.
- `$res` allows to _set_ all the _response_ headers and data.

Examples:
```php
use \phputil\router\HttpRequest;
use \phputil\router\HttpResponse;

$app->get( '/hello', function( HttpRequest $req, HttpResponse $res ) {
        $res->send( 'Hello!' );
    } )
    ->get( '/world',
        // Middleware
        function( HttpRequest $req, HttpResponse $res, bool &$stop ) {
            if ( $req->header( 'Origin' ) === 'http://localhost' ) {
                $res->status( 200 )->send( 'World!' );
                $stop = true;
            }
        },
        // Route handler
        function( HttpRequest $req, HttpResponse $res ) {
            $res->status( 400 )->send( 'Error: not in http://localhost :(' );
        }
    );
```

#### post

Method that deals with a `POST` HTTP request. Same syntax as [get](#get)'s.

#### put

Method that deals with a `PUT` HTTP request. Same syntax as [get](#get)'s.

#### delete

Method that deals with a `DELETE` HTTP request. Same syntax as [get](#get)'s.

#### head

Method that deals with a `HEAD` HTTP request. Same syntax as [get](#get)'s.

#### option

Method that deals with a `OPTION` HTTP request. Same syntax as [get](#get)'s.

#### patch

Method that deals with a `PATCH` HTTP request. Same syntax as [get](#get)'s.

#### all

Method that deals with any HTTP request. Same syntax as [get](#get)'s.

#### group

Alias to the method [route](#route).

#### route

Method that adds a route group, where you can register one or more HTTP method handlers.

Example:
```php
$app->
    route( '/employees' )
        ->get( '/emails', function( $req, $res ) { /* GET /employees/emails  */ } )
        ->get( '/phone-numbers', function( $req, $res ) { /* GET /employees/phone-numbers */ } )
        ->post( '/children', function( $req, $res ) { /* POST /employees/children */ } )
        ->end() // 👈 Finishes the group and back to "/"
    ->get( '/customers', function( $req, $res ) { /* GET /customers */ } )
    ;
```

⚠️ Don't forget to finish a route/group with the method `end()`.

#### end

Method that finishes a route group and returns to the group parent.

Example:
```php
$app->
    route( '/products' )
        ->get( '/colors', function( $req, $res ) { /* GET /products/colors  */ } )
        ->route( '/suppliers' )
            ->get( '/emails', function( $req, $res ) { /* GET /products/suppliers/emails */ } )
            ->end() // Finishes "/suppliers" and back to "/products"
        ->get( '/sizes', function( $req, $res ) { /* GET /products/sizes  */ } )
        ->end() // 👈 Finishes "/products" and back to "/"
    ->get( '/sales', function( $req, $res ) { /* GET /sales  */ } )
    ;
```

#### use

Method that adds a [middleware](#middleware) to be evaluated before the routes declared after it.

Example:
```php
$app
    ->use( $myMiddlewareFunction )
    ->get( '/hello', $sayHelloFunction ); // Executes after the middleware
```

#### listen

Method that executes the router.

```php
function listen( array|RouterOptions $options = [] ): void
```
Options are:
- `rootURL` is a string that sets the root URL. Example: `dirname( $_SERVER['PHP_SELF'] )`. By default it is `''`.
- `req` is an object that implements the interface `HttpRequest`, which retrieves all the headers and data from a HTTP request. _Changing it is only useful if you want to unit test your API_ - see [Mocking an HTTP request](#mocking-an-http-request). By default, it will receive an object from the class `RealHttpRequest`.
- `res` is an object that implements the interface `HttpResponse`. _You probably won't need to change its value_. By default, it will receive an object from the class `RealHttpResponse`.

Example:
```php
// Sets the 'rootURL' to where the index.php is located.
$app->listen( [ 'rootURL' => dirname( $_SERVER['PHP_SELF'] ) ] );
```

You can also use an instance of `RouterOptions` for setting the options:
```php
use phputil\router\RouterOptions;
// Sets the 'rootURL' to where the index.php is located.
$app->listen( ( new RouterOptions() )->withRootURL( dirname( $_SERVER['PHP_SELF'] ) ) );
```


### RouterOptions

> Options for the [Router](#router)'s [listen()](#listen) method.

#### withRootURL

```php
withRootURL( string $url ): RouterOptions
```

#### withReq

```php
withReq( HttpRequest $req ): RouterOptions
```

#### withRes

```php
withRes( HttpResponse $res ): RouterOptions
```


### HttpRequest

> Interface that represents an HTTP request.

API:

```php
interface HttpRequest {

    /** Returns the current URL or `null` on failure. */
    function url(): ?string;

    /** Returns the current URL without any queries. E.g. `/foo?bar=10` -> `/foo` */
    function urlWithoutQueries(): ?string;

    /** Returns the URL queries. E.g. `/foo?bar=10&zoo=A` -> `['bar'=>'10', 'zoo'=>'A']` */
    function queries(): array;

    /** Returns all HTTP request headers */
    function headers(): array;

    /** Returns the header with the given case-insensitive name, or `null` if not found. */
    function header( $name ): ?string;

    /** Returns the raw body or `null` on failure. */
    function rawBody(): ?string;

    /**
     * Returns the converted content, depending on the `Content-Type` header:
     *   - For `x-www-form-urlencoded`, it returns an `array`;
     *   - For `application/json`, it returns an `object` or an `array` (depending on the content).
     *   - Otherwise it returns a `string`, or `null` on failure.
     */
    function body();

    /** Returns the HTTP request method or `null` on failure. */
    function method(): ?string;

    /** Returns all cookies as an array (map). */
    function cookies(): array;

    /**
     * Returns the cookie value with the given case-insensitive key or `null` if not found.
     *
     * @param string $key Cookie key.
     * @return string|null
     */
    function cookie( $key ): ?string;

    /**
     * Returns a URL query or route parameter with the given name (key),
     * or `null` when the given name is not found.
     *
     * @param string $name Parameter name.
     * @return string
     */
    function param( $name ): ?string;

    /**
     * Returns all the URL queries and route parameters as an array (map).
     * @return array
     */
    function params(): array;

    /**
     * Returns extra, user-configurable data.
     * @return ExtraData
     */
    function extra(): ExtraData;

}

```


### ExtraData

> Extra, user-defined data.

Syntax:

```php
class ExtraData {

    /**
     * Sets a value to the given key. Chainable method.
     *
     * @param string|int $key
     * @param mixed $value
     * @return ExtraData
     */
    function set( $key, $value ): ExtraData;

    /**
     * Returns the value for the given key, or null otherwise.
     * @param string|int $key
     * @return mixed
     */
    function get( $key );

    /**
     * Returns the keys and values as an array.
     */
    function toArray(): array;

}
```


### HttpResponse

> Interface that represents an HTTP response.

Most of its methods are chainable, that is, you can call them in a sequence. Example:
```php
$response->status( 201 )->send( 'Saved successfully.' );
```

API:
```php
interface HttpResponse {

    /**
     * Sets the HTTP status code.
     *
     * @param int $code HTTP status code.
     * @return HttpResponse
     */
    function status( int $code ): HttpResponse;

    /**
     * Indicates if the current HTTP status code is equal to the given one.
     *
     * @param int $code HTTP status code.
     * @return bool
     */
    function isStatus( int $code ): bool;

    /**
     * Sets an HTTP header.
     *
     * @param string $header HTTP header.
     * @param string|int|float|bool|array $value Header value.
     * @return HttpResponse
     */
    function header( string $header, $value ): HttpResponse;

    /**
     * Indicates if the response has the given HTTP header.
     *
     * @param string $header HTTP header.
     * @return boolean
     */
    function hasHeader( string $header ): bool;

    /**
     * Returns the response header, if it exists. Returns `null` otherwise.
     *
     * @param string $header HTTP header.
     * @return string|null
     */
    function getHeader( string $header ): ?string;

    /**
     * Returns all the response headers with the given key, as a matrix.
     * Example: `[[ 'Set-Cookie' => 'foo=1;' ], [ 'Set-Cookie' => 'bar=hello;' ]]`
     *
     * @param string $header HTTP header.
     * @return string[]
     */
    function getHeaders( string $header ): array;

    /**
     * Removes the first header with the given key. Optionally removes all the headers with the given key.
     *
     * @param string $header Header to remove.
     * @param bool $removeAll Option (default `false`) to remove all the headers with the given key.
     * @return int The number of removed headers.
     */
    function removeHeader( string $header, bool $removeAll = false ): int;

    /**
     * Sets a redirect response.
     *
     * @param int $statusCode HTTP status code.
     * @param string|null $path Path.
     * @return HttpResponse
     */
    function redirect( int $statusCode, $path = null ): HttpResponse;

    /**
     * Sets a cookie.
     *
     * @param string $name Name (key)
     * @param string $value Value.
     * @param array $options Optional map with the following options:
     *  - `domain`: string
     *  - `path`: string
     *  - `httpOnly`: true|1
     *  - `secure`: true|1
     *  - `maxAge`: int
     *  - `expires`: string
     *  - `sameSite`: true|1
     * @return HttpResponse
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies for options' meanings.
     */
    function cookie( string $name, string $value, array $options = [] ): HttpResponse;

    /**
     * Clears a cookie with the given name (key).
     *
     * @param string $name Name (key)
     * @param array $options Optional map with the same options as #cookie()'s.
     * @return HttpResponse
     */
    function clearCookie( string $name, array $options = [] ): HttpResponse;

    /**
     * Sets the `Content-Type` header with the given MIME type.
     *
     * @param string $mime MIME type.
     * @return HttpResponse
     */
    function type( string $mime ): HttpResponse;

    /**
     * Sends the given HTTP response body.
     *
     * @param mixed $body Response body.
     * @return HttpResponse
     */
    function send( $body ): HttpResponse;

    /**
     * Sends a file based on its path.
     *
     * @param string $path File path
     * @param array $options Optional map with the options:
     *  - `mime`: string - MIME type, such as `application/pdf`.
     * @return HttpResponse
     */
    function sendFile( string $path, array $options = [] ): HttpResponse;

    /**
     * Send the given content as JSON, also setting the needed headers.
     *
     * @param mixed $body Content to send as JSON.
     * @return HttpResponse
     */
    function json( $body ): HttpResponse;

    /**
     * Ends the HTTP response.
     *
     * @param bool $clear If it is desired to clear the headers and the body after sending them. It defaults to `true`.
     */
    function end( bool $clear = true ): HttpResponse;
}
```


### Mocking an HTTP request

👉 Useful for API testing

```php
require_once 'vendor/autoload.php';
use \phputil\router\FakeHttpRequest;
use \phputil\router\Router;
$app = new Router();

// Set a expectation
$app->get( '/foo', function( $req, $res ) { $res->send( 'Called!' ); } );

// Mock the request
$fakeReq = new FakeHttpRequest();
$fakeReq->withURL( '/foo' )->withMethod( 'GET' );

// Use the mock request
$app->listen( [ 'req' => $fakeReq ] ); // It will use the fake request to call "/foo"
```

## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
