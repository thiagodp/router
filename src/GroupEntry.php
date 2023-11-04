<?php
namespace phputil\router;

require_once 'Entry.php';
require_once 'RouteBasedEntry.php';

use LogicException;

use function is_array;


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
            throw new LogicException( "Invalid HTTP method: $httpMethod" );
        }
        if ( is_array( $route ) ) {
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

?>
