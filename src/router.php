<?php
namespace phputil\router;

require_once 'request.php';
require_once 'response.php';
require_once 'mime.php';
require_once 'entry.php';
require_once 'regex.php';
require_once 'http.php';

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