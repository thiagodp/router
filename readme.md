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

### Using a parameter

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


**Some notes about `phputil/router`**:

1. Unlike ExpressJS, `phputil/router` needs an HTTP server to run (if the request is not [mocked](#mocking-an-http-request)). You can use the HTTP server of your choice, such as `php -S localhost:80`, Apache, Nginx or [http-server](https://www.npmjs.com/package/http-server).

2. The library does not aim to cover the entire [ExpressJS API](https://expressjs.com/en/api.html). However, feel free to contribute to this project and add more features.


## Known Middlewares

- [phputil/cors](https://github.com/thiagodp/cors) - [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) Middleware

> ℹ Did you create a useful middleware? Open an Issue for including it here.


## API

👉 This documentation is **_UNDER CONSTRUCTION_**. Until it isn't fully available, try to use it like the [ExpressJS API](https://expressjs.com/pt-br/4x/api.html) or see the [examples](https://github.com/thiagodp/router/tree/main/examples).


### Middleware

In `phputil/router`, a middleware is a function that can:

1. Perform some action (e.g., set response headers, verify permissions) before a route is evaluated.
2. Stop the router, optionally setting a response.

Syntax:
```php
function ( HttpRequest $req, HttpResponse $res, bool &$stop = false );
```
where:
- `$req` allows to _get_ all the _request_ headers and data.
- `$res` allows to _set_ all the _response_ headers and data.
- `$stop` allows to stop the router, when set to `true`.


### Router

...

#### listen
```php
listen( array|RouterOptions $options = [] ): void
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
