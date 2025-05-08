<?php
namespace phputil\router;

require_once 'HttpResponse.php';
require_once 'HttpException.php';
require_once 'mime.php';
require_once 'headers.php';

use LogicException;
use RuntimeException;

use function array_key_exists;
use function array_search;
use function basename;
use function filesize;
use function get_object_vars;
use function header;
use function http_response_code;
use function is_array;
use function is_null;
use function is_object;
use function is_readable;
use function is_string;
use function json_encode;
use function mb_strlen;
use function mb_strripos;


const MSG_HEADER_VALUE_CANNOT_BE_NULL = 'Header value cannot be null.';
const MSG_HEADER_KEY_MUST_BE_STRING = 'Header key must be a string.';
const MSG_HEADER_PARAMETER_INVALID = 'Invalid header type. Accepted: string, array.';
const MSG_JSON_ENCODING_ERROR = 'JSON encoding error.';


class RealHttpResponse implements HttpResponse {

    protected $avoidOutput = false; // For testing purposes
    protected $avoidClearing = false; // For testing purposes

    protected $statusCode = 0; // 0 = not change the default
    protected $headers = []; // matrix with pairs of content, like [ [ 'Set-Cookie', 'foo=1' ], [ 'Set-Cookie', 'hello=world' ], [ 'Content-Type', 'application/json' ] ]
    protected $body = [];

    public function __construct( $avoidOutput = false, $avoidClearing = false ) {
        $this->avoidOutput = $avoidOutput;
        $this->avoidClearing = $avoidClearing;

        // Copy the headers about to send
        $headers = @headers_list();
        foreach ( $headers as $key => $value ) {
            $this->addHeader( $key, $value );
        }
        // Remove the headers about to send
        @header_remove();
    }

    //
    // For testing/debugging purposes only
    //

    function dump() {
        return get_object_vars( $this );
    }

    function dumpObject() {
        return (object) $this->dump();
    }

    //
    // HttpResponse
    //

    /** @inheritDoc */
    function status( $code ): HttpResponse {
        $this->statusCode = $code;
        return $this;
    }

    /** @inheritDoc */
    function isStatus( int $code ): bool {
        return $code === $this->statusCode;
    }

    /** @inheritDoc */
    function header( $header, $value = null ): HttpResponse {
        if ( is_string( $header ) ) {
            $this->addHeader( $header, $value );
        } else if ( is_array( $header ) ) {
            foreach ( $header as $h => $v ) {
                $this->addHeader( $h, $v );
            }
        } else {
            throw new LogicException( MSG_HEADER_PARAMETER_INVALID );
        }
        return $this;
    }

    /** @inheritDoc */
    function headerCount( string $header ): int {
        $count = 0;
        foreach ( $this->headers as [ $key ] ) {
            if ( $header === $key ) {
                $count++;
            }
        }
        return $count;
    }

    /** @inheritDoc */
    public function getHeader( string $header ): ?string {
        foreach ( $this->headers as [ $key, $value ] ) {
            if ( $header === $key ) {
                return $value;
            }
        }
        return null;
    }

    /** @inheritDoc */
    public function getHeaders( string $header = '' ): array {

        if ( empty( $header ) ) {
            return $this->headers;
        }

        $found = [];
        foreach ( $this->headers as [ $key, $value ] ) {
            if ( $header === $key ) {
                $found []= [ $key, $value ];
            }
        }
        return $found;
    }

    /** @inheritDoc */
    function hasHeader( string $header ): bool {
        return $this->headerCount( $header ) > 0;
    }

    /** @inheritDoc */
    function removeHeader( string $header, bool $removeAll = false ): int {
        $removalCount = 0;
        foreach ( $this->headers as $index => [ $key ] ) {
            if ( $header === $key ) {
                unset( $this->headers[ $index ] );
                @header_remove( $header );
                $removalCount++;
                if ( ! $removeAll ) {
                    break;
                }
            }
        }
        return $removalCount;
    }

    /** @inheritDoc */
    function redirect( $statusCode, $path = null ): HttpResponse {
        $this->status( $statusCode );
        if ( ! is_null( $path ) ) {
            $this->addHeader( HEADER_LOCATION, $path );
        }
        return $this;
    }

    /** @inheritDoc */
    function cookie( string $name, string $value, array $options = [] ): HttpResponse {
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
        return $this->addHeader( HEADER_SET_COOKIE, $content );
    }

    /** @inheritDoc */
    function clearCookie( $name, array $options = [] ): HttpResponse {
        return $this->cookie( $name, '', $options );
    }

    private function addHeader( $header, $value ): HttpResponse {
        if ( ! is_string( $header ) ) {
            throw new LogicException( MSG_HEADER_KEY_MUST_BE_STRING );
        }
        if ( is_null( $value ) ) {
            throw new LogicException( MSG_HEADER_VALUE_CANNOT_BE_NULL );
        }
        $this->headers[] = [ $header, $value ];
        return $this;
    }

    /** @inheritDoc */
    function type( $mime, $useUTF8 = true ): HttpResponse {
        $value = ( mb_strlen( $mime ) <= 4 && isset( SHORT_MIMES[ $mime ] ) )
            ? SHORT_MIMES[ $mime ] : $mime;
        if ( $useUTF8 && mb_strripos( $value, 'text/' ) === 0 && mb_strripos( $value, 'charset' ) !== false ) {
            $value .= ';charset=UTF-8';
        }
        return $this->addHeader( HEADER_CONTENT_TYPE, $value );
    }

    /** @inheritDoc */
    function send( $body ): HttpResponse {
        if ( is_array( $body ) || is_object( $body ) ) {
            return $this->json( $body );
        }
        $this->body []= $body;
        return $this->end( false );
    }

    /** @inheritDoc */
    function json( $body ): HttpResponse {
        $this->addHeader( HEADER_CONTENT_TYPE, MIME_JSON_UTF8 );
        if ( is_array( $body ) || is_object( $body ) ) {
            $result = json_encode( $body );
            if ( $result === false ) {
                throw new HttpException( MSG_JSON_ENCODING_ERROR );
            }
            $this->body []= $result;
        } else {
            $this->body []= $body;
        }
        return $this->end( false );
    }

    /** @inheritDoc */
    function sendFile( $path, array $options = [] ): HttpResponse {

        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'File not found or not readable.' );
        }

        $mime = isset( $options[ 'mime' ] )
            ? $options[ 'mime' ]
            : ( isset( $this->headers[ 'Content-Type' ] ) ? $this->headers[ 'Content-Type' ] : getFileMime( $path ) );

        if ( ! isset( $mime ) ) {
            throw new RuntimeException( 'MIME type could not be defined. Please inform it.' );
        }

        $fileSize = @filesize( $path );
        $fileDisposition = 'attachment; path=' . basename( $path );

        $this->status( 200 );
        $this->addHeader( 'Content-Type', $mime );
        $this->addHeader( 'Content-Length', $fileSize );
        $this->addHeader( 'Content-Disposition', $fileDisposition );

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

    /** @inheritDoc */
    function end( $clear = true ): HttpResponse {
        $this->sendHeaders( $clear );
        $this->sendBody( $clear );
        return $this; // It should be kept
    }

    protected function sendHeaders( $clear ): void {
        if ( $this->statusCode !== 0 ) {
            @http_response_code( $this->statusCode );
        }
        foreach ( $this->headers as [ $header, $value ] ) {
            @header( $header . HEADER_TO_VALUE_SEPARATOR . $value );
        }
        if ( $clear && ! $this->avoidClearing ) {
            $this->headers = [];
        }
    }

    protected function sendBody( $clear ): void {
        if ( ! $this->avoidOutput ) {
            foreach ( $this->body as $body ) {
                echo $body;
            }
        }
        if ( $clear && ! $this->avoidClearing ) {
            $this->body = [];
        }
    }
}

?>