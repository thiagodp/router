<?php
require_once 'src/router-options.php';

use phputil\router\RouterOptions;

describe( 'RouterOptions', function() {

    describe( 'fromArray()', function() {

        it( 'imports values', function() {
            $opt = new RouterOptions();
            $opt->fromArray( [ 'foo' => 'bar', 'rootURL' => '/' ] );
            expect( $opt->rootURL )->toBe( '/' );
        } );

    } );

} );