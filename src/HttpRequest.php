<?php
namespace phputil\router;

/**
 * Mockable HTTP request.
 */
interface HttpRequest {

    /** Returns the current URL or `null` on failure. */
    function url(): ?string;

    /** Returns the current URL without any queries. E.g. `/foo?bar=10` -> `/foo` */
    function urlWithoutQueries(): ?string;

    /** Returns the URL queries. E.g. `/foo?bar=10&zoo=A` -> `['bar'=>'10', 'zoo'=>'A']` */
    function queries(): array;

    /** Returns all HTTP request headers */
    function headers(): array;

    /** Returns the header with the given case-insensitive name, or `null` if not found. */
    function header( $name ): ?string;

    /** Returns the raw body or `null` on failure. */
    function rawBody(): ?string;

    /**
     * Returns the converted content, depending on the `Content-Type` header:
     *   - For `x-www-form-urlencoded`, it returns an `array`;
     *   - For `application/json`, it returns an `object` or an `array` (depending on the content).
     *   - Otherwise it returns a `string`, or `null` on failure.
     */
    function body();

    /** Returns the HTTP request method or `null` on failure. */
    function method(): ?string;

    /** Returns all cookies as an array (map). */
    function cookies(): array;

    /**
     * Returns the cookie value with the given case-insensitive key or `null` if not found.
     *
     * @param string $key Cookie key.
     * @return string|null
     */
    function cookie( $key ): ?string;

    /**
     * Returns a URL query or route parameter with the given name (key),
     * or `null` when the given name is not found.
     *
     * @param string $name Parameter name.
     * @return string
     */
    function param( $name ): ?string;

    /**
     * Returns all the URL queries and route parameters as an array (map).
     * @return array
     */
    function params(): array;

    /**
     * Returns extra, user-configurable data.
     * @return ExtraData
     */
    function extra(): ExtraData;

    /**
     * Set the params. Do NOT use it directly.
     */
    function withParams( array $params ): HttpRequest;
}

?>