<?php
namespace phputil\router;

require_once 'request.php';
require_once 'response.php';
require_once 'mime.php';
require_once 'entry.php';
require_once 'regex.php';

// HTTP STATUS ----------------------------------------------------------------

const STATUS_NOT_FOUND = 404;
const STATUS_METHOD_NOT_ALLOWED = 405;

// HEADERS --------------------------------------------------------------------

const ACCEPT = 'Accept';
const ACCEPT_ENCODING = 'Accept-Encoding';

// UTILITIES ------------------------------------------------------------------

function isHttpMethodValid( $method ) {
    return \array_search( \mb_strtoupper( $method ), SUPPORTED_METHODS ) !== false;
}

function removeQualityValues( array &$input ) {
    $new = [];
    foreach ( $input as &$value ) {
        $r = \explode( ';', \trim( $value ) );
        $new []= $r[ 0 ];
    }
    return $new;
}

// ROUTER ---------------------------------------------------------------------

class Router extends GroupEntry {

    // private $conditions = []; // Same structure as $entries

    function __construct() {
        parent::__construct('');
    }

    protected function findRoute( &$req, &$res, &$path, &$parentRoute, array &$children, &$routeEntry, &$variables ) {

        foreach ( $children as &$entry ) {

            if ( $entry->type() === ENTRY_MIDDLEWARE && isset( $entry->callback ) ) {
                $stop = false;
                call_user_func_array( $entry->callback, [ &$req, &$res, &$stop ] );
                if ( $stop ) {
                    break;
                }
                continue;
            }

            $newRoute = joinRoutes( [ $parentRoute, $entry->route ] );
            list( $routeMatches, $var ) = extractVariables( $path, $newRoute );

             // Capture the variables from Groups or Routes if matched
            if ( $routeMatches && isset( $var ) ) {
                foreach ( $var as $key => $value ) {
                    $variables[ $key ] = $value;
                }
                $req->withParams( $variables );
            }

            // Found
            // if ( $routeMatches ) { // Matches both a Group and a Route
            if ( $routeMatches && ! $entry->isGroup ) { // Matches only a Route
                $routeEntry = $entry;
                return true;
            }

            // Not found && not a group -> try next
            if ( ! $entry->isGroup ) {
                continue;
            }

            // Not found && group -> find in children
            $found = $this->findRoute( $req, $res, $path, $newRoute, $entry->children, $routeEntry, $variables );
            if ( $found ) {
                return true;
            }
        }
        return false;
    }


    function listen( $rootURL = '', array $options = [] ) {

        // Create a request object if it is not defined for testing purposes
        $req = ( isset( $options[ 'req' ] ) &&
            \is_object( $options[ 'req' ] ) &&
            ( $options[ 'req' ] instanceof HttpRequest ) )
            ? $options[ 'req' ] : new RealHttpRequest();


        // Create a response object if it is not defined for testing purposes
        $res = ( isset( $options[ 'res' ] ) &&
            \is_object( $options[ 'res' ] ) &&
            ( $options[ 'res' ] instanceof HttpResponse ) )
            ? $options[ 'res' ] : new RealHttpResponse();

        // Extract route path
        $path = str_replace( $rootURL, '', $req->urlWithoutQueries() );

        // FIND
        $routeEntry = null;
        $variables = null;
        $found = $this->findRoute( $req, $res, $path, $this->route, $this->children, $routeEntry, $variables );

        // Not found
        if ( ! isset( $routeEntry ) ) {
            $res->status( STATUS_NOT_FOUND )->end();
            return [ false, $req, $res, $variables ];
        }

        // Method not allowed
        if ( ! $routeEntry->isGroup && $routeEntry->httpMethod !== $req->method() ) {
            $res->status( STATUS_METHOD_NOT_ALLOWED )->end();
            return [ false, $req, $res, $variables ];
        }

        // Callback not defined (?)
        if ( ! isset( $routeEntry->callback ) ) {
            return [ true, $req, $res, $variables ];
        }

        // Found -> invoke the given callback with request and response objects
        $ok = ! ( call_user_func_array( $routeEntry->callback, [ &$req, &$res, $variables ] ) === false );
        return [ $ok, $req, $res, $variables ];
    }

}

?>