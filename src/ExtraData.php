<?php
namespace phputil\router;

/**
 * Extra, user-defined data.
 */
class ExtraData {

    private $data = [];

    /**
     * Sets a value to the given key. Chainable method.
     *
     * @param string|int $key
     * @param mixed $value
     * @return ExtraData
     */
    public function set( $key, $value ): ExtraData {
        $this->data[ $key ] = $value;
        return $this;
    }

    /**
     * Returns the value for the given key, or null otherwise.
     * @param string|int $key
     * @return mixed
     */
    public function get( $key ) {
        return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
    }

    /**
     * Returns the keys and values as an array.
     */
    public function toArray(): array {
        return $this->data;
    }
}

?>