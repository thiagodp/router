[![Version](https://poser.pugx.org/phputil/router/v?style=flat-square)](https://packagist.org/packages/phputil/router)
![Build](https://github.com/thiagodp/router/actions/workflows/ci.yml/badge.svg?style=flat)
[![License](https://poser.pugx.org/phputil/router/license?style=flat-square)](https://packagist.org/packages/phputil/router)

# phputil/router

> ExpressJS-like router for PHP

## Installation

> Requires PHP 7.4+

```bash
composer require phputil/router
```

👉 You may also like to install [phputil/cors](https://github.com/thiagodp/cors).

### Notes

- Unlike ExpressJS, `phputil/router` needs an HTTP server to run (if the request is not [mocked](#mocking-an-http-request)). You can use the HTTP server of your choice, such as `php -S localhost:80`, [Apache](https://httpd.apache.org/), [Nginx](https://nginx.org/) or [http-server](https://www.npmjs.com/package/http-server).
- If you are using Apache or Nginx, you may need to inform the `rootURL` parameter when calling `listen()`. Example:
    ```php
    // Sets the 'rootURL' to where the index.php is located.
    $app->listen( [ 'rootURL' => dirname( $_SERVER['PHP_SELF'] ) ] );
    ```

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

> ℹ To help us with an example, just submit a Pull Request or open an Issue with the code.

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


## Known Middlewares

- [phputil/cors](https://github.com/thiagodp/cors) - [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) Middleware

> ℹ Did you create a useful middleware? Open an Issue for including it here.


## API

Notes:
- This documentation is **_UNDER CONSTRUCTION_**. Until it isn't fully available, try to use it like the [ExpressJS API](https://expressjs.com/pt-br/4x/api.html) or see the [examples](https://github.com/thiagodp/router/tree/main/examples).

- This library does not aim to cover the entire [ExpressJS API](https://expressjs.com/en/api.html). However, feel free to contribute to this project and add more features.

Types:
- [Middleware](#middleware)
- [Router](#router)
- [RouterOptions](#routeroptions)
- HttpRequest ⏳ _soon_
- HttpResponse ⏳ _soon_


### Middleware

In `phputil/router`, a middleware is a function that can:

1. Perform some action (e.g., set response headers, verify permissions) before a route is evaluated.
2. Stop the router, optionally setting a response.

Syntax:
```php
function ( HttpRequest $req, HttpResponse $res, bool &$stop = false )
```
where:
- `$req` allows to _get_ all the _request_ headers and data.
- `$res` allows to _set_ all the _response_ headers and data.
- `$stop` allows to stop the router, when set to `true`.


### Router

Class that represents a router.

#### get

Method that deals with a `GET` HTTP request.

```php
function get( string $route, callable ...$callbacks )
```
where:
- `$route` is a route (path).
- `$callbacks` can receive zero or more [middlewares](#middleware) and one route handler - that must be the last function.

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

$app->
    get( '/hello', function( HttpRequest $req, HttpResponse $res ) {
        $res->send( 'Hello!' );
    } )
    get( '/world',
        function( HttpRequest $req, HttpResponse $res, bool &$stop ) { // Middleware
            if ( $req->header( 'Origin' ) === 'http://localhost' ) {
                $res->status( 200 )->send( 'World!' );
                $stop = true;
            }
        },
        function( HttpRequest $req, HttpResponse $res ) { // Route handler
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
        ->end() // Finishes the group and back to "/"
    ->get( '/customers', function( $req, $res ) { /* GET /customers */ } )
    ;
```

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
        ->end() // Finishes "/products" and back to "/"
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

Class that can hold the values used by the [Router](#router)'s [listen()](#listen) method.

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

> Soon


### HttpResponse

> Soon


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
