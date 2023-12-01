<?php
namespace phputil\router;

require_once 'Entry.php';

class MiddlewareEntry implements Entry {

    /** @var callable Callback like function ( $req, $res, &$stop ) */
    public $callback = null;

    public function __construct( callable $callback ) {
        $this->callback = $callback;
    }

    function type() {
        return ENTRY_MIDDLEWARE;
    }
}
?>
