<?php
// require_once 'vendor/autoload.php';
require_once '../../src/router.php';
require_once 'tasks.php'; // Fake model

$app = new \phputil\router\Router();

$app->route( '/tasks' )
    ->get( '/', function( $req, $res ) use ( &$tasks ) {
        $res->json( $tasks );
    } )
    ->post( '/', function( $req, $res ) use ( &$tasks ) {
        $t = (array) json_encode( $req->rawBody() );
        $id = createTask( $t );
        $res->status( 201 )->send( $id ); // Created
    } )
    ->get( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = taskKey( $req->param( 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        $res->json( $tasks[ $key ] );
    } )
    ->delete( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = taskKey( $req->param( 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        unset( $tasks[ $key ] ); // Remove
        $res->status( 204 )->end(); // No Content
    } )
    ->put( '/:id', function( $req, $res ) use ( &$tasks ) {
        $key = taskKey( $req->param( 'id' ) );
        if ( $key === false ) {
            return $res->status( 404 )->send( 'Not Found' );
        }
        $t = (array) json_encode( $req->rawBody() );
        $tasks[ $key ] = $t;
        $res->send( $req->param( 'id' ) );
    } );

$app->listen();
