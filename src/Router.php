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

use Throwable;

use function call_user_func_array;
use function is_array;

// STATUS ---------------------------------------------------------------------

const STATUS_NOT_FOUND = 404;
const STATUS_METHOD_NOT_ALLOWED = 405;

// ERROR HANDLER --------------------------------------------------------------

function defaultErrorHandler( Throwable $e, HttpRequest $req, HttpResponse $res, bool $debugMode ) {
    if ( $debugMode ) {
        $traceContent = str_replace( "\n", '<br />', $e->getTraceAsString() );
        [ , $traceContent ] = explode( '{', $traceContent );
        $trace = "<br /><br />Stack Trace: <br/><code>{" . $traceContent . '</code>';
        $res->status( 500 )->send( $e->getMessage() . $trace );
    } else {
        $res->status( 500 )->send( $e->getMessage() );
    }
}

// ROUTER ---------------------------------------------------------------------

class Router extends GroupEntry {

    // private $conditions = []; // Same structure as $entries
    private bool $debugMode = false;

    /** @var callable|null */
    private $errorHandler = null;

    public function __construct() {
        parent::__construct( '' );
    }

    public function isDebugMode(): bool {
        return $this->debugMode;
    }
    public function setDebugMode( bool $debugMode ) {
        $this->debugMode = $debugMode;
        return $this;
    }

    public function getErrorHandler(): ?callable {
        return $this->errorHandler;
    }

    public function setErrorHandler( ?callable $handler ) {
        $this->errorHandler = $handler;
        return $this;
    }

    protected function invokeErrorHandler( Throwable $e, $req, $res ): void {
        if ( $this->errorHandler === null || ! is_callable( $this->errorHandler ) ) {
            defaultErrorHandler( $e, $req, $res, $this->isDebugMode() );
            return;
        }
        call_user_func_array( $this->errorHandler, [ $e, $req, $res, $this->isDebugMode() ] );
    }

    // TODO: refactor
    protected function findRoute(
        &$req,
        &$res,
        &$path,
        &$httpMethod,
        &$parentRoute,
        array &$children,
        &$routeEntry,
        &$variables,
        &$routeMatched,
        bool &$hasStopped
    ): bool {

        $hasStopped = false;
        foreach ( $children as &$entry ) {

            // Middleware
            if ( $entry->type() === ENTRY_MIDDLEWARE && isset( $entry->callback ) ) {
                $stop = false;
                $ok = false;
                try {
                    $ok = ! ( call_user_func_array( $entry->callback, [ &$req, &$res, &$stop ] ) === false );
                } catch ( Throwable $e ) {
                    $this->invokeErrorHandler( $e, $req, $res );
                    $hasStopped = true;
                    return false;
                }
                if ( ! $ok ) {
                    return false;
                }

                // @phpstan-ignore-next-line
                if ( $stop ) {
                    $hasStopped = true;
                    break;
                }

                continue; // Proceed to the next entry
            }

            // Find the route
            $newRoute = joinRoutes( [ $parentRoute, $entry->route ] );
            $isGroupRoute = $entry->type() === ENTRY_GROUP;
            [$found, $var] = extractVariables( $path, $newRoute, $isGroupRoute );
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
            $stop = false;
            $found = $this->findRoute(
                $req,
                $res,
                $path,
                $httpMethod,
                $newRoute,
                $entry->children,
                $routeEntry,
                $variables,
                $routeMatched,
                $stop
            );
            if ( $found ) {
                return true;
            }
            if ( $stop ) {
                $hasStopped = true;
                break;
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
    public function listen( $options = [] ): array {

        $opt = null;
        if ( is_array( $options ) ) {
            $opt = ( new RouterOptions() )->fromArray( $options );
        } else {
            $opt = ( $options instanceof RouterOptions ) ? $options : new RouterOptions();
        }

        $req = ( $opt->req instanceof HttpRequest ) ? $opt->req : new RealHttpRequest();

        $res = ( $opt->res instanceof HttpResponse ) ? $opt->res : new RealHttpResponse();

        // var_dump( $opt, $req, $res );
        // die();

        // Extract route path
        $path = urldecode( str_replace( $opt->rootURL, '', $req->urlWithoutQueries() ) );

        // FIND
        $routeEntry = null;
        $variables = null;
        $httpMethod = $req->method();
        $routeMatched = false;
        $hasStopped = false;

        $found = $this->findRoute(
            $req,
            $res,
            $path,
            $httpMethod,
            $this->route,
            $this->children,
            $routeEntry,
            $variables,
            $routeMatched,
            $hasStopped
        );

        if ( $hasStopped ) {
            return [ $found, $req, $res, $variables ];
        }

        // Route exists but its method is not allowed
        if ( $routeMatched && ! isset( $routeEntry ) ) { // Route matched but it does not have an entry
            $res->status( STATUS_METHOD_NOT_ALLOWED )->end();
            return [ false, $req, $res, $variables ];
        }

        // No entry found
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

            $ok = false;
            try {
                $ok = ! ( call_user_func_array( $callback, $args ) === false );
            } catch ( Throwable $e ) {
                $this->invokeErrorHandler( $e, $req, $res );
                $stop = true;
            }
            if ( ! $ok || $stop ) {
                break;
            }
        }

        return [ $ok, $req, $res, $variables ];
    }

}

?>