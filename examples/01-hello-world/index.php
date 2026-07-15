<?php
// require_once 'vendor/autoload.php';
require_once __DIR__ . '/../../src/RouteToRegexResult.php';
require_once __DIR__ . '/../../src/Router.php';

$app = new \phputil\router\Router();

$app->setDebugMode( true );

$app->use( function( $req, $res ) {
    $res->header( 'Foo', 'bar' );
} );

$app->get('/', function( $req, $res ) {
    $res->send( 'Hello, World!' );
} );

$app->get('/error', function( $req, $res ) {
    throw new Exception( 'Ouch' );
} );

$app->listen();
?>
