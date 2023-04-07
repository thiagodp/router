<?php
namespace phputil\router;

require_once 'http.php';
require_once 'HttpRequest.php';
require_once 'RealHttpRequest.php';
require_once 'HttpResponse.php';
require_once 'RealHttpResponse.php';
require_once 'mime.php';
require_once 'Entry.php';
require_once 'GroupEntry.php';
require_once 'HttpEntry.php';
require_once 'RouteBasedEntry.php';
require_once 'MiddlewareEntry.php';
require_once 'regex.php';
require_once 'RouterOptions.php';

// STATUS ---------------------------------------------------------------------

const STATUS_NOT_FOUND = 404;
const STATUS_METHOD_NOT_ALLOWED = 405;

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
                continue; // Proceed to the next entry
            }

            // Find the route
            $newRoute = joinRoutes( [ $parentRoute, $entry->route ] );
            $isGroupRoute = $entry->type() === ENTRY_GROUP;
            list( $found, $var ) = extractVariables( $path, $newRoute, $isGroupRoute );
            if ( ! $found ) {
                continue;
            }

            // Capture the variables from both Groups and Routes
            if ( isset( $var ) ) {
                $variables = [];
                foreach ( $var as $key => $value ) {
                    $variables[ $key ] = $value;
                }
                $req->withParams( $variables );
            }

            if ( ! $entry->isGroup ) {
                $routeMatched = true; // It was found for a route
                // Now let's check the HTTP method
                if ( $entry->httpMethod === $httpMethod ) {
                    $routeEntry = $entry;
                    return true;
                }
                // Not the same HTTP method -> let's try the next entry
                continue;
            }

            // It is was a group, find in children
            $found = $this->findRoute(
                $req, $res, $path, $httpMethod, $newRoute, $entry->children, $routeEntry, $variables, $routeMatched );
            if ( $found ) {
                return true;
            }
        }
        return false;
    }


    /**
     * Analyzes the registered routes and the HTTP request for determining if they match and executing the given function.
     *
     * @param array|RouterOptions $options Options. listen( [ 'rootURL' => dirname( $_SERVER[ 'PHP_SELF' ] ) ] )
     * @return array
     */
    function listen( $options = [] ) {

        $opt = \is_array( $options )
            ? ( ( new RouterOptions() )->fromArray( $options ) )
            : ( ( \is_object( $options ) && $options instanceof RouterOptions ) ? $options : new RouterOptions() );

        $req = ( \is_object( $opt->req ) && $opt->req instanceof HttpRequest ) ? $opt->req : new RealHttpRequest();

        $res = ( \is_object( $opt->res ) && $opt->res instanceof HttpResponse ) ? $opt->res : new RealHttpResponse();

        // var_dump( $opt, $req, $res );
        // die();

        // Extract route path
        $path = urldecode( str_replace( $opt->rootURL, '', $req->urlWithoutQueries() ) );

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
        if ( ! isset( $routeEntry->callbacks ) || count( $routeEntry->callbacks ) <= 0 ) {
            return [ true, $req, $res, $variables ];
        }

        // Found -> invoke the given callbacks with request and response objects
        $ok = true;
        $stop = false;
        foreach ( $routeEntry->callbacks as &$callback ) {

            $args = [ &$req, &$res, &$stop ];

            // $rf = new \ReflectionFunction( $callback );
            // $rfParams = $rf->getParameters();
            // if ( count( $rfParams ) < 3 ) { // User did not defined $stop
            //     array_pop( $args );
            // }

            $ok = ! ( \call_user_func_array( $callback, $args ) === false );
            if ( ! $ok || $stop ) {
                break;
            }
        }

        return [ $ok, $req, $res, $variables ];
    }

}

?>