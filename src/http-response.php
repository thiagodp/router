<?php
namespace phputil\router;

interface HttpResponse {
    function status( int $code );
    function header( $header, $value );
    function redirect( $statusCode, $path = null );
    function cookie( $name, $value, array $options = [] );
    function clearCookie( $name, array $options = [] );
    function type( $mime );
    function send( $body );
    function sendFile( $path, $options = [] );
    function json( $body );
    function end( $clear = true );
}

?>