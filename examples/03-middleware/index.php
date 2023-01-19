<?php
// require_once 'vendor/autoload.php';
require_once '../../src/router.php';

//
// WARNING: You must run this example with a real server (like Apache), not PHP's.
//

$middlewareIsAdmin = function( $req, $res, &$stop ) {
    session_start();
    $isAdmin = isset( $_SESSION[ 'admin' ] ) && $_SESSION[ 'admin' ];
    if ( $isAdmin ) {
        return; // Access allowed
    }
    $stop = true;
    $res->status( 403 )->send( 'Admin only' ); // Forbidden
};

$app = new \phputil\router\Router();
$app->get( '/admin', $middlewareIsAdmin, function( $req, $res ) {
    $res->send( 'Hello, admin' );
} );
$app->post( '/admin', function( $req, $res ) {
    if ( $req->rawBody() == '123' ) {
        session_start();
        $_SESSION[ 'admin' ] = true;
        $res->status( 201 )->send( 'Success' );
    } else {
        $res->status( 400 )->send( 'Invalid password.' );
    }
} );
$app->listen();

?>