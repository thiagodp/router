# phputil/router

> Express-like router for PHP

- Fast ⚡
- Unit-tested ✅

_Warning: This router is under development. Do not use it in production yet._

## Installation

```bash
composer require phputil/router
```

## Features

- [✔] Standard HTTP methods (`GET`, `POST`, `PUT`, `DELETE`, `HEAD`, `OPTIONS`) and `PATCH`.
- [✔] Parameters
    - _e.g._ `$app->get('/customers/:id', function( $req, $res ) { $res->send( $req->param('id') ); } );`
- [✔] URL groups
    - _e.g._ `$app->route('/customers/:id')->get('/emails', $cbGetEmails );`
- [✔] Middlewares
    - _e.g._ `$app->use( function( $req, $res, &$stop ) { /*...*/ } );`
- [✔] Cookies
    - _e.g._ `$app->get('/', function( $req, $res ) { $res->send( $req->cookie('sid') ); } );`
- [✔] Chainable definitions
    - _e.g._ `$app->get( '/foo', $cbGetFoo )->post( '/foo', $cbPostFoo )->listen();`
- [✔] Extra: Can mock HTTP requests for testing, without needing to run an HTTP server.
- [🕑] _(soon)_ Deal with `multipart/form-data` on `PUT` and `PATCH`


**Note**: Unlike Express, `phputil/router` needs an HTTP server to run - if the request is not mocked. You can use the HTTP server of your choice, such as `php -S localhost:80`, Apache, Nginx or [http-server](https://www.npmjs.com/package/http-server).

## Usage


### Hello World

```php
require_once 'vendor/autoload.php';

$app = new \phputil\router\Router();
$app->get( '/hello', function( $req, $res ) { $res->send( 'Hello, world!' ); } );
$app->get( '/people/:name', function( $req, $res ) { $res->send( $req->param('name') ); } );
$app->listen();
```

### CRUD with JSON

```php
<?php
require_once 'vendor/autoload.php';
$app = new \phputil\router\Router();

$tasks = [ // Some data for the example
    ['id'=>1, 'what'=>'Buy beer'],
    ['id'=>2, 'what'=>'Wash the dishes']
];

function generateId( $arrayCopy ) { // Just for the example
    $last = end( $arrayCopy );
    return isset( $last, $last['id'] ) ? 1 + $last['id'] : 1;
}

$app->route( '/tasks' )
    ->get( '/', function( $req, $res ) use ( &$tasks ) {
        $res->json( $tasks );
    } )
    ->post( '/', function( $req, $res ) use ( &$tasks ) {
        $t = (array) json_encode( $req->rawBody() );
        $t['id'] = generateId( $tasks );
        $tasks []= $t;
        $res->status( 201 )->send( $t['id'] ); // Created
    } )
    ->get( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = array_search( $req->param( 'id' ), array_column( $tasks, 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        $res->json( $tasks[ $key ] );
    } )
    ->delete( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = array_search( $req->param( 'id' ), array_column( $tasks, 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        unset( $tasks[ $key ] ); // Remove
        $res->status( 204 )->end(); // No Content
    } )
    ->put( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = array_search( $req->param( 'id' ), array_column( $tasks, 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        $t = (array) json_encode( $req->rawBody() );
        $tasks[ $key ] = $t;
        $res->end();
    } );

$app->listen();
```

## API


## Mocking an HTTP request

```php
require_once 'vendor/autoload.php';
use \phputil\Router;
use \phputil\FakeHttpRequest;

$fakeReq = new FakeHttpRequest();
$fakeReq->withURL( '/foo' )->withMethod( 'GET' );

$app = new Router();
$app->get( '/foo', function( $req, $res ) { $res->send( 'Called!' ); } );
$app->listen( [ 'req' => $fakeReq ] ); // It will use the fake request and call /foo
```

## License

[MIT](LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
