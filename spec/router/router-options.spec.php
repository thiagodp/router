<?php
require_once __DIR__ . '/../../src/RouterOptions.php';

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