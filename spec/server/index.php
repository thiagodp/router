<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use \phputil\router\Router;

$app = new Router();

$globalMiddleware = function( $req, $res ) {
    $res->header( 'Hello', 'World' );
};

$globalMiddleware2 = function( $req, $res, &$stop ) {
    if ( $req->method() === 'OPTIONS' ) {
        $stop = true;
        $res->status( 204 );
        $res->header( 'X', 'Y' );
        $res->header( 'method', $req->method() );
        $res->end();
    }
};

$app->use( $globalMiddleware );
$app->use( $globalMiddleware2 );

$app->get( '/customers', function( $req, $res ) {
    $res->status( 200 )->json( [
        (object) [ 'name' => 'Bob', 'email' => 'bob@example.com' ],
        (object) [ 'name' => 'Alice', 'email' => 'aliceb@example.com' ],
    ] );
} );

$middlewareFruits = function( $req, $res ) {
    $res->header( 'Foo', 'Bar' );
};

$app->get( '/fruits', $middlewareFruits, function( $req, $res ) {
    $res->status( 200 )->json( [
        'apple',
        'banana',
        'grape',
        'pineapple'
    ] );
} );

$app->listen();