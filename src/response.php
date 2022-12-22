<?php
namespace phputil\router;

const MSG_HEADER_VALUE_CANNOT_BE_NULL = 'Header value cannot be null.';
const MSG_HEADER_KEY_MUST_BE_STRING = 'Header key must be a string.';
const MSG_HEADER_PARAMETER_INVALID = 'Invalid header type. Accepted: string, array.';
const MSG_JSON_ENCODING_ERROR = 'JSON encoding error.';

const HEADER_TO_VALUE_SEPARATOR = ': ';
const HEADER_CONTENT_TYPE = 'Content-Type';
const HEADER_LOCATION = 'Location';
const HEADER_SET_COOKIE = 'Set-Cookie';
const MIME_JSON_UTF8 = 'application/json;charset=utf8';


class HttpException extends \RuntimeException {
}

interface HttpResponse {
    function status( int $code );
    function header( $header, $value );
    function redirect( $statusCode, $path = null );
    function cookie( $name, $value, array $options = [] );
    function clearCookie( $name, array $options = [] );
    function send( $body );
    function json( $body );
    function end();
}

class RealHttpResponse implements HttpResponse {

    private $statusCode = 200;
    private $headers = [];
    private $body = [];

    //
    // For testing/debugging purposes only
    //

    function dump() {
        return [
            'statusCode' => $this->statusCode,
            'headers' => $this->headers,
            'body' => $this->body
        ];
    }

    function dumpObject() {
        return (object) $this->dump();
    }

    function isStatus( $code ) {
        return $code === $this->statusCode;
    }

    //
    // HttpResponse
    //

    function status( $code ) {
        $this->statusCode = $code;
        return $this;
    }

    function header( $header, $value = null ) {
        if ( is_string( $header ) ) {
            $this->setHeader( $header, $value );
        } else if ( is_array( $header ) ) {
            foreach ( $header as $h => $v ) {
                $this->setHeader( $h, $v );
            }
        } else {
            throw new \LogicException( MSG_HEADER_PARAMETER_INVALID );
        }
        return $this;
    }

    function redirect( $statusCode, $path = null ) {
        $this->status( $statusCode );
        if ( ! \is_null( $path ) ) {
            $this->setHeader( HEADER_LOCATION, $path );
        }
        return $this;
    }

    function cookie( $name, $value, array $options = [] ) {
        $content = "$name=$value";
        if ( ! empty( $options ) ) {
            $fromToKeys = [
                'domain' => 'Domain',
                'path' => 'Path',
                'httpOnly' => 'HttpOnly',
                'secure' => 'Secure',
                'maxAge' => 'Max-Age',
                'expires' => 'Expires',
                'sameSite' => 'Same-Site'
            ];

            $opt = [];
            foreach ( $options as $key => $value ) {
                if ( array_key_exists( $key, $fromToKeys ) ) {
                    $opt[ $fromToKeys[ $key ] ] = $value;
                    continue;
                }
                if ( array_search( $key, $fromToKeys ) === false ) {
                    continue;
                }
                $opt[ $key ] = $value;
            }

            $additional = '';
            foreach ( $opt as $key => $value ) {
                if ( $key === 'HttpOnly' || $key === 'Secure' ) {
                    $additional .= " $key;";
                    continue;
                }
                $additional .= " $key=$value;";
            }
            if ( ! empty( $additional ) ) {
                $content .= ";" . $additional;
            }
        }
        return $this->setHeader( HEADER_SET_COOKIE, $content );
    }

    function clearCookie( $name, array $options = [] ) {
        return $this->cookie( $name, '', $options );
    }

    private function setHeader( $header, $value ) {
        if ( ! \is_string( $header ) ) {
            throw new \LogicException( MSG_HEADER_KEY_MUST_BE_STRING );
        }
        if ( \is_null( $value ) ) {
            throw new \LogicException( MSG_HEADER_VALUE_CANNOT_BE_NULL );
        }
        $this->headers[ $header ] = $value;
        return $this;
    }

    function send( $body ) {
        if ( \is_array( $body ) || \is_object( $body ) ) {
            return $this->json( $body );
        }
        $this->body []= $body;
        return $this;
    }

    function json( $body ) {
        $this->header( HEADER_CONTENT_TYPE, MIME_JSON_UTF8 );
        if ( \is_array( $body ) || \is_object( $body ) ) {
            $result = \json_encode( $body );
            if ( $result === false ) {
                throw new HttpException( MSG_JSON_ENCODING_ERROR );
            }
            $this->body []= $result;
        } else {
            $this->body []= $body;
        }
        return $this;
    }

    function end() {
        \http_response_code( $this->statusCode );
        foreach ( $this->headers as $header => $value ) {
            \header( $header . HEADER_TO_VALUE_SEPARATOR . $value );
        }
        foreach ( $this->body as $body ) {
            echo $body;
        }
    }
}

?>