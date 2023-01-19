<?php
namespace phputil\router;

/**
 * Mockable HTTP request.
 */
interface HttpRequest {

    /** Returns the current URL. */
    function url();

    /** Returns the current URL without any queries. E.g. `/foo?bar=10` -> `/foo` */
    function urlWithoutQueries();

    /** Returns the URL queries. E.g. `/foo?bar=10&zoo=A` -> `['bar'=>'10', 'zoo'=>'A']` */
    function queries();

    /** Returns all HTTP request headers */
    function headers();

    /** Returns the header with the given case-insensitive name, or `null` if not found. */
    function header( $name );

    /** Returns the raw body. */
    function rawBody();

    /**
     * Returns the converted content as following:
     *  - `x-form-urlencoded` is returned as an array;
     *  - `application/json` is returned as a json object/array;
     *  - Otherwise is returned as string.
     */
    function body();

    /** Returns the HTTP request method. */
    function method();

    /** Returns all cookies as an array (map). */
    function cookies();

    /**
     * Returns the cookie value with the given case-insensitive key or `null` if not found.
     *
     * @param string $key Cookie key.
     */
    function cookie( $key );

    /**
     * Returns the parameter value with the given name.
     *
     * @param string $name Parameter name.
     */
    function param( $name );

    /**
     * Return all params as an array (map).
     */
    function params();

    /**
     * Returns extra, user-configurable data.
     * @return array
     */
    function extra();


    /**
     * Set the params. Do NOT use it directly.
     */
    function withParams( array $params );
}


/**
 * Real HTTP request.
 */
class RealHttpRequest implements HttpRequest {

    private $_cookies = null;
    private $_params = [];
    private $_extra = null;

    /** @inheritDoc */
    function url() {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_URI' ] ) ) {
            return '';
        }
        return $_SERVER[ 'REQUEST_URI' ];
    }

    /** @inheritDoc */
    function urlWithoutQueries() {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    function queries() {
        if ( ! isset( $_GET ) ) {
            return [];
        }
        return $_GET;
    }

    /** @inheritDoc */
    function headers() {
        if ( ! isset( $_SERVER ) ) {
            return [];
        }
        return extractHeaders( $_SERVER );
    }

    /** @inheritDoc */
    function header( $name ) {
        if ( ! isset( $_SERVER ) ) {
            return null;
        }
        return headerWithName( $name, $_SERVER );
    }

    /** @inheritDoc */
    function rawBody() {
        return \file_get_contents( 'php://input' );
    }

    /** @inheritDoc */
    function body() {
        return analizeBody( $this->header( 'Content-Type' ), $this->rawBody() );
    }

    /** @inheritDoc */
    function method() {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_METHOD' ] ) ) {
            return '';
        }
        return $_SERVER[ 'REQUEST_METHOD' ];
    }

    /** @inheritDoc */
    function cookies() {
        if ( isset( $_COOKIE ) ) {
            return $_COOKIE;
        }
        if ( ! isset( $this->_cookies ) ) {
            $this->_cookies = extractCookies( $this->headers() );
        }
        return $this->_cookies;

    }

    /** @inheritDoc */
    function cookie( $key ) {
        $cookies = $this->cookies();
        return isset( $cookies[ $key ] ) ? $cookies[ $key ] : null;
    }

    /** @inheritDoc */
    function param( $name ) {
        if ( isset( $_GET[ $name ] ) ) {
            return urldecode( $_GET[ $name ] );
        }
        if ( isset( $this->_params[ $name ] ) ) {
            return $this->_params[ $name ];
        }
        return null;
    }

    /** @inheritDoc */
    function params() {
        return $this->_params;
    }


    /** @inheritDoc */
    function extra() {
        if ( $this->_extra === null ) {
            $this->_extra = new ExtraData();
        }
        return $this->_extra;
    }

    /** @inheritDoc */
    function withParams( array $params ) {
        $this->_params = $params;
    }

}


function extractHeaders( array &$array ) {
    // Copy values and fix some keys
    foreach ( $array as $key => $value ) {
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


function headerWithName( $name, array $array ) {
    if ( isset( $array, $array[ $name ] ) ) {
        return $array[ $name ];
    }
    $headers = extractHeaders( $array );
    $name = \mb_strtolower( $name );
    foreach ( $headers as $key => $value ) {
        if ( \mb_strtolower( $key ) == $name ) {
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
        if ( \mb_strtolower( $key ) === 'cookie' ) {
            $pairs = \explode( ';', $value ); // Allow more than one key per Cookie header
            foreach ( $pairs as $p ) {
                list( $k, $v ) = \explode( '=', $p );
                $cookies[ $k ] = $v;
            }
        }
    }
    return $cookies;
}


function removeQueries( $url ) {
    $index = \mb_strpos( $url, '?' );
    if ( $index === false ) {
        return $url;
    }
    return \mb_substr( $url, 0, $index );
}


function analizeBody( $contentType, $rawBody ) {
    if ( $contentType === null ) {
        return $rawBody;
    }
    list( $cType ) = \explode( ';', \mb_strtolower( $contentType ) ); // Explode to avoid to comparing the charset
    if ( $cType === 'application/x-www-form-urlencoded' ) {
        $data = [];
        if ( \mb_parse_str( $rawBody, $data ) ) { // Success
            return $data;
        }
        return $rawBody;
    }
    if ( $cType === 'application/json' ) {
        $r = \json_decode( $rawBody );
        if ( $r !== null ) { // Maybe success
            return $r;
        }
        return ( $rawBody === 'null' ) ? $r : $rawBody;
    }
    return $rawBody;
}

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
 * Fake HTTP request
 */
class FakeHttpRequest implements HttpRequest {

    private $_url = '';
    private $_queries = [];
    private $_headers = [];
    private $_rawBody = '';
    private $_method = '';
    private $_cookies = [];
    private $_params = [];
    private $_extra = null;

    /** @inheritDoc */
    function url() {
        return $this->_url;
    }

    /** @inheritDoc */
    function urlWithoutQueries() {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    function queries() {
        return $this->_queries;
    }

    /** @inheritDoc */
    function headers() {
        return $this->_headers;
    }

    /** @inheritDoc */
    function header( $name ) {
        return headerWithName( $name, $this->_headers );
    }

    /** @inheritDoc */
    function rawBody() {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    function body() {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    function method() {
        return $this->_method;
    }

    /** @inheritDoc */
    function cookies() {
        return $this->_cookies;
    }

    /** @inheritDoc */
    function cookie( $key ) {
        return isset( $this->_cookies[ $key ] ) ? $this->_cookies[ $key ] : null;
    }

    /** @inheritDoc */
    function param( $name ) {
        return isset( $this->_params[ $name ] ) ? $this->_params[ $name ] : null;
    }

    /** @inheritDoc */
    function params() {
        return $this->_params;
    }

    /** @inheritDoc */
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

    function withBody( $body ) {
        return $this->withRawBody( $body );
    }

    function withMethod( $method ) {
        $this->_method = $method;
        return $this;
    }

    function withCookies( array $cookies ) {
        $this->_cookies = $cookies;
        return $this;
    }

    /** @inheritDoc */
    function withParams( array $params ) {
        $this->_params = $params;
    }

}


?>