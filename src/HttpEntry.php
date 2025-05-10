<?php
namespace phputil\router;

require_once 'Entry.php';
require_once 'RouteBasedEntry.php';
require_once 'http.php';

class HttpEntry extends RouteBasedEntry {

    public $httpMethod = METHOD_GET;

    /** @var array<callable> Array of callbacks with the following syntax: ( $req, $res, bool &$stop = false ) */
    public array $callbacks = [];

    public function __construct( $route, $httpMethod, array $callbacks ) {
        parent::__construct( $route );
        $this->httpMethod = $httpMethod;
        $this->callbacks = $callbacks;
    }

    /** @inheritDoc */
    public function type() {
        return ENTRY_HTTP;
    }
}

?>
