<?php
namespace phputil\router;

require_once 'Entry.php';
require_once 'RouteBasedEntry.php';

use LogicException;

use function is_array;

class GroupEntry extends RouteBasedEntry {

    public function __construct( $route ) {
        parent::__construct( $route );
        $this->isGroup = true;
    }

    /** @inheritDoc */
    public function type() {
        return ENTRY_GROUP;
    }

    // HTTP

    public function get( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_GET, $callbacks );
    }

    public function post( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_POST, $callbacks );
    }

    public function put( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_PUT, $callbacks );
    }

    public function delete( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_DELETE, $callbacks );
    }

    public function patch( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_PATCH, $callbacks );
    }

    public function options( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_OPTIONS, $callbacks );
    }

    public function head( $route, callable ...$callbacks ) {
        return $this->addEntry( $route, METHOD_HEAD, $callbacks );
    }

    public function all( $route, callable ...$callbacks ) {
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
                $this->children [] = new HttpEntry( $str, $httpMethod, $callbacks );
            }
        } else {
            $this->children [] = new HttpEntry( $route, $httpMethod, $callbacks );
        }
        return $this;
    }

    // SUB-GROUP

    public function group( $route ) {
        $g = new GroupEntry( $route );
        $g->parent = $this;
        $this->children [] = $g;
        return $g;
    }

    /** Alias to group() */
    public function route( $route ) {
        return $this->group( $route );
    }

    /** Back to the parent, if it exists */
    public function end() {
        return $this->parent === null ? $this : $this->parent;
    }

    // MIDDLEWARE

    public function use( $callback ) {
        $this->children [] = new MiddlewareEntry( $callback );
        return $this;
    }

}

?>
