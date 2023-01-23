<?php
namespace phputil\router;

require_once 'http.php';

const ENTRY_HTTP        = 'h';
const ENTRY_GROUP       = 'g';
const ENTRY_MIDDLEWARE  = 'm';

interface Entry {
    function type();
}

class MiddlewareEntry implements Entry {

    /** @var callback Callback like function ( $req, $res, &$stop ) */
    public $callback = null;

    public function __construct( $callback ) {
        $this->callback = $callback;
    }

    function type() {
        return ENTRY_MIDDLEWARE;
    }
}


abstract class RouteBasedEntry implements Entry {

    /** @var string */
    public $route = '';

    /** @var Entry */
    public $parent = null;

    /** @var array of Entry */
    public $children = [];

    /** @var bool */
    public $isGroup = false;

    function __construct( $route ) {
        $this->route = $route;
    }

    /** @inheritDoc */
    abstract function type();


    function withParent( $parent ) {
        $this->parent = $parent;
        return $this;
    }

    function withRoute( $parent ) {
        $this->parent = $parent;
        return $this;
    }

    function hasParent() {
        return $this->parent != null;
    }

    function end() {
        return $this->hasParent() ? $this->parent : $this;
    }

    // function extractRoute( array &$target ) {
    //     if ( ! $this->isGroup ) {
    //         $target []= $this->route;
    //         return;
    //     }
    //     $this->extractRoutes( $target, $this->children, $this->route );
    // }

    // protected function extractRoutes( array &$target, array $children, $lastRoute ) {
    //     foreach ( $children as $child ) {
    //         $r = joinRoutes( [ $lastRoute, $child->route ] );
    //         if ( ! $child->isGroup ) {
    //             $target []= $r;
    //         } else {
    //             $this->extractRoutes( $target, $child->children, $r );
    //         }
    //     }
    // }

}


class HttpEntry extends RouteBasedEntry {

    public $httpMethod = METHOD_GET;

    /** @var array Array of callbacks with the following syntax: ( $req, $res, bool &$stop = false ) */
    public $callbacks = [];

    function __construct( $route, $httpMethod, array $callbacks ) {
        parent::__construct( $route );
        $this->httpMethod = $httpMethod;
        $this->callbacks = $callbacks;
    }

    /** @inheritDoc */
    function type() {
        return ENTRY_HTTP;
    }
}


class GroupEntry extends RouteBasedEntry {

    function __construct( $route ) {
        parent::__construct( $route );
        $this->isGroup = true;
    }

    /** @inheritDoc */
    function type() {
        return ENTRY_GROUP;
    }

    // HTTP

    function get( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_GET, $callbacks );
    }

    function post( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_POST, $callbacks );
    }

    function put( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_PUT, $callbacks );
    }

    function delete( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_DELETE, $callbacks );
    }

    function patch( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_PATCH, $callbacks );
    }

    function options( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_OPTIONS, $callbacks );
    }

    function head( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_HEAD, $callbacks );
    }

    function all( $route, callable ...$callbacks ) {
        foreach ( SUPPORTED_METHODS as $method ) {
            $this->addEntry( $route, $method, $callbacks );
        }
        return $this;
    }

    protected function addEntry( $route, $httpMethod, array $callbacks ) {
        if ( ! isHttpMethodValid( $httpMethod ) ) {
            throw new \LogicException( "Invalid HTTP method: $httpMethod" );
        }
        if ( \is_array( $route ) ) {
            foreach ( $route as $str ) {
                $this->children []= new HttpEntry( $str, $httpMethod, $callbacks );
            }
        } else {
            $this->children []= new HttpEntry( $route, $httpMethod, $callbacks );
        }
        return $this;
    }

    // SUB-GROUP

    function group( $route ) {
        $g = new GroupEntry( $route );
        $g->parent = $this;
        $this->children []= $g;
        return $g;
    }

    /** Alias to group() */
    function route( $route ) {
        return $this->group( $route );
    }

    /** Back to the parent, if it exists */
    function end() {
        return $this->parent === null ? $this : $this->parent;
    }

    // MIDDLEWARE

    function use( $callback ) {
        $this->children []= new MiddlewareEntry( $callback );
        return $this;
    }

}

/**
 * Join routes in a single URL path.
 */
function joinRoutes( array $routes ) {
    return \str_replace( '//', '/', \implode( '/', $routes ) );
}

?>
