<?php
namespace phputil\router;

require_once 'http-response.php';
require_once 'http-exception.php';
require_once 'mime.php';
require_once 'headers.php';

const MSG_HEADER_VALUE_CANNOT_BE_NULL = 'Header value cannot be null.';
const MSG_HEADER_KEY_MUST_BE_STRING = 'Header key must be a string.';
const MSG_HEADER_PARAMETER_INVALID = 'Invalid header type. Accepted: string, array.';
const MSG_JSON_ENCODING_ERROR = 'JSON encoding error.';


class RealHttpResponse implements HttpResponse {

    protected $avoidOutput = false; // For testing purposes
    protected $statusCode = 0; // 0 = not change the default
    protected $headers = [];
    protected $body = [];

    public function __construct( $avoidOutput = false ) {
        $this->avoidOutput = $avoidOutput;
    }

    //
    // For testing/debugging purposes only
    //

    function dump() {
        return \get_object_vars( $this );
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
                if ( \array_key_exists( $key, $fromToKeys ) ) {
                    $opt[ $fromToKeys[ $key ] ] = $value;
                    continue;
                }
                if ( \array_search( $key, $fromToKeys ) === false ) {
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

    function type( $mime, $useUTF8 = true ) {
        $value = ( \mb_strlen( $mime ) <= 4 && isset( SHORT_MIMES[ $mime ] ) )
            ? SHORT_MIMES[ $mime ] : $mime;
        if ( $useUTF8 && \mb_strripos( $value, 'text/' ) === 0 && \mb_strripos( $value, 'charset' ) !== false ) {
            $value .= ';charset=UTF-8';
        }
        return $this->setHeader( HEADER_CONTENT_TYPE, $value );
    }

    function send( $body ) {
        if ( \is_array( $body ) || \is_object( $body ) ) {
            return $this->json( $body );
        }
        $this->body []= $body;
        return $this->end( false );
    }

    function json( $body ) {
        $this->setHeader( HEADER_CONTENT_TYPE, MIME_JSON_UTF8 );
        if ( \is_array( $body ) || \is_object( $body ) ) {
            $result = \json_encode( $body );
            if ( $result === false ) {
                throw new HttpException( MSG_JSON_ENCODING_ERROR );
            }
            $this->body []= $result;
        } else {
            $this->body []= $body;
        }
        return $this->end( false );
    }

    function sendFile( $path, $options = [] ) {

        if ( ! \is_readable( $path ) ) {
            throw new \RuntimeException( 'File not found or not readable.' );
        }

        $mime = ( \is_array( $options ) && isset( $options[ 'mime' ] ) )
            ? $options[ 'mime' ] : getFileMime( $path );
        if ( ! isset( $mime ) ) {
            throw new \RuntimeException( 'MIME type could not be defined. Please inform it.' );
        }

        $fileSize = \filesize( $path );
        $fileDisposition = 'attachment; path=' . \basename( $path );

        $this->status( 200 );
        $this->header( [
            'Content-Type' => $mime,
            'Content-Length' => $fileSize,
            'Content-Disposition' => $fileDisposition,
        ] );

        // // No cache
        // header('Cache-Control: must-revalidate');
        // header('Expires: 0');
        // header('Pragma: public');

        $this->body = []; // Empty the body
        $this->sendHeaders( true );

        if ( $this->avoidOutput ) {
            return $this->end();
        }

        ob_clean();
        flush();
        readfile( $path );

        return $this->end( true );
    }

    function end( $clear = true ) {
        $this->sendHeaders( $clear );
        $this->sendBody( $clear );
        return $this; // It should be kept
    }

    protected function sendHeaders( $clear ) {
        if ( $this->statusCode !== 0 ) {
            \http_response_code( $this->statusCode );
        }
        foreach ( $this->headers as $header => $value ) {
            @\header( $header . HEADER_TO_VALUE_SEPARATOR . $value );
        }
        if ( $clear ) {
            $this->headers = [];
        }
    }

    protected function sendBody( $clear ) {
        if ( ! $this->avoidOutput ) {
            foreach ( $this->body as $body ) {
                echo $body;
            }
        }
        if ( $clear ) {
            $this->body = [];
        }
    }
}

?>