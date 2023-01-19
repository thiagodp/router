<?php
// require_once 'vendor/autoload.php';
require_once '../../src/router.php';

$app = new \phputil\router\Router();
$app->get('/', function( $req, $res ) {
    $res->send( 'Hello, World!' );
} );
$app->listen();
?>
