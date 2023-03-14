<?php
namespace phputil\router;

require_once 'HttpRequest.php';
require_once 'Request.php';
require_once 'ExtraData.php';

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