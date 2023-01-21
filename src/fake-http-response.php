<?php
namespace phputil\router;

require_once 'real-http-response.php';

/**
 * Fake HTTP response
 */
class FakeHttpResponse extends RealHttpResponse {

    public function __construct() {
        parent::__construct( $avoidOutput = true );
    }

}

?>