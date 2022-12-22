<?php
namespace phputil\router;

class Group {

    private $router = null;
    private $route = '';

    function __construct( &$router, $route ) {
        $this->router = $router;
        $this->route = $route;
    }

    function end() {
        return $this->router;
    }

    function get( $callback ) {
        $this->router->get( $this->route, $callback );
        return $this;
    }

    function post( $callback ) {
        $this->router->post( $this->route, $callback );
        return $this;
    }

    function put( $callback ) {
        $this->router->put( $this->route, $callback );
        return $this;
    }

    function delete( $callback ) {
        $this->router->delete( $this->route, $callback );
        return $this;
    }

    function head( $callback ) {
        $this->router->head( $this->route, $callback );
        return $this;
    }

    function options( $callback ) {
        $this->router->options( $this->route, $callback );
        return $this;
    }

    function patch( $callback ) {
        $this->router->patch( $this->route, $callback );
        return $this;
    }

    function all( $callback ) {
        $this->router->all( $this->route, $callback );
        return $this;
    }

}

?>