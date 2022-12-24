<?php
//
// Run with       : php -S localhost:8080
// Verify it with : curl http://localhost:8080/hello
//
require_once '../../src/router.php';

$app = new \phputil\router\Router();
$app->get( '/hello', function( $req, $res ) {
    $res->send( 'Hello, World!' );
} );
$app->listen();
?>
