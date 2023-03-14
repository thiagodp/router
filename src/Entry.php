<?php
namespace phputil\router;

const ENTRY_HTTP        = 'h';
const ENTRY_GROUP       = 'g';
const ENTRY_MIDDLEWARE  = 'm';

interface Entry {
    function type();
}

/**
 * Join routes in a single URL path.
 */
function joinRoutes( array $routes ) {
    return \str_replace( '//', '/', \implode( '/', $routes ) );
}

?>
