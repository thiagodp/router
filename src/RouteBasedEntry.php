<?php
namespace phputil\router;

require_once 'Entry.php';

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

?>
