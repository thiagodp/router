<?php
namespace phputil\router;

require_once 'Entry.php';
require_once 'RouteBasedEntry.php';
require_once 'http.php';

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

?>
