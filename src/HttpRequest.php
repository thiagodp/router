<?php
namespace phputil\router;

/**
 * Mockable HTTP request.
 */
interface HttpRequest {

    /** Returns the current URL or `null` on failure. */
    public function url(): ?string;

    /** Returns the current URL without any queries. E.g. `/foo?bar=10` -> `/foo` */
    public function urlWithoutQueries(): ?string;

    /** Returns the URL queries. E.g. `/foo?bar=10&zoo=A` -> `['bar'=>'10', 'zoo'=>'A']` */
    public function queries(): array;

    /** Returns all HTTP request headers */
    public function headers(): array;

    /** Returns the first header with the given case-insensitive name, or `null` if not found. */
    public function header( string $name ): ?string;

    /** Returns the raw body or `null` on failure. */
    public function rawBody(): ?string;

    /**
     * Returns the converted content, depending on the `Content-Type` header:
     *   - For `x-www-form-urlencoded`, it returns an `array`;
     *   - For `application/json`, it returns an `object` or an `array` (depending on the content).
     *   - Otherwise it returns a `string`, or `null` on failure.
     */
    public function body();

    /** Returns the HTTP request method or `null` on failure. */
    public function method(): ?string;

    /** Returns all cookies as an array (map). */
    public function cookies(): array;

    /**
     * Returns the cookie value with the given case-insensitive key or `null` if not found.
     *
     * @param string $key Cookie key.
     * @return string|null
     */
    public function cookie( string $key ): ?string;

    /**
     * Returns a URL query or route parameter with the given name (key),
     * or `null` when the given name is not found.
     *
     * @param string $name Parameter name.
     * @return string
     */
    public function param( string $name ): ?string;

    /**
     * Returns all the URL queries and route parameters as an array (map).
     * @return array
     */
    public function params(): array;

    /**
     * Returns extra, user-configurable data.
     * @return ExtraData
     */
    public function extra(): ExtraData;

    /**
     * Set the params. Do NOT use it directly.
     */
    public function withParams( array $params ): HttpRequest;
}

?>