<?php
namespace phputil\router;

use function str_replace;
use function implode;

interface Entry {
    public function type();
}

const ENTRY_HTTP = 'h';
const ENTRY_GROUP = 'g';
const ENTRY_MIDDLEWARE = 'm';

/**
 * Join routes in a single URL path.
 */
function joinRoutes( array $routes ) {
    return str_replace( '//', '/', implode( '/', $routes ) );
}

?>
