<?php
namespace phputil\router;

use function array_key_exists;
use function get_object_vars;

/**
 * Router options.
 */
class RouterOptions {

    /** @var string Root URL */
    public $rootURL = '';

    /** @var HttpRequest Request that can be replaced with a mock, for testing purposes. */
    public $req = null;

    /** @var HttpResponse Response that can be replaced with a mock, for testing purposes. */
    public $res = null;

    /** Converts to an array */
    public function toArray() {
        return get_object_vars( $this );
    }

    /**
     * Set values from an array (map).
     *
     * @param array $options Options.
     * @return RouterOptions
     */
    public function fromArray( array $options ) {
        $attributes = $this->toArray();
        foreach ( $options as $key => $value ) {
            if ( array_key_exists( $key, $attributes ) ) {
                $this->{ $key } = $value;
            }
        }
        return $this;
    }

    public function withRootURL( $value ) {
        $this->rootURL = $value;
        return $this;
    }
    public function withReq( $value ) {
        $this->req = $value;
        return $this;
    }
    public function withRes( $value ) {
        $this->res = $value;
        return $this;
    }
}

?>