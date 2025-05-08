<?php
namespace phputil\router;

use function explode;
use function json_decode;
use function mb_parse_str;
use function mb_strpos;
use function mb_strtolower;
use function mb_substr;
use function str_replace;
use function ucwords;

// TODO: change to allow repeated keys - options: 1) matrix of key-value 2) multiple values for a given key
function extractHeaders( array &$array ) {

    $headers = [];

    // Copy values and fix some keys
    foreach ( $array as $key => $value ) {
        if ( mb_substr( $key, 0, 5 ) === 'HTTP_' ) {
            $newKey = mb_substr( $key, 5 ); // Remove "HTTP_"
            $newKey = str_replace( '_', ' ', $newKey ); // Replace "_" with " "
            $newKey = ucwords( $newKey ); // Uppercase the first letter of each word
            $newKey = str_replace( ' ', '-', $newKey ); // Replace " " with "-"
            $headers[ $newKey ] = $value;
        } else if ( $key === 'CONTENT_TYPE' ) {
            $headers[ 'Content-Type' ] = $value;
        } else if ( $key === 'CONTENT_LENGTH' ) {
            $headers[ 'Content-Length' ] = $value;
        } else {
            $headers[ $key ] = $value;
        }
    }

    // Check for alternative Authorization headers
    if ( ! isset( $headers[ 'Authorization' ] ) ) {
        $key = 'Authorization';
        if ( isset( $array[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ) {
            $headers[ $key ] = $array[ 'REDIRECT_HTTP_AUTHORIZATION' ];
        } else if ( isset( $array[ 'PHP_AUTH_USER' ] ) ) {
            $pwd = isset( $array[ 'PHP_AUTH_PW' ] ) ? $array[ 'PHP_AUTH_PW' ] : '';
            $headers[ $key ] = 'Basic ' . base64_encode( $array[ 'PHP_AUTH_USER' ] . ':' . $pwd );
        } else if ( isset( $array[ 'PHP_AUTH_DIGEST' ] ) ) {
            $headers[ $key ] = $array[ 'PHP_AUTH_DIGEST' ];
        }
    }

    return $headers;
}


function headerWithName( $name, array $array ): ?string {
    if ( isset( $array[ $name ] ) ) {
        return $array[ $name ];
    }
    $headers = extractHeaders( $array );
    $name = mb_strtolower( $name );
    foreach ( $headers as $key => $value ) {
        if ( mb_strtolower( $key ) == $name ) {
            return $value;
        }
    }
    return null;
}


/**
 * Extract cookies from the headers.
 *
 * @param array $headers Headers.
 * @return array
 */
function extractCookies( array $headers ) {
    $cookies = [];
    foreach ( $headers as $key => $value ) {
        if ( mb_strtolower( $key ) === 'cookie' ) {
            $pairs = explode( ';', $value ); // Allow more than one key per Cookie header
            foreach ( $pairs as $p ) {
                list( $k, $v ) = explode( '=', $p );
                $cookies[ $k ] = $v;
            }
        }
    }
    return $cookies;
}


function removeQueries( $url ) {
    if ( null === $url ) {
        return null;
    }
    $index = mb_strpos( $url, '?' );
    if ( $index === false ) {
        return $url;
    }
    return mb_substr( $url, 0, $index );
}


function analizeBody( $contentType, $rawBody ) {
    if ( $contentType === null ) {
        return $rawBody;
    }
    list( $cType ) = explode( ';', mb_strtolower( $contentType ) ); // Explode to avoid to comparing the charset
    if ( $cType === 'application/x-www-form-urlencoded' ) {
        $data = [];
        if ( mb_parse_str( $rawBody, $data ) ) { // Success
            return $data;
        }
        return $rawBody;
    }
    if ( $cType === 'application/json' ) {
        $r = json_decode( $rawBody );
        if ( $r !== null ) { // Maybe success
            return $r;
        }
        return ( $rawBody === 'null' ) ? $r : $rawBody;
    }
    return $rawBody;
}

?>