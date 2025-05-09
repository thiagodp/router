<?php
namespace phputil\router;

use function explode;
use function trim;

// ===> THIS FILE IS UNFINISHED <===

const ACCEPT = 'Accept';
const ACCEPT_ENCODING = 'Accept-Encoding';

function removeQualityValues( array &$input ) {
    $new = [];
    foreach ( $input as &$value ) {
        $r = explode( ';', trim( $value ) );
        $new [] = $r[ 0 ];
    }
    return $new;
}

class When {

    private $headers = [];
    private $router = null;
    private $route = '';

    public function __construct( &$httpRequest, &$router, $route ) {
        $this->headers = $httpRequest->headers();
        $this->router = $router;
        $this->route = $route;
    }

    public function end() {
        return $this;
    }

    // Accept

    public function acceptIsIn( array $desiredFormatsOrMimes ) {
        if ( array_search( MIME_ANY, $desiredFormatsOrMimes ) !== false ) { // Any MIME ?
            return true;
        }
        if ( ! isset( $this->headers[ ACCEPT ] ) ) { // Not found
            return false;
        }
        foreach ( $this->headers as $key => $value ) {
            if ( $key !== ACCEPT ) { // Ignore
                continue;
            }
            $receivedMimes = explode( ',', $value ); // Many values in a single Accept header
            $mimesWithoutQualityValues = removeQualityValues( $receivedMimes );
            if ( areMimeCompatible( $desiredFormatsOrMimes, $mimesWithoutQualityValues ) ) {
                return true;
            }
        }
        return false;
    }

    public function acceptCharsetIsIn( array $charsets ) {}

    public function acceptEncodingIsIn( array $encodings ) {
        if ( array_search( ENCODING_ANY, $encodings ) !== false ) { // Any encoding ?
            return true;
        }
        if ( ! isset( $this->headers[ ACCEPT_ENCODING ] ) ) { // Not found
            return false;
        }
        foreach ( $this->headers as $key => $value ) {
            if ( $key !== ACCEPT_ENCODING ) {
                continue;
            }
            // ...
        }
        return false;
    }

    public function acceptLanguageIsIn( array $languages ) {}

    // Content-Type

    public function contentTypeIsIn( array $desiredFormatsOrMimes ) {}
}

?>