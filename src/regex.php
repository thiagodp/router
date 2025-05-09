<?php
namespace phputil\router;

/**
 * Transform a route into a regex.
 *
 * @param string $route Route
 * @param bool $isGroupRoute Indicates if it is a group route.
 * @return RouteToRegexResult
 */
function routeToRegex( string $route, bool $isGroupRoute = false ): RouteToRegexResult {

    // Asterisk replacement
    $asteriskRegex = '[\pL 0-9\_\-\.\,\;\%\?\=\!\#\&\+\*\$\@\~\[\]\(\)]';
    $quantifiedAsteriskRegex = $asteriskRegex . ( $route === '*' ? '*' : '+' );
    $r = preg_replace( '/\*/', $quantifiedAsteriskRegex, $route );

    // Parameter extraction
    $paramRegex = '/(?:\:[A-Za-z][A-Za-z0-9\_\-]*)/';
    $matches = [];
    preg_match_all( $paramRegex, $r, $matches );
    if ( count( $matches ) > 0 ) {
        $matches = array_map( fn(string $text) => str_replace( ':', '', $text ), $matches[ 0 ] );
    }
    $parameterValueRegex = '(' . $asteriskRegex . '+)';
    $r = preg_replace( $paramRegex, $parameterValueRegex, $r );

    // Slashes
    $r = str_replace( '/', '\/', $r ); // Escape slashes
    $lastIndex = mb_strlen( $r ) - 1;
    $lastChar = $lastIndex >= 0 && isset( $r[ $lastIndex ] ) ? $r[ $lastIndex ] : '';
    if ( $lastChar == '/' ) {
        $r .= '?'; // Makes the final slash optional
    } else {
        $r .= '\/?'; // Add an optional slash
    }

    $r = $isGroupRoute ? ( '/^' . $r . '/u' ) : ( '/^' . $r . '$/u' );
    return new RouteToRegexResult( $r, $matches, $isGroupRoute );
}

/**
 * Extracts variables and their values from a URL, according to the given route.
 * Returns a tuple in the format [ bool, array ] in which:
 *  - the first element indicates if the url did match the route.
 *  - the second element returns the variables and values.
 *
 * @param string $path  URL path.
 * @param string $route Desired route.
 * @param bool $isGroupRoute Indicates if it is a group route.
 * @return array
 *
 * @example
 *  list( $matches, $variables ) = #( '/hello/world', 'hello/:w' );
 *  assertTrue( $matches );
 *  assertTrue( array_key_exists( 'w', $variables ) );
 *  assertEquals( $variables[ 'w' ], 'world' );
 */
function extractVariables( string $path, string $route, bool $isGroupRoute = false ): array {
    $r = routeToRegex( $route, $isGroupRoute );
    $matches = [];
    if ( ! preg_match( $r->regex, $path, $matches ) ) {
        return [ false, [] ];
    }
    array_shift( $matches ); // Removes the global match
    $variables = [];
    if ( count( $matches ) > 0 ) {
        $i = 0;
        foreach ( $r->params as $p ) {
            $variables[ $p ] = $matches[ $i++ ];
        }
    }
    return [ true, $variables ];
}

?>
