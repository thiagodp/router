<?php
namespace phputil\router;

/**
 * Route to Regex result.
 */
class R2R {
    public string $regex;
    public array $params;
    function __construct( string $regex, array $params = [] ) {
        $this->regex = $regex;
        $this->params = $params;
    }
}

/**
 * Transform a route into a regex.
 *
 * @param string $route Route
 * @return R2R
 */
function routeToRegex( string $route ): R2R {

    // Asterisk replacement
    $asteriskRegex = '[\pL 0-9\_\-\.\,\;\%\?\=\!\#\&\+\*\$\@\~\[\]\(\)]';
    $quantifiedAsteriskRegex = $asteriskRegex . ( $route === '*' ? '*' : '+' );
    $r = preg_replace( '/\*/', $quantifiedAsteriskRegex, $route );

    // Parameter extraction
    $paramRegex = '/(?:\:[A-Za-z][A-Za-z0-9\_\-]*)/';
    $matches = [];
    preg_match_all( $paramRegex, $r, $matches );
    if ( count( $matches ) > 0 ) {
        $matches = array_map( function( string $text ) {
            return str_replace( ':', '', $text );
        }, $matches[ 0 ] );
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

    $r = '/^' . $r . '$/u';
    return new R2R( $r, $matches );
}

/**
 * Extracts variables and their values from a URL, according to the given route.
 * Returns a tuple in the format [ bool, array ] in which:
 *  - the first element indicates if the url did match the route.
 *  - the second element returns the variables and values.
 *
 * @param string $path  URL path.
 * @param string $route Desired route.
 * @return array
 *
 * @example
 *  list( $matches, $variables ) = #( '/hello/world', 'hello/:w' );
 *  assertTrue( $matches );
 *  assertTrue( array_key_exists( 'w', $variables ) );
 *  assertEquals( $variables[ 'w' ], 'world' );
 */
function extractVariables( string $path, string $route ): array {
    $r = routeToRegex( $route );
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
