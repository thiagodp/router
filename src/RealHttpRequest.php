<?php
namespace phputil\router;

require_once 'HttpRequest.php';
require_once 'ExtraData.php';
require_once 'request.php';

use function file_get_contents;

/**
 * Real HTTP request.
 */
class RealHttpRequest implements HttpRequest {

    private $_cookies = [];
    private $_params = [];
    private $_extra = null;

    /** @inheritDoc */
    public function url(): ?string {
        return $_SERVER[ 'REQUEST_URI' ] ?? null;
    }

    /** @inheritDoc */
    public function urlWithoutQueries(): ?string {
        return removeQueries( $this->url() );
    }

    /** @inheritDoc */
    public function queries(): array {
        return $_GET;
    }

    /** @inheritDoc */
    public function headers(): array {
        if ( function_exists( 'getallheaders' ) ) {
            return getallheaders();
        }
        return extractHeaders( $_SERVER );
    }

    /** @inheritDoc */
    public function header( string $name ): ?string {
        return headerWithName( $name, $_SERVER );
    }

    /** @inheritDoc */
    public function rawBody(): ?string {
        $content = @file_get_contents( 'php://input' );
        return $content === false ? null : $content;
    }

    /** @inheritDoc */
    public function body() {
        return analizeBody( $this->header( 'Content-Type' ), $this->rawBody() );
    }

    /** @inheritDoc */
    public function method(): ?string {
        return $_SERVER[ 'REQUEST_METHOD' ] ?? null;
    }

    /** @inheritDoc */
    public function cookies(): array {
        return $_COOKIE;
    }

    /** @inheritDoc */
    public function cookie( string $key ): ?string {
        if ( isset( $_COOKIE[ $key ] ) ) {
            return $_COOKIE[ $key ];
        }
        if ( empty( $this->_cookies ) ) {
            $this->_cookies = extractCookies( $this->headers() );
        }
        return $this->_cookies[ $key ] ?? null;
    }

    /** @inheritDoc */
    public function param( string $name ): ?string {
        if ( isset( $_GET[ $name ] ) ) {
            return urldecode( $_GET[ $name ] );
        }
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

}

?>