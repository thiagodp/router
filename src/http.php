<?php
namespace phputil\router;

use function array_search;
use function mb_strtoupper;

// HTTP METHODS ---------------------------------------------------------------

const METHOD_GET = 'GET';
const METHOD_POST = 'POST';
const METHOD_PUT = 'PUT';
const METHOD_DELETE = 'DELETE';
const METHOD_OPTIONS = 'OPTIONS';
const METHOD_HEAD = 'HEAD';
const METHOD_PATCH = 'PATCH';

const SUPPORTED_METHODS = [
    METHOD_GET,
    METHOD_POST,
    METHOD_PUT,
    METHOD_DELETE,
    METHOD_OPTIONS,
    METHOD_HEAD,
    METHOD_PATCH
];

// UTILITIES ------------------------------------------------------------------

function isHttpMethodValid( $method ) {
    return array_search( mb_strtoupper( $method ), SUPPORTED_METHODS ) !== false;
}

?>