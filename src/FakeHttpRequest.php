<?php
namespace phputil\router;

require_once 'HttpRequest.php';
require_once 'ExtraData.php';
require_once 'request.php';

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
    function url(): ?string {
        return $this->_url;
    }

    /** @inheritDoc */
    function urlWithoutQueries(): ?string {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    function queries(): array {
        return $this->_queries;
    }

    /** @inheritDoc */
    function headers(): array {
        return $this->_headers;
    }

    /** @inheritDoc */
    function header( $name ): ?string {
        return headerWithName( $name, $this->_headers );
    }

    /** @inheritDoc */
    function rawBody(): ?string {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    function body() {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    function method(): ?string {
        return $this->_method;
    }

    /** @inheritDoc */
    function cookies(): array {
        return $this->_cookies;
    }

    /** @inheritDoc */
    function cookie( $key ): ?string {
        return isset( $this->_cookies[ $key ] ) ? $this->_cookies[ $key ] : null;
    }

    /** @inheritDoc */
    function param( $name ): ?string {
        return isset( $this->_params[ $name ] ) ? $this->_params[ $name ] : null;
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

    //
    // Extra, build methods
    //

    function withUrl( $url ): HttpRequest {
        $this->_url = $url;
        return $this;
    }

    function withQueries( array $queries ): HttpRequest {
        $this->_queries = $queries;
        return $this;
    }

    function withHeaders( array $headers ): HttpRequest {
        $this->_headers = $headers;
        return $this;
    }

    function withHeader( $key, $value ): HttpRequest {
        $this->_headers[ $key ] = $value;
        return $this;
    }

    function withRawBody( $rawBody ): HttpRequest {
        $this->_rawBody = $rawBody;
        return $this;
    }

    function withBody( $body ): HttpRequest {
        return $this->withRawBody( $body );
    }

    function withMethod( $method ): HttpRequest {
        $this->_method = $method;
        return $this;
    }

    function withCookies( array $cookies ): HttpRequest {
        $this->_cookies = $cookies;
        return $this;
    }



}

?>