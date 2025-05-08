<?php
namespace phputil\router;

interface HttpResponse {

    /**
     * Sets the HTTP status code.
     *
     * @param int $code HTTP status code.
     * @return HttpResponse
     */
    function status( int $code ): HttpResponse;

    /**
     * Indicates if the current HTTP status code is equal to the given one.
     *
     * @param int $code HTTP status code.
     * @return bool
     */
    function isStatus( int $code ): bool;

    /**
     * Sets an HTTP header.
     *
     * @param string $header HTTP header.
     * @param string|int|float|bool|array $value Header value.
     * @return HttpResponse
     */
    function header( string $header, $value ): HttpResponse;

    /**
     * Returns the number of headers with the given key.
     *
     * @param string $header Key
     * @return int
     */
    function headerCount( string $header ): int;

    /**
     * Indicates if the response has the given HTTP header.
     *
     * @param string $header HTTP header.
     * @return boolean
     */
    function hasHeader( string $header ): bool;

    /**
     * Returns the first response header with the given key, or `null` if the header doesn't exist.
     *
     * @param string $header HTTP header.
     * @return string|null
     */
    function getHeader( string $header ): ?string;

    /**
     * Returns all the response headers with the given key, as a matrix.
     * Example: `[[ 'Set-Cookie' => 'foo=1;' ], [ 'Set-Cookie' => 'bar=hello;' ]]`
     *
     * @param string $header HTTP header.
     * @return string[]
     */
    function getHeaders( string $header ): array;

    /**
     * Removes the first header with the given key. Optionally removes all the headers with the given key.
     *
     * @param string $header Header to remove.
     * @param bool $removeAll Option (default `false`) to remove all the headers with the given key.
     * @return int The number of removed headers.
     */
    function removeHeader( string $header, bool $removeAll = false ): int;

    /**
     * Sets a redirect response.
     *
     * @param int $statusCode HTTP status code.
     * @param string|null $path Path.
     * @return HttpResponse
     */
    function redirect( int $statusCode, $path = null ): HttpResponse;

    /**
     * Sets a cookie.
     *
     * @param string $name Name (key)
     * @param string $value Value.
     * @param array $options Optional map with the following options:
     *  - `domain`: string
     *  - `path`: string
     *  - `httpOnly`: true|1
     *  - `secure`: true|1
     *  - `maxAge`: int
     *  - `expires`: string
     *  - `sameSite`: true|1
     * @return HttpResponse
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies for options' meanings.
     */
    function cookie( string $name, string $value, array $options = [] ): HttpResponse;

    /**
     * Clears a cookie with the given name (key).
     *
     * @param string $name Name (key)
     * @param array $options Optional map with the same options as #cookie()'s.
     * @return HttpResponse
     */
    function clearCookie( string $name, array $options = [] ): HttpResponse;

    /**
     * Sets the `Content-Type` header with the given MIME type.
     *
     * @param string $mime MIME type.
     * @return HttpResponse
     */
    function type( string $mime ): HttpResponse;

    /**
     * Sends the given HTTP response body.
     *
     * @param mixed $body Response body.
     * @return HttpResponse
     */
    function send( $body ): HttpResponse;

    /**
     * Sends a file based on its path.
     *
     * @param string $path File path
     * @param array $options Optional map with the options:
     *  - `mime`: string - MIME type, such as `application/pdf`.
     * @return HttpResponse
     */
    function sendFile( string $path, array $options = [] ): HttpResponse;

    /**
     * Send the given content as JSON, also setting the needed headers.
     *
     * @param mixed $body Content to send as JSON.
     * @return HttpResponse
     */
    function json( $body ): HttpResponse;

    /**
     * Ends the HTTP response.
     *
     * @param bool $clear If it is desired to clear the headers and the body after sending them. It defaults to `true`.
     */
    function end( bool $clear = true ): HttpResponse;
}

?>