<?php
namespace phputil\router;

require_once 'HttpRequest.php';
require_once 'ExtraData.php';
require_once 'request.php';

/**
 * Real HTTP request.
 */
class RealHttpRequest implements HttpRequest {

    private $_cookies = null;
    private $_params = [];
    private $_extra = null;

    /** @inheritDoc */
    function url(): ?string {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_URI' ] ) ) {
            return null;
        }
        return $_SERVER[ 'REQUEST_URI' ];
    }

    /** @inheritDoc */
    function urlWithoutQueries(): ?string {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    function queries(): array {
        if ( ! isset( $_GET ) ) {
            return [];
        }
        return $_GET;
    }

    /** @inheritDoc */
    function headers(): array {
        if ( ! isset( $_SERVER ) ) {
            return [];
        }
        return extractHeaders( $_SERVER );
    }

    /** @inheritDoc */
    function header( $name ): ?string {
        if ( ! isset( $_SERVER ) ) {
            return null;
        }
        return headerWithName( $name, $_SERVER );
    }

    /** @inheritDoc */
    function rawBody(): ?string {
        $content = \file_get_contents( 'php://input' );
        return $content === false ? null : $content;
    }

    /** @inheritDoc */
    function body() {
        return analizeBody( $this->header( 'Content-Type' ), $this->rawBody() );
    }

    /** @inheritDoc */
    function method(): ?string {
        if ( ! isset( $_SERVER, $_SERVER[ 'REQUEST_METHOD' ] ) ) {
            return null;
        }
        return $_SERVER[ 'REQUEST_METHOD' ];
    }

    /** @inheritDoc */
    function cookies(): array {
        if ( isset( $_COOKIE ) ) {
            return $_COOKIE;
        }
        if ( ! isset( $this->_cookies ) ) {
            $this->_cookies = extractCookies( $this->headers() );
        }
        return $this->_cookies;

    }

    /** @inheritDoc */
    function cookie( $key ): ?string {
        $cookies = $this->cookies();
        return isset( $cookies[ $key ] ) ? $cookies[ $key ] : null;
    }

    /** @inheritDoc */
    function param( $name ): ?string {
        if ( isset( $_GET[ $name ] ) ) {
            return urldecode( $_GET[ $name ] );
        }
        if ( isset( $this->_params[ $name ] ) ) {
            return $this->_params[ $name ];
        }
        return null;
    }

    /** @inheritDoc */
    function params(): array {
        return $this->_params;
    }


    /** @inheritDoc */
    function extra(): ExtraData {
        if ( $this->_extra === null ) {
            $this->_extra = new ExtraData();
        }
        return $this->_extra;
    }

    /** @inheritDoc */
    function withParams( array $params ): HttpRequest {
        $this->_params = $params;
        return $this;
    }

}

?>