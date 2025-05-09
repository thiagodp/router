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
    private $_headers = []; // Array containing every header. A header value can be an array (!)
    private $_rawBody = '';
    private $_method = '';
    private $_cookies = [];
    private $_params = [];
    private $_extra = null;

    /** @inheritDoc */
    public function url(): ?string {
        return $this->_url;
    }

    /** @inheritDoc */
    public function urlWithoutQueries(): ?string {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    public function queries(): array {
        return $this->_queries;
    }

    /** @inheritDoc */
    public function headers(): array {
        return $this->_headers;
    }

    /** @inheritDoc */
    public function header( $name ): ?string {
        return headerWithName( $name, $this->_headers );
    }

    /** @inheritDoc */
    public function rawBody(): ?string {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    public function body() {
        return $this->_rawBody;
    }

    /** @inheritDoc */
    public function method(): ?string {
        return $this->_method;
    }

    /** @inheritDoc */
    public function cookies(): array {
        return $this->_cookies;
    }

    /** @inheritDoc */
    public function cookie( $key ): ?string {
        return $this->_cookies[ $key ] ?? null;
    }

    /** @inheritDoc */
    public function param( $name ): ?string {
        return $this->_params[ $name ] ?? null;
    }

    /** @inheritDoc */
    public function params(): array {
        return $this->_params;
    }

    /** @inheritDoc */
    public function extra(): ExtraData {
        if ( $this->_extra === null ) {
            $this->_extra = new ExtraData();
        }
        return $this->_extra;
    }

    /** @inheritDoc */
    public function withParams( array $params ): HttpRequest {
        $this->_params = $params;
        return $this;
    }

    //
    // Extra, build methods
    //

    public function withUrl( $url ): FakeHttpRequest {
        $this->_url = $url;
        return $this;
    }

    public function withQueries( array $queries ): FakeHttpRequest {
        $this->_queries = $queries;
        return $this;
    }

    public function withHeaders( array $headers ): FakeHttpRequest {
        $this->_headers = $headers;
        return $this;
    }

    public function withHeader( $key, $value ): FakeHttpRequest {
        $this->_headers[ $key ] = $value;
        return $this;
    }

    public function withRawBody( $rawBody ): FakeHttpRequest {
        $this->_rawBody = $rawBody;
        return $this;
    }

    public function withBody( $body ): FakeHttpRequest {
        return $this->withRawBody( $body );
    }

    public function withMethod( $method ): FakeHttpRequest {
        $this->_method = $method;
        return $this;
    }

    public function withCookies( array $cookies ): FakeHttpRequest {
        $this->_cookies = $cookies;
        return $this;
    }

}

?>