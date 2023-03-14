<?php
namespace phputil\router;

/**
 * Mockable HTTP request.
 */
interface HttpRequest {

    /** Returns the current URL. */
    function url();

    /** Returns the current URL without any queries. E.g. `/foo?bar=10` -> `/foo` */
    function urlWithoutQueries();

    /** Returns the URL queries. E.g. `/foo?bar=10&zoo=A` -> `['bar'=>'10', 'zoo'=>'A']` */
    function queries();

    /** Returns all HTTP request headers */
    function headers();

    /** Returns the header with the given case-insensitive name, or `null` if not found. */
    function header( $name );

    /** Returns the raw body. */
    function rawBody();

    /**
     * Returns the converted content as following:
     *  - `x-form-urlencoded` is returned as an array;
     *  - `application/json` is returned as a json object/array;
     *  - Otherwise is returned as string.
     */
    function body();

    /** Returns the HTTP request method. */
    function method();

    /** Returns all cookies as an array (map). */
    function cookies();

    /**
     * Returns the cookie value with the given case-insensitive key or `null` if not found.
     *
     * @param string $key Cookie key.
     */
    function cookie( $key );

    /**
     * Returns the parameter value with the given name.
     *
     * @param string $name Parameter name.
     */
    function param( $name );

    /**
     * Return all params as an array (map).
     */
    function params();

    /**
     * Returns extra, user-configurable data.
     * @return array
     */
    function extra();


    /**
     * Set the params. Do NOT use it directly.
     */
    function withParams( array $params );
}

?>