<?php
// require_once 'vendor/autoload.php';
require_once '../../src/router.php';

$app = new \phputil\router\Router();

$app->get( '/json', function( $req, $res ) { $res->json( [ 'hello' => 'world' ] ); } );

$app->get( '/people/:name', function( $req, $res ) { $res->send( $req->param('name') ); } );

$app->route( '/names' )
    ->get( '/:who', function( $req, $res ) {
        $who = $req->param( 'who' );
        $res->send( $who );
    } )
    ->post( '/', function( $req, $res ) {
        $name = $req->rawBody();
        $res->status( 201 )->send( "Created $name." );
    } );

$app->listen();
?>