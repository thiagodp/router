<?php
namespace phputil\router;

require_once 'Entry.php';

abstract class RouteBasedEntry implements Entry {

    public string $route = '';

    public ?Entry $parent = null;

    /** @var array<Entry> */
    public array $children = [];

    public bool $isGroup = false;

    public function __construct( $route ) {
        $this->route = $route;
    }

    /** @inheritDoc */
    abstract public function type();

    public function withParent( $parent ) {
        $this->parent = $parent;
        return $this;
    }

    public function withRoute( $parent ) {
        $this->parent = $parent;
        return $this;
    }

    public function hasParent() {
        return $this->parent != null;
    }

    public function end() {
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
