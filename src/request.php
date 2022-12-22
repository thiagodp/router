<?php
namespace phputil\router;

/**
 * Extra, user-defined data.
 */
class ExtraData {

    private $data = [];

    /**
     * Sets a value to the given key. Chainable method.
     *
     * @param string|int $key
     * @param any $value
     * @return ExtraData
     */
    function set( $key, $value ) {
        $this->data[ $key ] = $value;
        return $this;
    }

    /**
     * Returns the value for the given key, or null otherwise.
     * @param string|int $key
     */
    function get( $key ) {
        return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
    }

    /**
     * Returns the keys and values as an array.
     */
    function toArray() {
        return $this->data;
    }
}

/**
 * Mockable HTTP request.
 */
interface HttpRequest {
    function url();
    function urlWithoutQueries();
    function queries();
    function headers();
    function rawBody();
    function method();
    /**
     * Returns extra, user-configurable data.
     * @return array
     */
    function extra();
}


/**
 * Real HTTP request.
 */
class RealHttpRequest implements HttpRequest {

    private $_extra = null;

    function url() {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_URI' ] ) ) {
            return '';
        }
        return $_SERVER[ 'REQUEST_URI' ];
    }

    function urlWithoutQueries() {
        return removeQueries( $this->url() );
    }

    function queries() {
        if ( ! isset( $_GET ) ) {
            return [];
        }
        return $_GET;
    }

    function headers() {
        if ( ! isset( $_SERVER ) ) {
            return [];
        }
        return extractHeaders( $_SERVER );
    }

    function rawBody() {
        return \file_get_contents( 'php://input' );
    }

    function method() {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_METHOD' ] ) ) {
            return '';
        }
        return $_SERVER[ 'REQUEST_METHOD' ];
    }

    function extra() {
        if ( $this->_extra === null ) {
            $this->_extra = new ExtraData();
        }
        return $this->_extra;
    }

}


function extractHeaders( array &$from ) {
    $headers = [];
    foreach ( $_SERVER as $key => $value ) {
        if ( \mb_substr( $key, 0, 5 ) === 'HTTP_' ) {
            $newKey = \mb_substr( $key, 5 ); // Remove "HTTP_"
            $newKey = \str_replace( '_', ' ', $newKey ); // Replace "_" with " "
            $newKey = \ucwords( $newKey ); // Uppercase the first letter of each word
            $newKey = \str_replace( ' ', '-', $newKey ); // Replace " " with "-"
            $headers[ $newKey ] = $value;
        } else if ( $key === 'CONTENT_TYPE' ) {
            $headers[ 'Content-Type' ] = $value;
        } else if ( $key === 'CONTENT_LENGTH' ) {
            $headers[ 'Content-Length' ] = $value;
        } else if ( $key === 'Authorization' ) {
            $headers[ $key ] = $value;
        }
    }

    if ( ! isset( $headers[ 'Authorization' ] ) ) {
        $key = 'Authorization';
        if ( isset( $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ) {
            $headers[ $key ] = $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ];
        } else if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) ) {
            $pwd = isset( $_SERVER[ 'PHP_AUTH_PW' ] ) ? $_SERVER[ 'PHP_AUTH_PW' ] : '';
            $headers[ $key ] = 'Basic ' . base64_encode( $_SERVER[ 'PHP_AUTH_USER' ] . ':' . $pwd );
        } else if ( isset( $_SERVER[ 'PHP_AUTH_DIGEST' ] ) ) {
            $headers[ $key ] = $_SERVER[ 'PHP_AUTH_DIGEST' ];
        }
    }

    return $headers;
}


function removeQueries( $url ) {
    $index = \mb_strpos( $url, '?' );
    if ( $index === false ) {
        return $url;
    }
    return \mb_substr( $url, 0, $index );
}


/**
 * Fake HTTP request
 */
class FakeHttpRequest implements HttpRequest {

    private $_url = '';
    private $_queries = [];
    private $_headers = [];
    private $_rawBody = '';
    private $_method = '';
    private $_extra = null;

    function url() {
        return $this->_url;
    }

    function urlWithoutQueries() {
        return removeQueries( $this->url() );
    }

    function queries() {
        return $this->_queries;
    }

    function headers() {
        return $this->_headers;
    }

    function rawBody() {
        return $this->_rawBody;
    }

    function method() {
        return $this->_method;
    }

    function extra() {
        if ( $this->_extra === null ) {
            $this->_extra = new ExtraData();
        }
        return $this->_extra;
    }

    //
    // Extra, build methods
    //

    function withUrl( $url ) {
        $this->_url = $url;
        return $this;
    }

    function withQueries( array $queries ) {
        $this->_queries = $queries;
        return $this;
    }

    function withHeaders( array $headers ) {
        $this->_headers = $headers;
        return $this;
    }

    function withHeader( $key, $value ) {
        $this->_headers[ $key ] = $value;
        return $this;
    }

    function withRawBody( $rawBody ) {
        $this->_rawBody = $rawBody;
        return $this;
    }

    function withMethod( $method ) {
        $this->_method = $method;
        return $this;
    }

}


?>