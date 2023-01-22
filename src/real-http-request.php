<?php
namespace phputil\router;

require_once 'http-request.php';
require_once 'request.php';

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

?>