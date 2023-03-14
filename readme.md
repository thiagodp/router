[![Version](https://poser.pugx.org/phputil/router/v?style=flat-square)](https://packagist.org/packages/phputil/router)
![Build](https://github.com/thiagodp/router/actions/workflows/ci.yml/badge.svg?style=flat)
[![License](https://poser.pugx.org/phputil/router/license?style=flat-square)](https://packagist.org/packages/phputil/router)

# phputil/router

> ExpressJS-like router for PHP

_Warning: This router is under development. Do not use it in production yet._

## Installation

```bash
composer require phputil/router
```

> Requires PHP 7.4+

## Examples

[See all the examples](https://github.com/thiagodp/router/tree/main/examples/)

To help us with an example, just submit a Pull Request or open an Issue with the code.

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

### Saying Hi

```php
require_once 'vendor/autoload.php';
use \phputil\router\Router;

$app = new Router();
$app->route( '/hi' )
    ->get( '/', function( $req, $res ) {
        $res->send( 'Hi, Anonymous' );
    } )
    ->get( '/:name', function( $req, $res ) {
        $res->send( 'Hi, ' . $req->param( 'name' ) );
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

## Known Middlewares

- [phputil/cors](https://github.com/thiagodp/cors) - CORS Middleware

> Did you create a useful middleware? Open an Issue to include it here.


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
- [✔] _Extra_: Can mock HTTP requests for testing, without needing to run an HTTP server.
- [🕑] _(soon)_ Deal with `multipart/form-data` on `PUT` and `PATCH`


**Some notes about `phputil/router`**:

1. Unlike ExpressJS, `phputil/router` needs an HTTP server to run (if the request is not [mocked](#mocking-an-http-request)). You can use the HTTP server of your choice, such as `php -S localhost:80`, Apache, Nginx or [http-server](https://www.npmjs.com/package/http-server).

2. The library does not aim to cover the entire [ExpressJS API](https://expressjs.com/en/api.html). However, feel free to contribute to this project and add more features.


## API

**_Soon_**. Until it isn't available, try to use it like the [ExpressJS API](https://expressjs.com/pt-br/4x/api.html).


### Mocking an HTTP request

```php
require_once 'vendor/autoload.php';
use \phputil\router\FakeHttpRequest;
use \phputil\router\Router;

$app = new Router();
$app->get( '/foo', function( $req, $res ) { $res->send( 'Called!' ); } );

$fakeReq = new FakeHttpRequest();
$fakeReq->withURL( '/foo' )->withMethod( 'GET' );

$app->listen( [ 'req' => $fakeReq ] ); // It will use the fake request to call "/foo"
```

## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
