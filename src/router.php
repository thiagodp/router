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

    // TODO: refactor
    protected function findRoute(
        &$req, &$res, &$path, &$httpMethod, &$parentRoute, array &$children, &$routeEntry, &$variables, &$routeMatched
    ) {

        foreach ( $children as &$entry ) {

            // Middleware
            if ( $entry->type() === ENTRY_MIDDLEWARE && isset( $entry->callback ) ) {
                $stop = false;
                call_user_func_array( $entry->callback, [ &$req, &$res, &$stop ] );
                if ( $stop ) {
                    break;
                }
                continue;
            }

            // Find the route
            $newRoute = joinRoutes( [ $parentRoute, $entry->route ] );
            list( $routeMatches, $var ) = extractVariables( $path, $newRoute );

            // Found -> Capture the variables from Groups or Routes if matched
            if ( $routeMatches && isset( $var ) ) {
                $variables = [];
                foreach ( $var as $key => $value ) {
                    $variables[ $key ] = $value;
                }
                $req->withParams( $variables );
            }

            // Matches only a Route (not a group) that has the same HTTP method
            if ( $routeMatches && ! $entry->isGroup ) {
                $routeMatched = true;
                if ( $entry->httpMethod === $httpMethod ) {
                    $routeEntry = $entry;
                    return true;
                }
            }

            // Not found && not a group -> try next
            if ( ! $entry->isGroup ) {
                continue;
            }

            // Not found && group -> find in children
            $found = $this->findRoute(
                $req, $res, $path, $httpMethod, $newRoute, $entry->children, $routeEntry, $variables, $routeMatched );
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
        $path = urldecode( str_replace( $rootURL, '', $req->urlWithoutQueries() ) );

        // FIND
        $routeEntry = null;
        $variables = null;
        $httpMethod = $req->method();
        $routeMatched = false;

        $found = $this->findRoute(
            $req, $res, $path, $httpMethod, $this->route, $this->children, $routeEntry, $variables, $routeMatched );

        // Method not allowed
        if ( $routeMatched && ! isset( $routeEntry )  ) { // Route matched but has no entry
            $res->status( STATUS_METHOD_NOT_ALLOWED )->end();
            return [ false, $req, $res, $variables ];
        }

        // Not found
        if ( ! isset( $routeEntry ) ) {
            $res->status( STATUS_NOT_FOUND )->end();
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